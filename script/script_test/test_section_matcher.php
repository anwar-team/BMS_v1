<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;
use App\Services\BookSectionMatcher;
use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "اختبار استخراج ومطابقة أقسام الكتب\n";
echo "========================================\n\n";

// إنشاء instance من الـ Matcher
$matcher = new BookSectionMatcher();

// ملاحظة: جميع الكتب لديها shamela_id، سنختبر على كتب عشوائية
// في المستقبل، عندما نضيف كتب جديدة بدون shamela_id، سيعمل الفلتر
$books = Book::whereNotNull('description')
    ->where('description', '!=', '')
    ->inRandomOrder()
    ->limit(10)
    ->get();

echo "عدد الكتب للاختبار: " . $books->count() . "\n\n";

if ($books->isEmpty()) {
    echo "⚠️  لم يتم العثور على كتب للاختبار!\n";
    exit;
}

$stats = [
    'total' => 0,
    'extracted' => 0,
    'matched' => 0,
    'high_confidence' => 0,
    'medium_confidence' => 0,
    'low_confidence' => 0,
    'no_match' => 0
];

foreach ($books as $index => $book) {
    $stats['total']++;
    $bookNum = $index + 1;
    
    echo str_repeat("=", 80) . "\n";
    echo "كتاب #{$bookNum}: {$book->title}\n";
    echo str_repeat("=", 80) . "\n";
    
    // عرض جزء من الوصف
    $descriptionPreview = mb_substr($book->description, 0, 200);
    echo "\nالوصف (أول 200 حرف):\n{$descriptionPreview}...\n\n";
    
    // معالجة الكتاب
    try {
        $result = $matcher->process($book->description);
        
        if (!$result) {
            echo "❌ لم يتم استخراج قسم من الوصف\n\n";
            continue;
        }
        
        $stats['extracted']++;
        
        echo "📋 النص المستخرج: \"{$result['extracted_section_name']}\"\n";
        
        if ($result['matched_section_id']) {
            $stats['matched']++;
            
            $matchDetails = $result['match_details'];
            $confidence = $result['section_match_confidence'];
            
            // تصنيف حسب الثقة
            if ($confidence >= 0.80) {
                $stats['high_confidence']++;
                $icon = "✅";
                $label = "ثقة عالية";
            } elseif ($confidence >= 0.60) {
                $stats['medium_confidence']++;
                $icon = "⚠️";
                $label = "ثقة متوسطة";
            } else {
                $stats['low_confidence']++;
                $icon = "⚠️";
                $label = "ثقة منخفضة";
            }
            
            echo "\n{$icon} تم العثور على مطابقة ({$label}):\n";
            echo "   - القسم المطابق: {$matchDetails['section_name']}\n";
            echo "   - نسبة الثقة: " . ($confidence * 100) . "%\n";
            echo "   - نوع المطابقة: {$matchDetails['match_type']}\n";
            
            if (isset($matchDetails['similarity_score'])) {
                echo "   - درجة التشابه: {$matchDetails['similarity_score']}%\n";
            }
            
            // التطبيق التلقائي؟
            if ($confidence >= 0.80) {
                echo "   - ✅ يمكن التطبيق التلقائي\n";
            } else {
                echo "   - ⚠️  يحتاج مراجعة يدوية\n";
            }
        } else {
            $stats['no_match']++;
            echo "\n❌ لم يتم العثور على مطابقة في قاعدة البيانات\n";
            echo "   - يحتاج إنشاء قسم جديد أو مراجعة يدوية\n";
        }
        
        echo "\n";
        
    } catch (Exception $e) {
        echo "❌ خطأ في المعالجة: " . $e->getMessage() . "\n\n";
    }
}

// عرض الإحصائيات النهائية
echo str_repeat("=", 80) . "\n";
echo "📊 الإحصائيات النهائية\n";
echo str_repeat("=", 80) . "\n\n";

echo "إجمالي الكتب المختبرة: {$stats['total']}\n";
echo "تم استخراج قسم: {$stats['extracted']} (" . round(($stats['extracted'] / $stats['total']) * 100, 1) . "%)\n";
echo "تم العثور على مطابقة: {$stats['matched']} (" . round(($stats['matched'] / $stats['total']) * 100, 1) . "%)\n\n";

echo "توزيع الثقة:\n";
echo "  ✅ ثقة عالية (≥80%): {$stats['high_confidence']}\n";
echo "  ⚠️  ثقة متوسطة (60-79%): {$stats['medium_confidence']}\n";
echo "  ⚠️  ثقة منخفضة (<60%): {$stats['low_confidence']}\n";
echo "  ❌ بدون مطابقة: {$stats['no_match']}\n\n";

// معدل النجاح
$successRate = $stats['total'] > 0 
    ? round((($stats['high_confidence'] + $stats['medium_confidence']) / $stats['total']) * 100, 1)
    : 0;

echo "معدل النجاح الإجمالي: {$successRate}%\n";

if ($stats['high_confidence'] >= 7) {
    echo "\n✅ النتائج ممتازة! يمكن المتابعة للخطوة التالية.\n";
} elseif ($stats['high_confidence'] >= 5) {
    echo "\n⚠️  النتائج جيدة ولكن تحتاج بعض التحسين.\n";
} else {
    echo "\n❌ النتائج تحتاج تحسين كبير في الأنماط.\n";
}

echo "\n";
