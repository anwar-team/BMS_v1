<?php
/**
 * Test Filters Application
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\UltraFastSearchService;

echo "=== Testing Filter Application ===\n\n";

$searchService = new UltraFastSearchService();

// Test 1: Search without filters
echo "Test 1: Search WITHOUT Filters\n";
echo "--------------------------------\n";
$results1 = $searchService->search('الله', [], 1, 5);
echo "Total without filters: " . $results1['total'] . "\n\n";

// Test 2: Search WITH author filter
echo "Test 2: Search WITH Author Filter (ID=1)\n";
echo "-----------------------------------------\n";
$results2 = $searchService->search('الله', [
    'author_id' => 1,
    'search_type' => 'flexible_match'
], 1, 5);
echo "Total with author_id=1: " . $results2['total'] . "\n";
echo "Results count: " . count($results2['results']) . "\n";

if (count($results2['results']) > 0) {
    echo "First result author_id: " . ($results2['results'][0]['author_id'] ?? 'N/A') . "\n";
    echo "First result book_id: " . ($results2['results'][0]['book_id'] ?? 'N/A') . "\n";
}
echo "\n";

// Test 3: Search WITH section filter
echo "Test 3: Search WITH Section Filter (ID=2)\n";
echo "------------------------------------------\n";
$results3 = $searchService->search('القرآن', [
    'section_id' => 2,
    'search_type' => 'flexible_match'
], 1, 5);
echo "Total with section_id=2: " . $results3['total'] . "\n";
echo "Results count: " . count($results3['results']) . "\n";

if (count($results3['results']) > 0) {
    echo "First result section_id: " . ($results3['results'][0]['book_section_id'] ?? 'N/A') . "\n";
}
echo "\n";

// Test 4: Search WITH book filter
echo "Test 4: Search WITH Book Filter (ID=1)\n";
echo "---------------------------------------\n";
$results4 = $searchService->search('الإسلام', [
    'book_id' => 1,
    'search_type' => 'flexible_match'
], 1, 5);
echo "Total with book_id=1: " . $results4['total'] . "\n";
echo "Results count: " . count($results4['results']) . "\n";

if (count($results4['results']) > 0) {
    echo "First result book_id: " . ($results4['results'][0]['book_id'] ?? 'N/A') . "\n";
}
echo "\n";

// Test 5: Search WITH multiple filters
echo "Test 5: Search WITH Multiple Filters\n";
echo "-------------------------------------\n";
$results5 = $searchService->search('الحديث', [
    'author_id' => [1, 2],
    'section_id' => 2,
    'search_type' => 'flexible_match'
], 1, 5);
echo "Total with author_id=[1,2] + section_id=2: " . $results5['total'] . "\n";
echo "Results count: " . count($results5['results']) . "\n";
echo "\n";

// Test 6: Check if filter is actually applied by comparing
echo "Test 6: Verify Filter Application\n";
echo "----------------------------------\n";
$withoutFilter = $searchService->search('test', [], 1, 1);
$withFilter = $searchService->search('test', ['author_id' => 999999], 1, 1);

echo "Without filter total: " . $withoutFilter['total'] . "\n";
echo "With author_id=999999 (non-existent): " . $withFilter['total'] . "\n";

if ($withFilter['total'] < $withoutFilter['total']) {
    echo "✅ Filter IS being applied (results reduced)\n";
} else {
    echo "❌ Filter NOT being applied (same results)\n";
}
echo "\n";

echo "=== Summary ===\n";
echo "Check if totals are different when filters are applied.\n";
echo "If all tests show same total, filters are NOT working.\n";
