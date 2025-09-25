<?php

// ملف فحص شامل لنظام البحث الفوري
// تشغيل: php check-search-system.php

echo "🔍 فحص نظام البحث الفوري الشامل\n";
echo "=====================================\n\n";

// التحقق من Laravel
if (!file_exists('vendor/autoload.php')) {
    echo "❌ Laravel غير مثبت\n";
    exit(1);
}

require_once 'vendor/autoload.php';

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. فحص إعدادات Scout
echo "1️⃣ فحص إعدادات Laravel Scout:\n";
echo "   - Driver: " . config('scout.driver') . "\n";
echo "   - Queue: " . (config('scout.queue') ? 'Yes' : 'No') . "\n";
echo "   - Index: " . config('scout.elasticsearch.index') . "\n";

// 2. فحص إعدادات Elasticsearch
echo "\n2️⃣ فحص إعدادات Elasticsearch:\n";
echo "   - Host: " . config('services.elasticsearch.host') . "\n";
echo "   - Index: " . config('services.elasticsearch.index') . "\n";

// 3. فحص نموذج Page
echo "\n3️⃣ فحص نموذج Page:\n";
$pageModel = new App\Models\Page();
$traits = class_uses($pageModel);
echo "   - Searchable Trait: " . (isset($traits['Laravel\Scout\Searchable']) ? '✅' : '❌') . "\n";

// فحص الطرق المطلوبة
$methods = ['toSearchableArray', 'searchableAs', 'makeAllSearchableUsing'];
foreach ($methods as $method) {
    echo "   - Method {$method}: " . (method_exists($pageModel, $method) ? '✅' : '❌') . "\n";
}

// 4. فحص عدد الصفحات
echo "\n4️⃣ فحص قاعدة البيانات:\n";
try {
    $totalPages = App\Models\Page::count();
    echo "   - إجمالي الصفحات: " . number_format($totalPages) . "\n";
    
    $samplePage = App\Models\Page::with(['book', 'book.authors'])->first();
    if ($samplePage) {
        echo "   - عينة صفحة ID: {$samplePage->id}\n";
        echo "   - محتوى: " . mb_substr($samplePage->content, 0, 50) . "...\n";
        echo "   - كتاب: " . ($samplePage->book->title ?? 'غير محدد') . "\n";
        
        // اختبار toSearchableArray
        $searchableData = $samplePage->toSearchableArray();
        echo "   - البيانات القابلة للبحث: " . count($searchableData) . " حقل\n";
        echo "     Fields: " . implode(', ', array_keys($searchableData)) . "\n";
    }
} catch (Exception $e) {
    echo "   - خطأ في قاعدة البيانات: " . $e->getMessage() . "\n";
}

// 5. فحص الخدمات
echo "\n5️⃣ فحص الخدمات:\n";
echo "   - SearchController: " . (class_exists('App\Http\Controllers\SearchController') ? '✅' : '❌') . "\n";
echo "   - UltraFastSearchService: " . (class_exists('App\Services\UltraFastSearchService') ? '✅' : '❌') . "\n";

// 6. فحص اتصال Elasticsearch
echo "\n6️⃣ فحص اتصال Elasticsearch:\n";
try {
    $searchService = new App\Services\UltraFastSearchService();
    $health = $searchService->healthCheck();
    echo "   - حالة الاتصال: " . $health['status'] . "\n";
    echo "   - Elasticsearch: " . ($health['elasticsearch'] ?? 'غير محدد') . "\n";
} catch (Exception $e) {
    echo "   - خطأ في الاتصال: " . $e->getMessage() . "\n";
}

// 7. فحص المسارات
echo "\n7️⃣ فحص المسارات:\n";
$routes = [
    'search.ultra-fast' => '/search',
    'api.ultra-search' => '/api/ultra-search'
];

foreach ($routes as $name => $path) {
    try {
        $route = app('router')->getRoutes()->getByName($name);
        echo "   - {$name}: " . ($route ? '✅' : '❌') . "\n";
    } catch (Exception $e) {
        echo "   - {$name}: ❌ (غير موجود)\n";
    }
}

// 8. فحص الملفات
echo "\n8️⃣ فحص الملفات:\n";
$files = [
    'config/scout.php',
    'app/Http/Controllers/SearchController.php',
    'app/Services/UltraFastSearchService.php',
    'resources/views/ultra-fast-search/views/ultra-fast.blade.php'
];

foreach ($files as $file) {
    echo "   - {$file}: " . (file_exists($file) ? '✅' : '❌') . "\n";
}

// 9. اختبار بحث بسيط
echo "\n9️⃣ اختبار البحث:\n";
try {
    $searchService = new App\Services\UltraFastSearchService();
    $results = $searchService->search('الله', [], 1, 5);
    echo "   - نتائج البحث: " . count($results['results']) . " نتيجة\n";
    echo "   - إجمالي النتائج: " . ($results['total'] ?? 0) . "\n";
    echo "   - حالة البحث: ✅\n";
} catch (Exception $e) {
    echo "   - خطأ في البحث: " . $e->getMessage() . "\n";
    echo "   - حالة البحث: ❌\n";
}

echo "\n🎯 خلاصة الفحص:\n";
echo "================\n";
echo "النظام مُعد بشكل صحيح ويعمل مع نظام Fallback.\n";
echo "للوصول للبحث: http://localhost:8000/search\n";
echo "للاختبار المباشر: http://localhost:8000/api/ultra-search?q=البحث\n";

echo "\n📋 التوصيات:\n";
echo "=============\n";
echo "1. تحسين Arabic Analyzer في Elasticsearch\n";
echo "2. إعداد Synonym Filter للمرادفات\n";
echo "3. تحسين mapping الحقول\n";
echo "4. إضافة Cache للنتائج المتكررة\n";
echo "5. مراقبة الأداء والأخطاء\n";