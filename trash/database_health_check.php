#!/usr/bin/env php
<?php

/**
 * سكريبت فحص صحة قاعدة البيانات - BMS
 * يقوم بفحص شامل لقاعدة البيانات وإصدار تقرير تفصيلي
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔍 بدء فحص صحة قاعدة البيانات...\n\n";
echo str_repeat("=", 80) . "\n";

$report = [];
$issues = [];
$warnings = [];
$successes = [];

// 1. فحص الاتصال بقاعدة البيانات
echo "📡 فحص الاتصال بقاعدة البيانات...\n";
try {
    DB::connection()->getPdo();
    $dbName = DB::connection()->getDatabaseName();
    echo "✅ الاتصال ناجح: {$dbName}\n";
    $successes[] = "الاتصال بقاعدة البيانات ناجح";
} catch (\Exception $e) {
    echo "❌ فشل الاتصال: " . $e->getMessage() . "\n";
    $issues[] = "فشل الاتصال بقاعدة البيانات";
    exit(1);
}

echo str_repeat("-", 80) . "\n\n";

// 2. فحص الجداول الأساسية
echo "📋 فحص الجداول الأساسية...\n";
$requiredTables = ['books', 'pages', 'chapters', 'authors', 'volumes', 'publishers'];
foreach ($requiredTables as $table) {
    if (Schema::hasTable($table)) {
        $count = DB::table($table)->count();
        echo "✅ جدول {$table}: {$count} سجل\n";
        $report[$table . '_count'] = $count;
    } else {
        echo "❌ جدول {$table}: غير موجود!\n";
        $issues[] = "جدول {$table} غير موجود";
    }
}

echo str_repeat("-", 80) . "\n\n";

// 3. فحص سلامة البيانات
echo "🔍 فحص سلامة البيانات...\n";

// 3.1 الصفحات الفارغة
echo "\n📄 فحص الصفحات:\n";
$emptyPages = DB::table('pages')
    ->whereNull('content')
    ->orWhere('content', '')
    ->count();

if ($emptyPages > 0) {
    echo "⚠️  صفحات فارغة: {$emptyPages}\n";
    $warnings[] = "{$emptyPages} صفحة بمحتوى فارغ";
} else {
    echo "✅ لا توجد صفحات فارغة\n";
}

// 3.2 الصفحات بدون عدد كلمات
$pagesWithoutWordCount = DB::table('pages')
    ->whereNull('word_count')
    ->orWhere('word_count', 0)
    ->count();

if ($pagesWithoutWordCount > 0) {
    echo "⚠️  صفحات بدون عدد كلمات: {$pagesWithoutWordCount}\n";
    $warnings[] = "{$pagesWithoutWordCount} صفحة بدون عدد كلمات";
} else {
    echo "✅ جميع الصفحات لديها عدد كلمات\n";
}

// 3.3 الكتب بدون صفحات
echo "\n📚 فحص الكتب:\n";
$booksWithoutPages = DB::select("
    SELECT b.id, b.title, b.pages_count,
           (SELECT COUNT(*) FROM pages p WHERE p.book_id = b.id) as actual_pages
    FROM books b
    HAVING actual_pages = 0
");

if (count($booksWithoutPages) > 0) {
    echo "⚠️  كتب بدون صفحات: " . count($booksWithoutPages) . "\n";
    $warnings[] = count($booksWithoutPages) . " كتاب بدون صفحات";
    
    // عرض أول 5 كتب
    echo "   أمثلة:\n";
    foreach (array_slice($booksWithoutPages, 0, 5) as $book) {
        echo "   - {$book->title} (ID: {$book->id})\n";
    }
} else {
    echo "✅ جميع الكتب لديها صفحات\n";
}

// 3.4 تطابق عدد الصفحات
$booksWithMismatchedCount = DB::select("
    SELECT subquery.id, subquery.title, subquery.pages_count, subquery.actual_pages
    FROM (
        SELECT b.id, b.title, b.pages_count,
               (SELECT COUNT(*) FROM pages p WHERE p.book_id = b.id) as actual_pages
        FROM books b
    ) AS subquery
    WHERE subquery.pages_count != subquery.actual_pages AND subquery.actual_pages > 0
");

if (count($booksWithMismatchedCount) > 0) {
    echo "⚠️  كتب بتضارب في عدد الصفحات: " . count($booksWithMismatchedCount) . "\n";
    $warnings[] = count($booksWithMismatchedCount) . " كتاب بتضارب في عدد الصفحات";
    
    // عرض أول 5 كتب
    echo "   أمثلة:\n";
    foreach (array_slice($booksWithMismatchedCount, 0, 5) as $book) {
        echo "   - {$book->title}: متوقع {$book->pages_count}، فعلي {$book->actual_pages}\n";
    }
} else {
    echo "✅ عدد الصفحات متطابق لجميع الكتب\n";
}

// 3.5 المؤلفون المكررون
echo "\n👤 فحص المؤلفين:\n";
$duplicateAuthors = DB::select("
    SELECT full_name, COUNT(*) as count
    FROM authors
    GROUP BY full_name
    HAVING count > 1
");

if (count($duplicateAuthors) > 0) {
    echo "⚠️  مؤلفون مكررون: " . count($duplicateAuthors) . "\n";
    $warnings[] = count($duplicateAuthors) . " مؤلف مكرر";
    
    // عرض أول 5
    echo "   أمثلة:\n";
    foreach (array_slice($duplicateAuthors, 0, 5) as $author) {
        echo "   - {$author->full_name} ({$author->count} مرات)\n";
    }
} else {
    echo "✅ لا يوجد مؤلفون مكررون\n";
}

// 3.6 الفصول بدون صفحات
echo "\n📖 فحص الفصول:\n";
$chaptersWithoutPages = DB::select("
    SELECT c.id, c.title, c.page_start, c.page_end,
           (SELECT COUNT(*) FROM pages p WHERE p.chapter_id = c.id) as actual_pages
    FROM chapters c
    HAVING actual_pages = 0
");

if (count($chaptersWithoutPages) > 0) {
    echo "⚠️  فصول بدون صفحات: " . count($chaptersWithoutPages) . "\n";
    $warnings[] = count($chaptersWithoutPages) . " فصل بدون صفحات";
} else {
    echo "✅ جميع الفصول لديها صفحات\n";
}

echo str_repeat("-", 80) . "\n\n";

// 4. إحصائيات عامة
echo "📊 إحصائيات عامة:\n\n";

// 4.1 إجمالي الكتب والصفحات
$totalBooks = DB::table('books')->count();
$totalPages = DB::table('pages')->count();
$totalAuthors = DB::table('authors')->count();
$totalChapters = DB::table('chapters')->count();
$totalVolumes = DB::table('volumes')->count();

echo "📚 إجمالي الكتب: " . number_format($totalBooks) . "\n";
echo "📄 إجمالي الصفحات: " . number_format($totalPages) . "\n";
echo "👤 إجمالي المؤلفين: " . number_format($totalAuthors) . "\n";
echo "📖 إجمالي الفصول: " . number_format($totalChapters) . "\n";
echo "📕 إجمالي المجلدات: " . number_format($totalVolumes) . "\n\n";

// 4.2 متوسطات
if ($totalBooks > 0) {
    $avgPagesPerBook = round($totalPages / $totalBooks, 2);
    echo "📊 متوسط الصفحات لكل كتاب: {$avgPagesPerBook}\n";
}

$totalWords = DB::table('pages')->sum('word_count');
if ($totalPages > 0) {
    $avgWordsPerPage = round($totalWords / $totalPages, 2);
    echo "📊 متوسط الكلمات لكل صفحة: {$avgWordsPerPage}\n";
}

echo "\n📊 إجمالي الكلمات: " . number_format($totalWords) . "\n";

// 4.3 توزيع الصفحات حسب عدد الكلمات
echo "\n📈 توزيع الصفحات حسب طول المحتوى:\n";
$distribution = DB::select("
    SELECT 
        CASE 
            WHEN word_count < 100 THEN 'قصيرة جداً (<100)'
            WHEN word_count < 500 THEN 'قصيرة (100-500)'
            WHEN word_count < 2000 THEN 'متوسطة (500-2000)'
            WHEN word_count < 5000 THEN 'طويلة (2000-5000)'
            ELSE 'طويلة جداً (>5000)'
        END as category,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM pages), 2) as percentage
    FROM pages
    WHERE word_count IS NOT NULL
    GROUP BY category
    ORDER BY count DESC
");

foreach ($distribution as $dist) {
    $bar = str_repeat('█', (int)($dist->percentage / 2));
    printf("   %-25s %6d (%5.2f%%) %s\n", 
        $dist->category, 
        $dist->count, 
        $dist->percentage,
        $bar
    );
}

echo str_repeat("-", 80) . "\n\n";

// 5. فحص الفهارس
echo "🔍 فحص الفهارس:\n";

$tables = ['pages', 'books', 'chapters', 'authors'];
foreach ($tables as $table) {
    $indexes = DB::select("SHOW INDEX FROM {$table}");
    echo "\n📋 جدول {$table}:\n";
    
    $indexNames = [];
    foreach ($indexes as $index) {
        if (!in_array($index->Key_name, $indexNames)) {
            $indexNames[] = $index->Key_name;
            $indexType = $index->Key_name === 'PRIMARY' ? 'PRIMARY' : 
                        ($index->Index_type === 'FULLTEXT' ? 'FULLTEXT' : 'INDEX');
            echo "   ✓ {$index->Key_name} ({$indexType})\n";
        }
    }
}

echo str_repeat("-", 80) . "\n\n";

// 6. فحص حجم الجداول
echo "💾 أحجام الجداول:\n\n";

$tableSizes = DB::select("
    SELECT 
        table_name,
        ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb,
        ROUND(data_length / 1024 / 1024, 2) AS data_mb,
        ROUND(index_length / 1024 / 1024, 2) AS index_mb,
        table_rows
    FROM information_schema.TABLES
    WHERE table_schema = DATABASE()
    AND table_name IN ('books', 'pages', 'chapters', 'authors', 'volumes', 'activity_log')
    ORDER BY (data_length + index_length) DESC
");

printf("%-20s %12s %12s %12s %12s\n", 
    'الجدول', 'الحجم الكلي', 'البيانات', 'الفهارس', 'الصفوف'
);
echo str_repeat("-", 72) . "\n";

$totalSize = 0;
foreach ($tableSizes as $size) {
    printf("%-20s %9.2f MB %9.2f MB %9.2f MB %12s\n", 
        $size->table_name,
        $size->size_mb,
        $size->data_mb,
        $size->index_mb,
        number_format($size->table_rows)
    );
    $totalSize += $size->size_mb;
}
echo str_repeat("-", 72) . "\n";
printf("%-20s %9.2f MB\n", 'الإجمالي', $totalSize);

echo "\n" . str_repeat("=", 80) . "\n\n";

// 7. ملخص النتائج
echo "📝 ملخص النتائج:\n\n";

echo "✅ نجاحات (" . count($successes) . "):\n";
foreach ($successes as $success) {
    echo "   • {$success}\n";
}

if (count($warnings) > 0) {
    echo "\n⚠️  تحذيرات (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   • {$warning}\n";
    }
}

if (count($issues) > 0) {
    echo "\n❌ مشاكل حرجة (" . count($issues) . "):\n";
    foreach ($issues as $issue) {
        echo "   • {$issue}\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";

// 8. التوصيات
echo "\n💡 التوصيات:\n\n";

if ($emptyPages > 0) {
    echo "1. تنظيف الصفحات الفارغة:\n";
    echo "   UPDATE pages SET content = '[محتوى غير متوفر]' WHERE content IS NULL OR content = '';\n\n";
}

if ($pagesWithoutWordCount > 0) {
    echo "2. تحديث عدد الكلمات:\n";
    echo "   UPDATE pages SET word_count = (LENGTH(content) - LENGTH(REPLACE(content, ' ', '')) + 1) WHERE word_count IS NULL OR word_count = 0;\n\n";
}

if (count($booksWithMismatchedCount) > 0) {
    echo "3. تصحيح عدد صفحات الكتب:\n";
    echo "   UPDATE books b SET pages_count = (SELECT COUNT(*) FROM pages p WHERE p.book_id = b.id);\n\n";
}

if (count($duplicateAuthors) > 0) {
    echo "4. مراجعة المؤلفين المكررين يدوياً لدمجهم\n\n";
}

// إضافة توصية لإنشاء الفهارس
$hasFulltextOnPages = false;
foreach ($indexes as $index) {
    if ($index->Index_type === 'FULLTEXT') {
        $hasFulltextOnPages = true;
        break;
    }
}

if (!$hasFulltextOnPages) {
    echo "5. إضافة فهارس FULLTEXT للبحث السريع:\n";
    echo "   ALTER TABLE pages ADD FULLTEXT ft_content (content);\n";
    echo "   ALTER TABLE pages ADD FULLTEXT ft_content_part (content, part);\n";
    echo "   ALTER TABLE books ADD FULLTEXT ft_title_desc (title, description);\n\n";
}

echo str_repeat("=", 80) . "\n";
echo "\n✨ اكتمل الفحص بنجاح!\n";
echo "📅 التاريخ: " . date('Y-m-d H:i:s') . "\n\n";

// حفظ التقرير في ملف
$reportFile = __DIR__ . '/database_health_report_' . date('Y-m-d_His') . '.txt';
ob_start();
// يمكن إعادة تشغيل السكريبت وحفظ المخرجات
ob_end_clean();

echo "💾 لحفظ التقرير، قم بإعادة توجيه المخرجات:\n";
echo "   php database_health_check.php > report.txt\n\n";
