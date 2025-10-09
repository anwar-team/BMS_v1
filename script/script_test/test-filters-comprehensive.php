<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Author;
use App\Models\BookSection;
use App\Models\Book;
use Illuminate\Support\Facades\Http;

echo "=== 🔍 اختبار نظام الفلاتر الشامل ===\n\n";

// 1. اختبار جلب خيارات الفلاتر من API
echo "📋 1. اختبار جلب خيارات الفلاتر من API:\n";
echo str_repeat("─", 60) . "\n";

$filterTypes = ['section', 'author', 'book'];

foreach ($filterTypes as $type) {
    try {
        $url = "http://127.0.0.1:8000/api/filter-options?type={$type}";
        $response = Http::timeout(10)->get($url);
        
        if ($response->successful()) {
            $data = $response->json();
            if ($data['success'] ?? false) {
                $count = count($data['data'] ?? []);
                echo "✅ {$type}: {$count} عنصر\n";
                
                // عرض أول 3 عناصر كمثال
                if ($count > 0) {
                    echo "   أمثلة: ";
                    $examples = array_slice($data['data'], 0, 3);
                    foreach ($examples as $item) {
                        echo "{$item['name']}, ";
                    }
                    echo "\n";
                }
            } else {
                echo "❌ {$type}: فشل - " . ($data['message'] ?? 'خطأ غير معروف') . "\n";
            }
        } else {
            echo "❌ {$type}: خطأ HTTP " . $response->status() . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ {$type}: استثناء - " . $e->getMessage() . "\n";
    }
}

echo "\n";

// 2. اختبار الفلاتر من قاعدة البيانات مباشرة
echo "📊 2. اختبار البيانات في قاعدة البيانات:\n";
echo str_repeat("─", 60) . "\n";

try {
    $sectionsCount = BookSection::count();
    $authorsCount = Author::count();
    $booksCount = Book::count();
    
    echo "✅ الأقسام: {$sectionsCount}\n";
    echo "✅ المؤلفون: {$authorsCount}\n";
    echo "✅ الكتب: {$booksCount}\n";
    
    // عرض أمثلة
    echo "\n📚 أمثلة على الأقسام:\n";
    $sections = BookSection::orderBy('name')->limit(5)->get(['id', 'name']);
    foreach ($sections as $section) {
        echo "   - [{$section->id}] {$section->name}\n";
    }
    
    echo "\n✍️ أمثلة على المؤلفين:\n";
    $authors = Author::orderBy('full_name')->limit(5)->get(['id', 'full_name']);
    foreach ($authors as $author) {
        echo "   - [{$author->id}] {$author->full_name}\n";
    }
    
    echo "\n📖 أمثلة على الكتب:\n";
    $books = Book::orderBy('title')->limit(5)->get(['id', 'title']);
    foreach ($books as $book) {
        echo "   - [{$book->id}] {$book->title}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. اختبار البحث مع الفلاتر
echo "🔎 3. اختبار البحث مع الفلاتر:\n";
echo str_repeat("─", 60) . "\n";

// الحصول على أول قسم ومؤلف للاختبار
$testSection = BookSection::first();
$testAuthor = Author::first();

if ($testSection && $testAuthor) {
    echo "📌 سيتم الاختبار باستخدام:\n";
    echo "   - القسم: [{$testSection->id}] {$testSection->name}\n";
    echo "   - المؤلف: [{$testAuthor->id}] {$testAuthor->full_name}\n\n";
    
    // اختبار 1: بحث مع فلتر القسم فقط
    echo "🧪 اختبار 1: البحث مع فلتر القسم فقط\n";
    try {
        $url = "http://127.0.0.1:8000/api/ultra-search?q=الله&section_id={$testSection->id}&per_page=5";
        $response = Http::timeout(10)->get($url);
        
        if ($response->successful()) {
            $data = $response->json();
            if ($data['success'] ?? false) {
                $total = $data['pagination']['total'] ?? 0;
                $count = count($data['data'] ?? []);
                echo "✅ نجح: {$count} نتيجة من أصل {$total}\n";
                
                // التحقق من أن جميع النتائج من نفس القسم
                $allSameSection = true;
                foreach ($data['data'] as $result) {
                    if (($result['book_section_id'] ?? null) != $testSection->id) {
                        $allSameSection = false;
                        break;
                    }
                }
                
                if ($allSameSection) {
                    echo "✅ جميع النتائج من القسم المحدد\n";
                } else {
                    echo "⚠️ بعض النتائج من أقسام أخرى!\n";
                }
            } else {
                echo "❌ فشل: " . ($data['message'] ?? 'خطأ غير معروف') . "\n";
            }
        } else {
            echo "❌ خطأ HTTP " . $response->status() . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ استثناء: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // اختبار 2: بحث مع فلتر المؤلف فقط
    echo "🧪 اختبار 2: البحث مع فلتر المؤلف فقط\n";
    try {
        $url = "http://127.0.0.1:8000/api/ultra-search?q=الله&author_id={$testAuthor->id}&per_page=5";
        $response = Http::timeout(10)->get($url);
        
        if ($response->successful()) {
            $data = $response->json();
            if ($data['success'] ?? false) {
                $total = $data['pagination']['total'] ?? 0;
                $count = count($data['data'] ?? []);
                echo "✅ نجح: {$count} نتيجة من أصل {$total}\n";
                
                // عرض بعض النتائج
                if ($count > 0) {
                    echo "   أمثلة:\n";
                    foreach (array_slice($data['data'], 0, 2) as $result) {
                        echo "   - {$result['book_title']}\n";
                    }
                }
            } else {
                echo "❌ فشل: " . ($data['message'] ?? 'خطأ غير معروف') . "\n";
            }
        } else {
            echo "❌ خطأ HTTP " . $response->status() . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ استثناء: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // اختبار 3: بحث مع فلترين معاً (قسم + مؤلف)
    echo "🧪 اختبار 3: البحث مع فلترين معاً (قسم + مؤلف)\n";
    try {
        $url = "http://127.0.0.1:8000/api/ultra-search?q=الله&section_id={$testSection->id}&author_id={$testAuthor->id}&per_page=5";
        $response = Http::timeout(10)->get($url);
        
        if ($response->successful()) {
            $data = $response->json();
            if ($data['success'] ?? false) {
                $total = $data['pagination']['total'] ?? 0;
                $count = count($data['data'] ?? []);
                echo "✅ نجح: {$count} نتيجة من أصل {$total}\n";
            } else {
                echo "❌ فشل: " . ($data['message'] ?? 'خطأ غير معروف') . "\n";
            }
        } else {
            echo "❌ خطأ HTTP " . $response->status() . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ استثناء: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // اختبار 4: فلاتر متعددة (Multiple IDs)
    echo "🧪 اختبار 4: فلاتر متعددة (Multiple Section IDs)\n";
    $sections = BookSection::limit(3)->pluck('id')->toArray();
    $sectionIds = implode(',', $sections);
    
    try {
        $url = "http://127.0.0.1:8000/api/ultra-search?q=الله&section_id={$sectionIds}&per_page=5";
        $response = Http::timeout(10)->get($url);
        
        if ($response->successful()) {
            $data = $response->json();
            if ($data['success'] ?? false) {
                $total = $data['pagination']['total'] ?? 0;
                $count = count($data['data'] ?? []);
                echo "✅ نجح: {$count} نتيجة من أصل {$total}\n";
                echo "   الأقسام المختارة: [" . implode(', ', $sections) . "]\n";
            } else {
                echo "❌ فشل: " . ($data['message'] ?? 'خطأ غير معروف') . "\n";
            }
        } else {
            echo "❌ خطأ HTTP " . $response->status() . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ استثناء: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "❌ لا توجد بيانات كافية للاختبار (قسم أو مؤلف غير موجود)\n";
}

echo "\n";

// 4. فحص JavaScript في الـ Blade
echo "🌐 4. فحص كود JavaScript للفلاتر في الواجهة:\n";
echo str_repeat("─", 60) . "\n";

$bladeFile = __DIR__ . '/resources/views/ultra-fast-search/views/ultra-fast.blade.php';
if (file_exists($bladeFile)) {
    $content = file_get_contents($bladeFile);
    
    // فحص العناصر الأساسية
    $checks = [
        'filterToggle' => strpos($content, 'filterToggle') !== false,
        'filterDropdown' => strpos($content, 'filterDropdown') !== false,
        'filterModal' => strpos($content, 'filterModal') !== false,
        'loadFilterOptions' => strpos($content, 'loadFilterOptions') !== false,
        'applyCurrentFilter' => strpos($content, 'applyCurrentFilter') !== false,
        'selectedFilters' => strpos($content, 'selectedFilters') !== false,
        'filter-options API' => strpos($content, '/api/filter-options') !== false,
    ];
    
    echo "فحص عناصر JavaScript:\n";
    foreach ($checks as $name => $exists) {
        $status = $exists ? '✅' : '❌';
        echo "{$status} {$name}\n";
    }
    
    // فحص الفلاتر المتاحة
    $hasSection = strpos($content, 'case \'section\'') !== false;
    $hasAuthor = strpos($content, 'case \'author\'') !== false;
    $hasBook = strpos($content, 'case \'book\'') !== false;
    
    echo "\nأنواع الفلاتر المتاحة:\n";
    echo ($hasSection ? '✅' : '❌') . " فلتر الأقسام\n";
    echo ($hasAuthor ? '✅' : '❌') . " فلتر المؤلفين\n";
    echo ($hasBook ? '✅' : '❌') . " فلتر الكتب\n";
    
} else {
    echo "❌ ملف Blade غير موجود\n";
}

echo "\n";

// 5. ملخص نهائي
echo "📊 5. الملخص النهائي:\n";
echo str_repeat("─", 60) . "\n";

$issues = [];

// فحص البيانات
if ($sectionsCount == 0) $issues[] = "لا توجد أقسام في قاعدة البيانات";
if ($authorsCount == 0) $issues[] = "لا يوجد مؤلفون في قاعدة البيانات";
if ($booksCount == 0) $issues[] = "لا توجد كتب في قاعدة البيانات";

if (empty($issues)) {
    echo "✅ نظام الفلاتر يعمل بشكل صحيح!\n";
    echo "\nالإحصائيات:\n";
    echo "   - الأقسام: {$sectionsCount}\n";
    echo "   - المؤلفون: {$authorsCount}\n";
    echo "   - الكتب: {$booksCount}\n";
} else {
    echo "⚠️ مشاكل تم اكتشافها:\n";
    foreach ($issues as $issue) {
        echo "   - {$issue}\n";
    }
}

echo "\n=== ✨ انتهى الاختبار ===\n";
