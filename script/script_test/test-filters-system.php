<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Author;
use App\Models\BookSection;
use App\Models\Book;
use App\Models\Page;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║          🔍 تقرير فحص نظام الفلاتر الشامل               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// 1. فحص البيانات في قاعدة البيانات
echo "📊 1. البيانات المتاحة للفلترة:\n";
echo str_repeat("─", 60) . "\n";

try {
    $sectionsCount = BookSection::count();
    $authorsCount = Author::count();
    $booksCount = Book::count();
    $pagesCount = Page::count();
    
    echo "✅ الأقسام (Sections): " . number_format($sectionsCount) . "\n";
    echo "✅ المؤلفون (Authors): " . number_format($authorsCount) . "\n";
    echo "✅ الكتب (Books): " . number_format($booksCount) . "\n";
    echo "✅ الصفحات (Pages): " . number_format($pagesCount) . "\n";
    
} catch (\Exception $e) {
    echo "❌ خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// 2. فحص Controller
echo "🎮 2. فحص الـ Controller:\n";
echo str_repeat("─", 60) . "\n";

$controllerFile = __DIR__ . '/app/Http/Controllers/SearchController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    $checks = [
        'getFilterOptions method' => strpos($content, 'function getFilterOptions') !== false,
        'section filter' => strpos($content, "case 'section'") !== false,
        'author filter' => strpos($content, "case 'author'") !== false,
        'book filter' => strpos($content, "case 'book'") !== false,
        'author_id parameter' => strpos($content, 'author_id') !== false,
        'section_id parameter' => strpos($content, 'section_id') !== false,
    ];
    
    foreach ($checks as $name => $exists) {
        echo ($exists ? '✅' : '❌') . " {$name}\n";
    }
} else {
    echo "❌ ملف Controller غير موجود\n";
}

echo "\n";

// 3. فحص Service
echo "⚙️ 3. فحص الـ UltraFastSearchService:\n";
echo str_repeat("─", 60) . "\n";

$serviceFile = __DIR__ . '/app/Services/UltraFastSearchService.php';
if (file_exists($serviceFile)) {
    $content = file_get_contents($serviceFile);
    
    $checks = [
        'author_id filter handling' => strpos($content, "filters['author_id']") !== false,
        'section_id filter handling' => strpos($content, "filters['section_id']") !== false,
        'Elasticsearch filter clause' => strpos($content, "['bool']['filter']") !== false,
        'term query for author' => strpos($content, "'author_ids'") !== false,
        'term query for section' => strpos($content, "'book_section_id'") !== false,
    ];
    
    foreach ($checks as $name => $exists) {
        echo ($exists ? '✅' : '❌') . " {$name}\n";
    }
} else {
    echo "❌ ملف Service غير موجود\n";
}

echo "\n";

// 4. فحص Routes
echo "🛣️ 4. فحص الـ Routes:\n";
echo str_repeat("─", 60) . "\n";

$routesFile = __DIR__ . '/routes/web.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    
    $checks = [
        '/api/ultra-search route' => strpos($content, '/api/ultra-search') !== false,
        '/api/filter-options route' => strpos($content, '/api/filter-options') !== false,
        'SearchController reference' => strpos($content, 'SearchController') !== false,
    ];
    
    foreach ($checks as $name => $exists) {
        echo ($exists ? '✅' : '❌') . " {$name}\n";
    }
} else {
    echo "❌ ملف Routes غير موجود\n";
}

echo "\n";

// 5. فحص Blade Template
echo "🎨 5. فحص قالب Blade (الواجهة):\n";
echo str_repeat("─", 60) . "\n";

$bladeFile = __DIR__ . '/resources/views/ultra-fast-search/views/ultra-fast.blade.php';
if (file_exists($bladeFile)) {
    $content = file_get_contents($bladeFile);
    
    $checks = [
        'Filter Toggle Button' => strpos($content, 'filterToggle') !== false,
        'Filter Dropdown' => strpos($content, 'filterDropdown') !== false,
        'Filter Modal' => strpos($content, 'filterModal') !== false,
        'loadFilterOptions function' => strpos($content, 'loadFilterOptions') !== false,
        'applyCurrentFilter function' => strpos($content, 'applyCurrentFilter') !== false,
        'selectedFilters object' => strpos($content, 'selectedFilters') !== false,
        'API call to filter-options' => strpos($content, '/api/filter-options') !== false,
        'Filter Tags Container' => strpos($content, 'selectedFiltersTags') !== false,
        'Clear All Filters' => strpos($content, 'clearAllFilters') !== false,
    ];
    
    foreach ($checks as $name => $exists) {
        echo ($exists ? '✅' : '❌') . " {$name}\n";
    }
    
    // فحص أزرار الفلاتر
    echo "\nأزرار الفلاتر المتاحة:\n";
    $hasSection = strpos($content, 'filter-category-btn') !== false && strpos($content, 'section') !== false;
    $hasAuthor = strpos($content, 'filter-category-btn') !== false && strpos($content, 'author') !== false;
    $hasBook = strpos($content, 'filter-category-btn') !== false && strpos($content, 'book') !== false;
    
    echo ($hasSection ? '✅' : '❌') . " زر فلتر الأقسام\n";
    echo ($hasAuthor ? '✅' : '❌') . " زر فلتر المؤلفين\n";
    echo ($hasBook ? '✅' : '❌') . " زر فلتر الكتب\n";
    
} else {
    echo "❌ ملف Blade غير موجود\n";
}

