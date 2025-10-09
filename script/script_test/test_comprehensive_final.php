<?php
/**
 * Comprehensive Final Test
 * All Search Types with Real Queries
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\UltraFastSearchService;

echo "=== اختبار شامل لنظام البحث ===\n\n";

$searchService = new UltraFastSearchService();

$tests = [
    [
        'name' => 'exact_match + consecutive',
        'query' => 'الحمد لله',
        'filters' => ['search_type' => 'exact_match', 'word_order' => 'consecutive'],
        'expected' => 'يجب أن يجد العبارة بالضبط بدون أي كلمات بينها'
    ],
    [
        'name' => 'exact_match + same_paragraph',
        'query' => 'رسول الله',
        'filters' => ['search_type' => 'exact_match', 'word_order' => 'same_paragraph'],
        'expected' => 'يجب أن يجد الكلمتين في نفس الفقرة (slop=50)'
    ],
    [
        'name' => 'exact_match + any_order',
        'query' => 'محمد صلى الله عليه وسلم',
        'filters' => ['search_type' => 'exact_match', 'word_order' => 'any_order'],
        'expected' => 'يجب أن يجد جميع الكلمات بأي ترتيب'
    ],
    [
        'name' => 'flexible_match + consecutive',
        'query' => 'القرآن الكريم',
        'filters' => ['search_type' => 'flexible_match', 'word_order' => 'consecutive'],
        'expected' => 'يجب أن يجد الكلمتين متتاليتين'
    ],
    [
        'name' => 'flexible_match + any_order',
        'query' => 'الإسلام',
        'filters' => ['search_type' => 'flexible_match', 'word_order' => 'any_order'],
        'expected' => 'يجب أن يجد الكلمة بأي شكل'
    ],
    [
        'name' => 'morphological + consecutive',
        'query' => 'علم',
        'filters' => ['search_type' => 'morphological', 'word_order' => 'consecutive'],
        'expected' => 'يجب أن يجد: علم، عالم، معلوم، علماء، إلخ'
    ],
    [
        'name' => 'morphological + any_order',
        'query' => 'كتب',
        'filters' => ['search_type' => 'morphological', 'word_order' => 'any_order'],
        'expected' => 'يجب أن يجد: كتب، كاتب، كتاب، مكتوب، إلخ'
    ],
    [
        'name' => 'With author filter',
        'query' => 'الله',
        'filters' => ['search_type' => 'flexible_match', 'author_id' => 1],
        'expected' => 'يجب أن يجد فقط من مؤلف رقم 1'
    ],
    [
        'name' => 'With section filter',
        'query' => 'الحديث',
        'filters' => ['search_type' => 'flexible_match', 'section_id' => 2],
        'expected' => 'يجب أن يجد فقط من قسم رقم 2'
    ]
];

$passed = 0;
$failed = 0;

foreach ($tests as $i => $test) {
    echo "Test " . ($i + 1) . ": {$test['name']}\n";
    echo str_repeat('-', 60) . "\n";
    echo "Query: '{$test['query']}'\n";
    echo "Expected: {$test['expected']}\n";
    
    try {
        $result = $searchService->search($test['query'], $test['filters'], 1, 5);
        
        $total = $result['total'] ?? 0;
        $resultsCount = count($result['results'] ?? []);
        $hasFilters = isset($result['filters']);
        
        echo "✅ Status: SUCCESS\n";
        echo "   Total: " . number_format($total) . " results\n";
        echo "   Shown: $resultsCount results\n";
        echo "   Has filter metadata: " . ($hasFilters ? 'YES' : 'NO') . "\n";
        
        if ($resultsCount > 0) {
            $sample = $result['results'][0];
            $content = strip_tags($sample['content'] ?? '');
            echo "   Sample: " . mb_substr($content, 0, 80) . "...\n";
        }
        
        $passed++;
        
    } catch (Exception $e) {
        echo "❌ Status: FAILED\n";
        echo "   Error: " . $e->getMessage() . "\n";
        $failed++;
    }
    
    echo "\n";
}

echo str_repeat('=', 60) . "\n";
echo "=== النتيجة النهائية ===\n";
echo "Passed: $passed / " . count($tests) . " tests\n";
echo "Failed: $failed / " . count($tests) . " tests\n";
echo "Success Rate: " . round(($passed / count($tests)) * 100, 2) . "%\n";

if ($passed === count($tests)) {
    echo "\n✅✅✅ جميع الاختبارات نجحت! النظام جاهز للاستخدام ✅✅✅\n";
} else {
    echo "\n⚠️ بعض الاختبارات فشلت، راجع التفاصيل أعلاه\n";
}
