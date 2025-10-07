<?php
/**
 * Check What Fields Actually Exist
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

echo "=== Finding Available Filter Fields ===\n\n";

// Get 10 random documents
$response = $client->search([
    'index' => 'pages_new_search',
    'size' => 10,
    'body' => [
        'query' => ['match_all' => new stdClass()]
    ]
]);

echo "Checking 10 sample documents...\n\n";

$fieldPresence = [];

foreach ($response['hits']['hits'] as $i => $hit) {
    $doc = $hit['_source'];
    
    echo "Document " . ($i+1) . ":\n";
    echo "  book_id: " . ($doc['book_id'] ?? 'MISSING') . "\n";
    echo "  book_section_id: " . ($doc['book_section_id'] ?? 'MISSING') . "\n";
    echo "  author_ids: " . (isset($doc['author_ids']) ? json_encode($doc['author_ids']) : 'MISSING') . "\n";
    echo "  book_author: " . ($doc['book_author'] ?? 'MISSING') . "\n";
    echo "  author_names: " . ($doc['author_names'] ?? 'MISSING') . "\n";
    echo "\n";
    
    // Track field presence
    $fieldPresence['book_id'] = ($fieldPresence['book_id'] ?? 0) + (isset($doc['book_id']) ? 1 : 0);
    $fieldPresence['book_section_id'] = ($fieldPresence['book_section_id'] ?? 0) + (isset($doc['book_section_id']) ? 1 : 0);
    $fieldPresence['author_ids'] = ($fieldPresence['author_ids'] ?? 0) + (isset($doc['author_ids']) ? 1 : 0);
    $fieldPresence['book_author'] = ($fieldPresence['book_author'] ?? 0) + (isset($doc['book_author']) && !empty($doc['book_author']) ? 1 : 0);
}

echo "\n=== Field Presence Summary ===\n";
foreach ($fieldPresence as $field => $count) {
    $percentage = ($count / 10) * 100;
    echo "$field: $count/10 ($percentage%)\n";
}

echo "\n=== Recommendations ===\n";
if ($fieldPresence['author_ids'] == 0) {
    echo "❌ author_ids field is MISSING from all documents!\n";
    echo "   This needs to be re-indexed or use alternative field.\n";
}

if ($fieldPresence['book_id'] == 10) {
    echo "✅ book_id field is present in all documents.\n";
    echo "   Can use for filtering.\n";
}

if ($fieldPresence['book_section_id'] == 10) {
    echo "✅ book_section_id field is present in all documents.\n";
    echo "   Can use for filtering.\n";
}

// Test if book_id filter works
echo "\n\n=== Testing book_id Filter ===\n";

// First, get a real book_id
$sample = $response['hits']['hits'][0]['_source'];
$testBookId = $sample['book_id'] ?? null;

if ($testBookId) {
    echo "Testing with book_id: $testBookId\n";
    
    $result = $client->search([
        'index' => 'pages_new_search',
        'body' => [
            'query' => [
                'bool' => [
                    'must' => [
                        ['match' => ['content' => 'الله']]
                    ],
                    'filter' => [
                        ['term' => ['book_id' => $testBookId]]
                    ]
                ]
            ],
            'size' => 0
        ]
    ]);
    
    echo "Results with book_id filter: " . $result['hits']['total']['value'] . "\n";
    
    if ($result['hits']['total']['value'] > 0) {
        echo "✅ book_id filter WORKS!\n";
    } else {
        echo "❌ book_id filter gives 0 results\n";
    }
}

// Test section_id filter
echo "\n\n=== Testing book_section_id Filter ===\n";

$testSectionId = $sample['book_section_id'] ?? null;

if ($testSectionId) {
    echo "Testing with book_section_id: $testSectionId\n";
    
    $result = $client->search([
        'index' => 'pages_new_search',
        'body' => [
            'query' => [
                'bool' => [
                    'must' => [
                        ['match' => ['content' => 'الله']]
                    ],
                    'filter' => [
                        ['term' => ['book_section_id' => $testSectionId]]
                    ]
                ]
            ],
            'size' => 0
        ]
    ]);
    
    echo "Results with book_section_id filter: " . $result['hits']['total']['value'] . "\n";
    
    if ($result['hits']['total']['value'] > 0) {
        echo "✅ book_section_id filter WORKS!\n";
    } else {
        echo "❌ book_section_id filter gives 0 results\n";
    }
}
