<?php
/**
 * Check Actual Field Names and Sample Data
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

echo "=== Checking Elasticsearch Fields ===\n\n";

// Get a sample document
echo "Fetching sample document...\n";
$response = $client->search([
    'index' => 'pages_new_search',
    'size' => 1,
    'body' => [
        'query' => ['match_all' => new stdClass()]
    ]
]);

if (isset($response['hits']['hits'][0]['_source'])) {
    $doc = $response['hits']['hits'][0]['_source'];
    
    echo "\nSample Document Fields:\n";
    echo "------------------------\n";
    foreach ($doc as $field => $value) {
        if (is_array($value)) {
            echo "  $field: [array with " . count($value) . " items]\n";
            if (count($value) > 0 && $field === 'author_ids') {
                echo "    First value: " . $value[0] . " (type: " . gettype($value[0]) . ")\n";
            }
        } else {
            $display = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
            echo "  $field: $display (type: " . gettype($value) . ")\n";
        }
    }
    
    echo "\n=== Critical Fields ===\n";
    echo "author_ids: " . (isset($doc['author_ids']) ? json_encode($doc['author_ids']) : 'NOT FOUND') . "\n";
    echo "book_id: " . (isset($doc['book_id']) ? $doc['book_id'] : 'NOT FOUND') . "\n";
    echo "book_section_id: " . (isset($doc['book_section_id']) ? $doc['book_section_id'] : 'NOT FOUND') . "\n";
}

// Check mapping
echo "\n\n=== Field Mappings ===\n";
$mapping = $client->indices()->getMapping([
    'index' => 'pages_new_search'
]);

$properties = $mapping['pages_new_search']['mappings']['properties'] ?? [];

echo "author_ids type: " . ($properties['author_ids']['type'] ?? 'NOT FOUND') . "\n";
echo "book_id type: " . ($properties['book_id']['type'] ?? 'NOT FOUND') . "\n";
echo "book_section_id type: " . ($properties['book_section_id']['type'] ?? 'NOT FOUND') . "\n";

// Test actual query with filter
echo "\n\n=== Testing Actual Query ===\n";

$testQuery = [
    'index' => 'pages_new_search',
    'body' => [
        'query' => [
            'bool' => [
                'must' => [
                    ['match' => ['content' => 'الله']]
                ],
                'filter' => [
                    ['terms' => ['author_ids' => [1]]]
                ]
            ]
        ],
        'size' => 0
    ]
];

echo "Query: " . json_encode($testQuery['body']['query'], JSON_UNESCAPED_UNICODE) . "\n\n";

$result = $client->search($testQuery);
echo "Results with author_ids filter [1]: " . $result['hits']['total']['value'] . "\n";

// Try without intval
$testQuery2 = [
    'index' => 'pages_new_search',
    'body' => [
        'query' => [
            'bool' => [
                'must' => [
                    ['match' => ['content' => 'الله']]
                ],
                'filter' => [
                    ['terms' => ['author_ids' => ['1']]] // String instead of int
                ]
            ]
        ],
        'size' => 0
    ]
];

$result2 = $client->search($testQuery2);
echo "Results with author_ids filter ['1'] (string): " . $result2['hits']['total']['value'] . "\n";

// Get actual author_ids values
echo "\n\n=== Sample author_ids Values ===\n";
$agg = $client->search([
    'index' => 'pages_new_search',
    'body' => [
        'size' => 0,
        'aggs' => [
            'authors' => [
                'terms' => [
                    'field' => 'author_ids',
                    'size' => 10
                ]
            ]
        ]
    ]
]);

if (isset($agg['aggregations']['authors']['buckets'])) {
    echo "Top 10 author_ids values:\n";
    foreach ($agg['aggregations']['authors']['buckets'] as $bucket) {
        echo "  - {$bucket['key']} (type: " . gettype($bucket['key']) . ") - {$bucket['doc_count']} documents\n";
    }
}