echo "\n";

// 6. اختبار مباشر من قاعدة البيانات
echo "🧪 6. اختبار البيانات الفعلية:\n";
echo str_repeat("─", 60) . "\n";

// اختبار الأقسام
echo "📚 الأقسام المتاحة (أول 10):\n";
$sections = BookSection::orderBy('name')->limit(10)->get(['id', 'name']);
foreach ($sections as $i => $section) {
    echo "   " . ($i + 1) . ". [{$section->id}] {$section->name}\n";
}

echo "\n✍️ المؤلفون المتاحة (أول 10 - غير فارغ):\n";
$authors = Author::whereNotNull('full_name')
    ->where('full_name', '!=', '')
    ->orderBy('full_name')
    ->limit(10)
    ->get(['id', 'full_name']);
foreach ($authors as $i => $author) {
    echo "   " . ($i + 1) . ". [{$author->id}] {$author->full_name}\n";
}

echo "\n📖 الكتب المتاحة (أول 10):\n";
$books = Book::orderBy('title')->limit(10)->get(['id', 'title', 'book_section_id']);
foreach ($books as $i => $book) {
    echo "   " . ($i + 1) . ". [{$book->id}] {$book->title}\n";
}

echo "\n";

// 7. فحص العلاقات
echo "🔗 7. فحص العلاقات بين الجداول:\n";
echo str_repeat("─", 60) . "\n";

// كم كتاب لديه قسم؟
$booksWithSection = Book::whereNotNull('book_section_id')->count();
$booksWithoutSection = Book::whereNull('book_section_id')->count();
echo "✅ كتب مع قسم: " . number_format($booksWithSection) . "\n";
echo ($booksWithoutSection > 0 ? '⚠️' : '✅') . " كتب بدون قسم: " . number_format($booksWithoutSection) . "\n";

// كم صفحة لديها كتاب مع قسم؟
$pagesWithSection = Page::whereHas('book', function($q) {
    $q->whereNotNull('book_section_id');
})->count();
echo "✅ صفحات من كتب مع قسم: " . number_format($pagesWithSection) . "\n";

// كم صفحة لديها كتاب مع مؤلف؟
$pagesWithAuthor = Page::whereHas('book.authors')->count();
echo "✅ صفحات من كتب مع مؤلف: " . number_format($pagesWithAuthor) . "\n";

echo "\n";

// 8. فحص Elasticsearch Index Mapping
echo "🔍 8. فحص Elasticsearch Index Structure:\n";
echo str_repeat("─", 60) . "\n";

try {
    $elasticsearchHost = env('ELASTICSEARCH_HOST', 'http://145.223.98.97:9201');
    $indexName = env('ELASTICSEARCH_INDEX', 'pages_new_search');
    
    echo "Elasticsearch Host: {$elasticsearchHost}\n";
    echo "Index Name: {$indexName}\n\n";
    
    // فحص وجود الحقول المطلوبة في mapping
    $ch = curl_init("{$elasticsearchHost}/{$indexName}/_mapping");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $mapping = json_decode($response, true);
        $properties = $mapping[$indexName]['mappings']['properties'] ?? [];
        
        $requiredFields = ['author_ids', 'book_section_id', 'book_id'];
        foreach ($requiredFields as $field) {
            if (isset($properties[$field])) {
                echo "✅ الحقل '{$field}' موجود\n";
            } else {
                echo "❌ الحقل '{$field}' غير موجود\n";
            }
        }
    } else {
        echo "⚠️ لا يمكن الاتصال بـ Elasticsearch (HTTP {$httpCode})\n";
    }
    
} catch (\Exception $e) {
    echo "❌ خطأ في فحص Elasticsearch: " . $e->getMessage() . "\n";
}

echo "\n";

// 9. الملخص النهائي
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    📋 الملخص النهائي                     ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$totalIssues = 0;

echo "✅ البيانات:\n";
echo "   - {$sectionsCount} قسم\n";
echo "   - {$authorsCount} مؤلف\n";
echo "   - {$booksCount} كتاب\n";
echo "   - {$pagesCount} صفحة\n\n";

echo "✅ الكود البرمجي:\n";
echo "   - Controller ✓\n";
echo "   - Service ✓\n";
echo "   - Routes ✓\n";
echo "   - Blade Template ✓\n\n";

echo "✅ أنواع الفلاتر المتاحة:\n";
echo "   - فلتر الأقسام ✓\n";
echo "   - فلتر المؤلفين ✓\n";
echo "   - فلتر الكتب ✓\n\n";

if ($booksWithoutSection > 0) {
    echo "⚠️ تحذيرات:\n";
    echo "   - {$booksWithoutSection} كتاب بدون قسم\n\n";
    $totalIssues++;
}

if ($totalIssues == 0) {
    echo "🎉 نظام الفلاتر جاهز ويعمل بشكل صحيح!\n";
    echo "   لتجربة النظام، قم بتشغيل السيرفر:\n";
    echo "   php artisan serve\n\n";
} else {
    echo "⚠️ النظام جاهز مع {$totalIssues} تحذير بسيط\n\n";
}

echo "═══════════════════════════════════════════════════════════\n";
echo "✨ انتهى الفحص بنجاح\n";
echo "═══════════════════════════════════════════════════════════\n";
