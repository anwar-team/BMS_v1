<?php

require __DIR__.'/vendor/autoload.php';

// تحميل Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Page;
use Elasticsearch\ClientBuilder;

$elasticsearchHost = getenv('ELASTICSEARCH_HOST') ?: 'http://145.223.98.97:9201';
$indexName = 'pages_new_search';
$sampleSize = 100; // عدد الصفحات للاختبار

echo "════════════════════════════════════════════════════════\n";
echo "   فهرسة بيانات تجريبية ($sampleSize صفحة)\n";
echo "════════════════════════════════════════════════════════\n\n";

// جلب بيانات تجريبية
echo "📚 جاري جلب البيانات من قاعدة البيانات...\n";
$pages = Page::with(['book', 'book.authors'])
    ->limit($sampleSize)
    ->get();

echo "✅ تم جلب {$pages->count()} صفحة من قاعدة البيانات\n\n";

// إنشاء اتصال Elasticsearch
$client = ClientBuilder::create()
    ->setHosts([$elasticsearchHost])
    ->setSSLVerification(false)
    ->build();

// التحقق من وجود Index
try {
    if (!$client->indices()->exists(['index' => $indexName])) {
        echo "❌ Index غير موجود: $indexName\n";
        echo "   قم بتشغيل: php create-new-search-index.php\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

$indexedCount = 0;
$errors = 0;

echo "📤 جاري الفهرسة...\n";
$progressBar = 0;

foreach ($pages as $page) {
    try {
        $searchableData = $page->toSearchableArray();
        
        $params = [
            'index' => $indexName,
            'id' => $page->id,
            'body' => $searchableData
        ];
        
        $client->index($params);
        $indexedCount++;
        
        // Progress bar
        $progressBar++;
        if ($progressBar % 10 == 0) {
            echo "   ✓ فُهرس $progressBar من {$pages->count()}\n";
        }
        
    } catch (Exception $e) {
        $errors++;
        echo "   ✗ خطأ في فهرسة الصفحة {$page->id}: " . $e->getMessage() . "\n";
    }
}

echo "\n════════════════════════════════════════════════════════\n";
echo "   النتائج\n";
echo "════════════════════════════════════════════════════════\n";
echo "✅ تم فهرسة: $indexedCount صفحة\n";
echo "❌ أخطاء: $errors\n";
echo "📊 نسبة النجاح: " . round(($indexedCount / $pages->count()) * 100, 2) . "%\n\n";

// تحديث Index للتأكد من توفر البيانات للبحث
echo "🔄 جاري تحديث Index...\n";
try {
    $client->indices()->refresh(['index' => $indexName]);
    echo "✅ تم تحديث Index بنجاح\n\n";
} catch (Exception $e) {
    echo "❌ فشل تحديث Index: " . $e->getMessage() . "\n\n";
}

echo "════════════════════════════════════════════════════════\n";
echo "   الخطوة التالية: اختبار البحث\n";
echo "════════════════════════════════════════════════════════\n";
echo "php test-search-types.php\n";
echo "════════════════════════════════════════════════════════\n";
