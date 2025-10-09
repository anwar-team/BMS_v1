<?php
/**
 * Test REAL Search Issues
 * Testing exact_match, flexible_match, morphological
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\UltraFastSearchService;

echo "=== Testing REAL Search Problems ===\n\n";

$searchService = new UltraFastSearchService();

// Test 1: Exact Match - Should find ONLY exact phrase
echo "Test 1: البحث المطابق التام (Exact Match)\n";
echo "==========================================\n";
echo "Query: 'بسم الله الرحمن الرحيم'\n";
echo "Expected: الجملة بالضبط بدون أي كلمات بينها\n\n";

try {
    $results = $searchService->search('بسم الله الرحمن الرحيم', [
        'search_type' => 'exact_match',
        'word_order' => 'consecutive'
    ], 1, 5);
    
    echo "Total Results: " . $results['total'] . "\n";
    echo "Results shown: " . count($results['results']) . "\n\n";
    
    if (count($results['results']) > 0) {
        echo "Sample Result:\n";
        $sample = $results['results'][0];
        echo strip_tags($sample['content']) . "\n\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

// Test 2: Flexible Match - Should find with variations
echo "Test 2: البحث المرن (Flexible Match)\n";
echo "====================================\n";
echo "Query: 'الله'\n";
echo "Expected: يجد 'الله' و 'لله' و 'والله' إلخ\n\n";

try {
    $results = $searchService->search('الله', [
        'search_type' => 'flexible_match',
        'word_order' => 'any_order'
    ], 1, 5);
    
    echo "Total Results: " . $results['total'] . "\n";
    echo "Results shown: " . count($results['results']) . "\n\n";
    
    if (count($results['results']) > 0) {
        echo "Sample Result:\n";
        $sample = $results['results'][0];
        echo strip_tags($sample['content']) . "\n\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

// Test 3: Morphological - Should find root derivatives
echo "Test 3: البحث الاشتقاقي (Morphological)\n";
echo "======================================\n";
echo "Query: 'كتب'\n";
echo "Expected: يجد 'كتب' و 'كاتب' و 'كتاب' و 'مكتوب' إلخ\n\n";

try {
    $results = $searchService->search('كتب', [
        'search_type' => 'morphological',
        'word_order' => 'any_order'
    ], 1, 5);
    
    echo "Total Results: " . $results['total'] . "\n";
    echo "Results shown: " . count($results['results']) . "\n\n";
    
    if (count($results['results']) > 0) {
        echo "Sample Results:\n";
        for ($i = 0; $i < min(3, count($results['results'])); $i++) {
            $sample = $results['results'][$i];
            echo ($i+1) . ". " . strip_tags($sample['content']) . "\n\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

// Test 4: Check Elasticsearch Query
echo "Test 4: فحص Elasticsearch Query الفعلي\n";
echo "======================================\n";

// Let's check what query is actually sent to Elasticsearch
$reflection = new ReflectionClass($searchService);
$method = $reflection->getMethod('buildOptimizedQuery');
$method->setAccessible(true);

$query = $method->invoke($searchService, 'الله', [
    'search_type' => 'exact_match',
    'word_order' => 'consecutive'
]);

echo "Query Structure for exact_match:\n";
echo json_encode($query, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

$query2 = $method->invoke($searchService, 'الله', [
    'search_type' => 'morphological',
    'word_order' => 'any_order'
]);

echo "Query Structure for morphological:\n";
echo json_encode($query2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== Analysis Complete ===\n";
