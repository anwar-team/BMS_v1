<?php
/**
 * Test with Real Values
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\UltraFastSearchService;

echo "=== Testing Filters with Real Values ===\n\n";

$searchService = new UltraFastSearchService();

// Test with book_id=9125 (we know this exists)
echo "Test 1: Filter by book_id=9125\n";
echo "--------------------------------\n";
$results1 = $searchService->search('الله', [
    'book_id' => 9125,
    'search_type' => 'flexible_match'
], 1, 5);
echo "Total with book_id=9125: " . $results1['total'] . "\n";
echo "Results: " . count($results1['results']) . "\n";
if (count($results1['results']) > 0) {
    echo "First result book_id: " . ($results1['results'][0]['book_id'] ?? 'N/A') . "\n";
}
echo "\n";

// Test with section_id=8 (we know this exists)
echo "Test 2: Filter by section_id=8\n";
echo "--------------------------------\n";
$results2 = $searchService->search('القرآن', [
    'section_id' => 8,
    'search_type' => 'flexible_match'
], 1, 5);
echo "Total with section_id=8: " . $results2['total'] . "\n";
echo "Results: " . count($results2['results']) . "\n";
if (count($results2['results']) > 0) {
    echo "First result section_id: " . ($results2['results'][0]['book_section_id'] ?? 'N/A') . "\n";
}
echo "\n";

// Test with section_id='8' as string
echo "Test 3: Filter by section_id='8' (string)\n";
echo "-------------------------------------------\n";
$results3 = $searchService->search('القرآن', [
    'section_id' => '8',
    'search_type' => 'flexible_match'
], 1, 5);
echo "Total with section_id='8': " . $results3['total'] . "\n";
echo "Results: " . count($results3['results']) . "\n";
echo "\n";

// Test combined filters
echo "Test 4: Combined Filters (book_id=9125 + section_id=8)\n";
echo "-------------------------------------------------------\n";
$results4 = $searchService->search('الفقه', [
    'book_id' => 9125,
    'section_id' => 8,
    'search_type' => 'flexible_match'
], 1, 5);
echo "Total with both filters: " . $results4['total'] . "\n";
echo "Results: " . count($results4['results']) . "\n";
if (count($results4['results']) > 0) {
    echo "First result book_id: " . ($results4['results'][0]['book_id'] ?? 'N/A') . "\n";
    echo "First result section_id: " . ($results4['results'][0]['book_section_id'] ?? 'N/A') . "\n";
}
echo "\n";

echo "=== Summary ===\n";
echo "If totals are different from Test 1 in SEARCH_OPTIMIZATION_LOG.md,\n";
echo "then filters are now WORKING!\n";
