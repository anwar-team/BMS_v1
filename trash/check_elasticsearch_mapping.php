<?php
/**
 * Check Elasticsearch Mapping and Analyzers
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

echo "=== Elasticsearch Mapping Analysis ===\n\n";

// Get mapping
$mapping = $client->indices()->getMapping(['index' => 'pages_new_search']);

echo "Content Field Mapping:\n";
echo "=====================\n";

$contentMapping = $mapping['pages_new_search']['mappings']['properties']['content'] ?? null;

if ($contentMapping) {
    echo json_encode($contentMapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
} else {
    echo "No content mapping found!\n\n";
}

// Test actual search with different fields
echo "Testing Actual Searches:\n";
echo "========================\n\n";

// Test 1: Search on content.exact
echo "1. Testing content.exact with 'الله':\n";
$result = $client->search([
    'index' => 'pages_new_search',
    'body' => [
        'query' => [
            'match_phrase' => [
                'content.exact' => [
                    'query' => 'الله',
                    'slop' => 0
                ]
            ]
        ],
        'size' => 3
    ]
]);
echo "Total: " . ($result['hits']['total']['value'] ?? 0) . "\n";
if (!empty($result['hits']['hits'])) {
    echo "Sample: " . substr($result['hits']['hits'][0]['_source']['content'] ?? '', 0, 100) . "...\n";
}
echo "\n";

// Test 2: Search on content.flexible
echo "2. Testing content.flexible with 'الله':\n";
$result = $client->search([
    'index' => 'pages_new_search',
    'body' => [
        'query' => [
            'match' => [
                'content.flexible' => [
                    'query' => 'الله',
                    'operator' => 'and'
                ]
            ]
        ],
        'size' => 3
    ]
]);
echo "Total: " . ($result['hits']['total']['value'] ?? 0) . "\n";
if (!empty($result['hits']['hits'])) {
    echo "Sample: " . substr($result['hits']['hits'][0]['_source']['content'] ?? '', 0, 100) . "...\n";
}
echo "\n";

// Test 3: Search on content.stemmed
echo "3. Testing content.stemmed with 'كتب':\n";
$result = $client->search([
    'index' => 'pages_new_search',
    'body' => [
        'query' => [
            'match' => [
                'content.stemmed' => [
                    'query' => 'كتب',
                    'operator' => 'and'
                ]
            ]
        ],
        'size' => 3
    ]
]);
echo "Total: " . ($result['hits']['total']['value'] ?? 0) . "\n";
if (!empty($result['hits']['hits'])) {
    for ($i = 0; $i < min(2, count($result['hits']['hits'])); $i++) {
        $content = $result['hits']['hits'][$i]['_source']['content'] ?? '';
        echo "Sample " . ($i+1) . ": " . substr($content, 0, 100) . "...\n";
    }
}
echo "\n";

// Test 4: Analyze text to see how it's tokenized
echo "4. Testing Analyzers:\n";
echo "=====================\n\n";

$analyzers = ['arabic_exact', 'arabic_flexible', 'arabic_stemmed'];
$testText = 'كتاب الله العظيم';

foreach ($analyzers as $analyzer) {
    echo "Analyzer: $analyzer\n";
    echo "Text: '$testText'\n";
    
    try {
        $analyzed = $client->indices()->analyze([
            'index' => 'pages_new_search',
            'body' => [
                'analyzer' => $analyzer,
                'text' => $testText
            ]
        ]);
        
        echo "Tokens: ";
        $tokens = array_map(function($t) { return $t['token']; }, $analyzed['tokens'] ?? []);
        echo implode(', ', $tokens) . "\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== Analysis Complete ===\n";
