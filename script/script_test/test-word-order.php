<?php

/**
 * اختبار خيارات ترتيب الكلمات
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\UltraFastSearchService;

echo "════════════════════════════════════════════════════════\n";
echo "   اختبار ترتيب الكلمات في البحث\n";
echo "════════════════════════════════════════════════════════\n\n";

$searchService = new UltraFastSearchService();

$testQuery = "قال رسول الله";

echo "🔍 استعلام البحث: \"$testQuery\"\n\n";

$wordOrders = [
    'consecutive' => '📏 متتالية (slop=0)',
    'same_paragraph' => '📄 نفس الفقرة (slop=50)',
    'any_order' => '🔀 أي ترتيب (match with AND)'
];

foreach ($wordOrders as $order => $label) {
    echo "┌" . str_repeat("─", 58) . "┐\n";
    echo "│ $label" . str_repeat(" ", 58 - mb_strlen($label) - 1) . "│\n";
    echo "└" . str_repeat("─", 58) . "┘\n\n";
    
    // Test with different search types
    $searchTypes = [
        'exact_match' => '🎯',
        'flexible_match' => '🔄',
        'morphological' => '🌳'
    ];
    
    foreach ($searchTypes as $type => $icon) {
        try {
            $results = $searchService->search($testQuery, [
                'search_type' => $type,
                'word_order' => $order
            ], 1, 5);
            
            $total = $results['total'] ?? 0;
            echo "$icon $type: " . number_format($total) . " نتيجة\n";
            
            if ($total > 0 && isset($results['data'][0])) {
                $firstResult = $results['data'][0];
                $highlight = $firstResult['highlight'] ?? '';
                if ($highlight) {
                    echo "   عينة: " . mb_substr(strip_tags($highlight), 0, 100) . "...\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ $type: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
}

echo "════════════════════════════════════════════════════════\n";
echo "   📊 التحليل المتوقع\n";
echo "════════════════════════════════════════════════════════\n";
echo "📏 متتالية:      أقل النتائج (الكلمات متجاورة)\n";
echo "📄 نفس الفقرة:   نتائج متوسطة (مع كلمات بينها)\n";
echo "🔀 أي ترتيب:     أكثر النتائج (في أي مكان)\n";
echo "════════════════════════════════════════════════════════\n";
