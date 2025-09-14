<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Page;

echo "Testing Laravel Scout with Elasticsearch...\n";
echo "=========================================\n\n";

try {
    echo "1. Testing basic count...\n";
    $totalPages = Page::count();
    echo "Total pages in database: " . $totalPages . "\n\n";
    
    echo "2. Testing Scout search...\n";
    $start = microtime(true);
    
    $results = Page::search('الصلاة')->take(5)->get();
    
    $end = microtime(true);
    $duration = round(($end - $start) * 1000, 2);
    
    echo "Search completed in: " . $duration . " ms\n";
    echo "Results found: " . $results->count() . "\n\n";
    
    foreach ($results as $result) {
        echo "- Page ID: " . $result->id . ", Page Number: " . $result->page_number . "\n";
        echo "  Book: " . ($result->book->title ?? 'N/A') . "\n";
        echo "  Content Preview: " . mb_substr($result->content, 0, 100) . "...\n\n";
    }
    
    echo "3. Testing Scout with pagination...\n";
    $start = microtime(true);
    
    $paginatedResults = Page::search('الصلاة')->paginate(10);
    
    $end = microtime(true);
    $duration = round(($end - $start) * 1000, 2);
    
    echo "Paginated search completed in: " . $duration . " ms\n";
    echo "Total results: " . $paginatedResults->total() . "\n";
    echo "Results per page: " . $paginatedResults->count() . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nDone.\n";