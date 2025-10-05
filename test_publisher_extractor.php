<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;
use App\Services\PublisherExtractor;

echo "========================================\n";
echo "اختبار استخراج ومطابقة الناشرين\n";
echo "========================================\n\n";

// إنشاء instance من الـ Extractor
$extractor = new PublisherExtractor();

// جلب 10 كتب عشوائية لديها وصف
$books = Book::whereNotNull('description')
    ->where('description', '!=', '')
    ->inRandomOrder()
    ->limit(10)
    ->get();

echo "عدد الكتب للاختبار: " . $books->count() . "\n\n";

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
    $descLines = explode("\n", $book->description);
    $relevantLines = array_filter($descLines, function($line) {
        return mb_stripos($line, 'الناشر') !== false 
            || mb_stripos($line, 'دار النشر') !== false
            || mb_stripos($line, 'المطبعة') !== false;
    });
    
    if (!empty($relevantLines)) {
        echo "\nالسطر المتعلق بالناشر:\n";
        echo implode("\n", array_slice($relevantLines, 0, 3)) . "\n";
    }
    
    echo "\n";
    
    // معالجة الكتاب
    try {
        $result = $extractor->process($book->description);
        
        if (!$result) {
            echo "❌ لم يتم استخراج ناشر من الوصف\n\n";
            continue;
        }
        
        $stats['extracted']++;
        
        echo "📋 الناشر المستخرج: \"{$result['extracted_publisher_name']}\"\n";
        
        if ($result['matched_publisher_id']) {
            $stats['matched']++;
            
            $matchDetails = $result['match_details'];
            $confidence = $result['publisher_match_confidence'];
            
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
            echo "   - الناشر المطابق: {$matchDetails['publisher_name']}\n";
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
            echo "   - يحتاج إنشاء ناشر جديد أو مراجعة يدوية\n";
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
echo "تم استخراج ناشر: {$stats['extracted']} (" . round(($stats['extracted'] / $stats['total']) * 100, 1) . "%)\n";
echo "تم العثور على مطابقة: {$stats['matched']} (" . ($stats['total'] > 0 ? round(($stats['matched'] / $stats['total']) * 100, 1) : 0) . "%)\n\n";

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

if ($stats['extracted'] >= 8) {
    echo "\n✅ معدل الاستخراج ممتاز! ({$stats['extracted']}/10)\n";
}

if ($stats['high_confidence'] >= 6) {
    echo "✅ معدل المطابقة عالي! ({$stats['high_confidence']} مطابقات عالية الثقة)\n";
} elseif ($stats['high_confidence'] >= 4) {
    echo "⚠️  معدل المطابقة جيد ولكن يمكن تحسينه. ({$stats['high_confidence']} مطابقات عالية الثقة)\n";
} else {
    echo "❌ معدل المطابقة يحتاج تحسين. ({$stats['high_confidence']} مطابقات فقط)\n";
}

echo "\n";
