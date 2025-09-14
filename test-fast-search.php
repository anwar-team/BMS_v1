<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FastElasticsearchService;

echo "Testing FastElasticsearchService...\n";
echo "===================================\n\n";

try {
    $service = new FastElasticsearchService();
    
    echo "1. Testing fast search...\n";
    $start = microtime(true);
    
    $results = $service->search('الصلاة', [], 1, 5);
    
    $end = microtime(true);
    $duration = round(($end - $start) * 1000, 2);
    
    echo "Fast search completed in: " . $duration . " ms\n";
    echo "Results found: " . count($results['results']) . "\n";
    echo "Total results: " . $results['total'] . "\n\n";
    
    foreach ($results['results'] as $result) {
        echo "- Page ID: " . $result['id'] . ", Page Number: " . $result['page_number'] . "\n";
        echo "  Book: " . $result['book_title'] . "\n";
        echo "  Author: " . $result['author_name'] . "\n";
        echo "  Content: " . mb_substr(strip_tags($result['content']), 0, 100) . "...\n\n";
    }
    
    echo "2. Testing search with filters...\n";
    $start = microtime(true);
    
    $filteredResults = $service->search('القرآن', ['section_id' => 1], 1, 3);
    
    $end = microtime(true);
    $duration = round(($end - $start) * 1000, 2);
    
    echo "Filtered search completed in: " . $duration . " ms\n";
    echo "Filtered results found: " . count($filteredResults['results']) . "\n\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "Done.\n";