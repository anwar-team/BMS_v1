#!/usr/bin/env php
<?php

/**
 * سكريبت تحليل أوصاف الكتب واستخراج المعلومات المنظمة
 * يقوم بفحص حقل description في جدول books واستخراج المعلومات القابلة للهيكلة
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 تحليل أوصاف الكتب واستخراج المعلومات المنظمة...\n\n";
echo str_repeat("=", 100) . "\n";

// 1. إحصائيات عامة
echo "📊 إحصائيات عامة:\n\n";

$totalBooks = DB::table('books')->count();
$booksWithDescription = DB::table('books')->whereNotNull('description')->where('description', '!=', '')->count();
$booksWithoutDescription = $totalBooks - $booksWithDescription;

echo "إجمالي الكتب: " . number_format($totalBooks) . "\n";
echo "كتب لديها وصف: " . number_format($booksWithDescription) . " (" . round($booksWithDescription/$totalBooks*100, 2) . "%)\n";
echo "كتب بدون وصف: " . number_format($booksWithoutDescription) . " (" . round($booksWithoutDescription/$totalBooks*100, 2) . "%)\n";

// 2. متوسط طول الوصف
$avgDescLength = DB::table('books')
    ->whereNotNull('description')
    ->where('description', '!=', '')
    ->selectRaw('AVG(LENGTH(description)) as avg_length')
    ->value('avg_length');

echo "\nمتوسط طول الوصف: " . round($avgDescLength) . " حرف\n";

echo "\n" . str_repeat("-", 100) . "\n\n";

// 3. تحليل نماذج من الأوصاف
echo "📝 نماذج من أوصاف الكتب (أول 20 كتاب):\n\n";

$sampleBooks = DB::table('books')
    ->select('id', 'title', 'description', 'publisher_id', 'book_section_id', 'edition', 'edition_DATA')
    ->whereNotNull('description')
    ->where('description', '!=', '')
    ->limit(20)
    ->get();

$patterns = [
    'author' => [],
    'edition' => [],
    'publisher' => [],
    'year' => [],
    'section' => [],
    'tahqeeq' => [],
    'pages' => [],
    'volumes' => [],
    'other' => []
];

foreach ($sampleBooks as $index => $book) {
    echo str_repeat("-", 100) . "\n";
    echo "الكتاب #" . ($index + 1) . " (ID: {$book->id})\n";
    echo "العنوان: {$book->title}\n";
    echo "الوصف:\n";
    echo wordwrap($book->description, 90, "\n") . "\n";
    
    // تحليل الوصف للبحث عن أنماط
    $desc = $book->description;
    
    // البحث عن المؤلف
    if (preg_match('/(?:المؤلف|تأليف|للمؤلف|للشيخ|للإمام|للعلامة|لـ)\s*[:：]\s*([^\n\r]+)/u', $desc, $matches)) {
        $patterns['author'][] = trim($matches[1]);
        echo "✓ مؤلف محتمل: " . trim($matches[1]) . "\n";
    }
    
    // البحث عن الطبعة
    if (preg_match('/(?:الطبعة|طبعة|ط)\s*[:：]?\s*([^\n\r،]+)/u', $desc, $matches)) {
        $patterns['edition'][] = trim($matches[1]);
        echo "✓ طبعة محتملة: " . trim($matches[1]) . "\n";
    }
    
    // البحث عن الناشر
    if (preg_match('/(?:الناشر|دار النشر|نشر|دار)\s*[:：]?\s*([^\n\r،]+)/u', $desc, $matches)) {
        $patterns['publisher'][] = trim($matches[1]);
        echo "✓ ناشر محتمل: " . trim($matches[1]) . "\n";
    }
    
    // البحث عن السنة
    if (preg_match('/(?:سنة النشر|عام|السنة)\s*[:：]?\s*(\d{4})/u', $desc, $matches)) {
        $patterns['year'][] = $matches[1];
        echo "✓ سنة محتملة: " . $matches[1] . "\n";
    } elseif (preg_match('/(\d{4})\s*(?:هـ|م)/u', $desc, $matches)) {
        $patterns['year'][] = $matches[1];
        echo "✓ سنة محتملة: " . $matches[1] . "\n";
    }
    
    // البحث عن التحقيق
    if (preg_match('/(?:تحقيق|المحقق|حققه)\s*[:：]?\s*([^\n\r]+)/u', $desc, $matches)) {
        $patterns['tahqeeq'][] = trim($matches[1]);
        echo "✓ محقق محتمل: " . trim($matches[1]) . "\n";
    }
    
    // البحث عن عدد الصفحات
    if (preg_match('/(\d+)\s*صفحة/u', $desc, $matches)) {
        $patterns['pages'][] = $matches[1];
        echo "✓ عدد الصفحات: " . $matches[1] . "\n";
    }
    
    // البحث عن عدد المجلدات
    if (preg_match('/(\d+)\s*(?:مجلد|مجلدات|جزء|أجزاء)/u', $desc, $matches)) {
        $patterns['volumes'][] = $matches[1];
        echo "✓ عدد المجلدات: " . $matches[1] . "\n";
    }
    
    // البحث عن القسم
    if (preg_match('/(?:القسم|التصنيف|الموضوع)\s*[:：]?\s*([^\n\r،]+)/u', $desc, $matches)) {
        $patterns['section'][] = trim($matches[1]);
        echo "✓ قسم محتمل: " . trim($matches[1]) . "\n";
    }
    
    echo "\n";
}

echo "\n" . str_repeat("=", 100) . "\n\n";

// 4. ملخص الأنماط المكتشفة
echo "📊 ملخص الأنماط المكتشفة:\n\n";

echo "المؤلفون المستخرجون: " . count($patterns['author']) . "\n";
if (count($patterns['author']) > 0) {
    echo "  أمثلة: " . implode(" | ", array_slice($patterns['author'], 0, 3)) . "\n";
}

echo "\nالطبعات المستخرجة: " . count($patterns['edition']) . "\n";
if (count($patterns['edition']) > 0) {
    echo "  أمثلة: " . implode(" | ", array_slice($patterns['edition'], 0, 3)) . "\n";
}

echo "\nالناشرون المستخرجون: " . count($patterns['publisher']) . "\n";
if (count($patterns['publisher']) > 0) {
    echo "  أمثلة: " . implode(" | ", array_slice($patterns['publisher'], 0, 3)) . "\n";
}

echo "\nالسنوات المستخرجة: " . count($patterns['year']) . "\n";
if (count($patterns['year']) > 0) {
    echo "  أمثلة: " . implode(" | ", array_slice($patterns['year'], 0, 3)) . "\n";
}

echo "\nالمحققون المستخرجون: " . count($patterns['tahqeeq']) . "\n";
if (count($patterns['tahqeeq']) > 0) {
    echo "  أمثلة: " . implode(" | ", array_slice($patterns['tahqeeq'], 0, 3)) . "\n";
}

echo "\n" . str_repeat("=", 100) . "\n\n";

// 5. البحث عن أنماط شائعة في الأوصاف
echo "🔍 الكلمات المفتاحية الشائعة في الأوصاف:\n\n";

$keywords = [
    'المؤلف', 'تأليف', 'الطبعة', 'الناشر', 'دار النشر', 'تحقيق', 'المحقق',
    'سنة النشر', 'صفحة', 'مجلد', 'جزء', 'القسم', 'التصنيف'
];

foreach ($keywords as $keyword) {
    $count = DB::table('books')
        ->whereNotNull('description')
        ->where('description', 'LIKE', "%{$keyword}%")
        ->count();
    
    $percentage = $booksWithDescription > 0 ? round($count/$booksWithDescription*100, 2) : 0;
    echo sprintf("%-20s: %6d كتاب (%5.2f%%)\n", $keyword, $count, $percentage);
}

echo "\n" . str_repeat("=", 100) . "\n\n";

// 6. فحص الكتب التي لها معلومات في الوصف ولكن الحقول فارغة
echo "⚠️  الكتب التي لديها معلومات في الوصف ولكن الحقول المناسبة فارغة:\n\n";

// كتب بها ذكر للمؤلف في الوصف لكن لا مؤلف مربوط
$booksWithAuthorInDesc = DB::select("
    SELECT COUNT(*) as count
    FROM books b
    WHERE (b.description LIKE '%المؤلف%' 
        OR b.description LIKE '%تأليف%'
        OR b.description LIKE '%للمؤلف%')
    AND NOT EXISTS (
        SELECT 1 FROM author_book ab WHERE ab.book_id = b.id
    )
")[0]->count;

echo "كتب بها ذكر للمؤلف في الوصف لكن لا مؤلف مربوط: {$booksWithAuthorInDesc}\n";

// كتب بها ذكر للناشر في الوصف لكن publisher_id فارغ
$booksWithPublisherInDesc = DB::table('books')
    ->whereNull('publisher_id')
    ->where(function($q) {
        $q->where('description', 'LIKE', '%الناشر%')
          ->orWhere('description', 'LIKE', '%دار النشر%')
          ->orWhere('description', 'LIKE', '%نشر%');
    })
    ->count();

echo "كتب بها ذكر للناشر في الوصف لكن publisher_id فارغ: {$booksWithPublisherInDesc}\n";

// كتب بها ذكر للطبعة في الوصف لكن edition فارغ
$booksWithEditionInDesc = DB::table('books')
    ->where(function($q) {
        $q->whereNull('edition')->orWhere('edition', 0);
    })
    ->where(function($q) {
        $q->where('description', 'LIKE', '%الطبعة%')
          ->orWhere('description', 'LIKE', '%طبعة%');
    })
    ->count();

echo "كتب بها ذكر للطبعة في الوصف لكن edition فارغ: {$booksWithEditionInDesc}\n";

echo "\n" . str_repeat("=", 100) . "\n\n";

// 7. تحليل بنية الوصف
echo "📋 تحليل بنية الوصف:\n\n";

$descriptionStructures = [
    'يحتوي على نقاط' => 0,
    'يحتوي على أسطر متعددة' => 0,
    'يحتوي على معلومات منظمة (: أو -)' => 0,
    'نص حر بدون هيكلة' => 0
];

$sampleForStructure = DB::table('books')
    ->select('description')
    ->whereNotNull('description')
    ->where('description', '!=', '')
    ->limit(100)
    ->get();

foreach ($sampleForStructure as $book) {
    if (strpos($book->description, '•') !== false || strpos($book->description, '*') !== false) {
        $descriptionStructures['يحتوي على نقاط']++;
    }
    
    if (substr_count($book->description, "\n") > 2) {
        $descriptionStructures['يحتوي على أسطر متعددة']++;
    }
    
    if (strpos($book->description, ':') !== false || strpos($book->description, '：') !== false || 
        strpos($book->description, '-') !== false) {
        $descriptionStructures['يحتوي على معلومات منظمة (: أو -)']++;
    }
    
    if (strpos($book->description, ':') === false && 
        strpos($book->description, '：') === false && 
        strpos($book->description, '-') === false &&
        substr_count($book->description, "\n") <= 2) {
        $descriptionStructures['نص حر بدون هيكلة']++;
    }
}

foreach ($descriptionStructures as $type => $count) {
    $percentage = round($count/100*100, 2);
    echo sprintf("%-40s: %3d (%5.2f%%)\n", $type, $count, $percentage);
}

echo "\n" . str_repeat("=", 100) . "\n\n";

// 8. التوصيات
echo "💡 التوصيات:\n\n";

echo "1. استخراج المعلومات المنظمة:\n";
echo "   - يمكن استخراج معلومات المؤلف، الناشر، الطبعة من الوصف باستخدام Regular Expressions\n";
echo "   - نسبة النجاح المتوقعة: 60-70% للأوصاف المنظمة\n\n";

echo "2. إنشاء جداول إضافية:\n";
echo "   - book_extracted_metadata: لحفظ المعلومات المستخرجة من الوصف\n";
echo "   - book_description_keywords: لحفظ الكلمات المفتاحية المستخرجة\n\n";

echo "3. عملية الاستخراج:\n";
echo "   - تحليل الأنماط الشائعة في الأوصاف\n";
echo "   - استخراج البيانات باستخدام AI/NLP أو Regular Expressions\n";
echo "   - مراجعة يدوية للنتائج\n";
echo "   - تحديث الجداول الرئيسية\n\n";

echo "4. الخطوات التالية:\n";
echo "   - فحص عينة أكبر (1000-5000 كتاب)\n";
echo "   - بناء قاموس بالأنماط الشائعة\n";
echo "   - تطوير سكريبت استخراج تلقائي\n";
echo "   - واجهة مراجعة للبيانات المستخرجة\n\n";

echo str_repeat("=", 100) . "\n";
echo "\n✨ اكتمل التحليل!\n";
echo "📅 التاريخ: " . date('Y-m-d H:i:s') . "\n\n";
