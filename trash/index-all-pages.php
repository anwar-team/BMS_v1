<?php

/**
 * فهرسة جميع الصفحات في Elasticsearch
 * يستخدم batches صغيرة لتجنب مشكلة الذاكرة
 */

require __DIR__.'/vendor/autoload.php';

// تحميل Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Page;
use Elasticsearch\ClientBuilder;

// زيادة حد الذاكرة
ini_set('memory_limit', '512M');

$indexName = 'pages_new_search';
$batchSize = 500; // حجم كل batch

echo "════════════════════════════════════════════════════════\n";
echo "   فهرسة كاملة لجميع الصفحات\n";
echo "════════════════════════════════════════════════════════\n\n";

// إنشاء اتصال Elasticsearch
$client = ClientBuilder::create()
    ->setHosts([env('ELASTICSEARCH_HOST', 'http://145.223.98.97:9201')])
    ->build();

// الحصول على العدد الإجمالي
$totalPages = Page::count();
echo "📊 العدد الإجمالي للصفحات: " . number_format($totalPages) . "\n";
echo "📦 حجم ال Batch: " . $batchSize . " صفحة\n";
echo "🔄 عدد ال Batches: " . ceil($totalPages / $batchSize) . "\n\n";

$indexed = 0;
$errors = 0;
$startTime = microtime(true);

// معالجة كل batch
Page::with(['book', 'book.authors'])
    ->chunkById($batchSize, function ($pages) use ($client, $indexName, &$indexed, &$errors, $totalPages, $startTime) {
        $params = ['body' => []];
        
        foreach ($pages as $page) {
            try {
                $searchableData = $page->toSearchableArray();
                
                // إضافة للـ bulk request
                $params['body'][] = [
                    'index' => [
                        '_index' => $indexName,
                        '_id' => $page->id
                    ]
                ];
                $params['body'][] = $searchableData;
                
            } catch (Exception $e) {
                $errors++;
                echo "❌ خطأ في الصفحة {$page->id}: " . $e->getMessage() . "\n";
            }
        }
        
        // تنفيذ الـ bulk request
        if (!empty($params['body'])) {
            try {
                $response = $client->bulk($params);
                
                if (isset($response['errors']) && $response['errors']) {
                    foreach ($response['items'] as $item) {
                        if (isset($item['index']['error'])) {
                            $errors++;
                        }
                    }
                }
                
                $indexed += count($pages);
                
                // حساب التقدم
                $percentage = ($indexed / $totalPages) * 100;
                $elapsed = microtime(true) - $startTime;
                $rate = $indexed / $elapsed;
                $remaining = ($totalPages - $indexed) / $rate;
                
                // شريط التقدم
                $bar = str_repeat('█', (int)($percentage / 2));
                $space = str_repeat('░', 50 - (int)($percentage / 2));
                
                echo "\r";
                echo sprintf(
                    "[%s%s] %0.1f%% | %s / %s | %d/s | باقي %s",
                    $bar,
                    $space,
                    $percentage,
                    number_format($indexed),
                    number_format($totalPages),
                    (int)$rate,
                    gmdate('H:i:s', (int)$remaining)
                );
                
            } catch (Exception $e) {
                echo "\n❌ خطأ في bulk request: " . $e->getMessage() . "\n";
                $errors += count($pages);
            }
        }
        
        // تنظيف الذاكرة
        $params = ['body' => []];
        gc_collect_cycles();
        
    }, 'id');

echo "\n\n════════════════════════════════════════════════════════\n";
echo "   النتائج النهائية\n";
echo "════════════════════════════════════════════════════════\n";
echo "✅ تم فهرسة: " . number_format($indexed) . " صفحة\n";
echo "❌ أخطاء: " . $errors . "\n";
echo "📊 نسبة النجاح: " . round(($indexed / $totalPages) * 100, 2) . "%\n";
echo "⏱️  الوقت الإجمالي: " . gmdate('H:i:s', (int)(microtime(true) - $startTime)) . "\n";

// تحديث Index
echo "\n🔄 جاري تحديث Index...\n";
try {
    $client->indices()->refresh(['index' => $indexName]);
    echo "✅ تم تحديث Index بنجاح\n";
} catch (Exception $e) {
    echo "❌ فشل تحديث Index: " . $e->getMessage() . "\n";
}

// فحص العدد النهائي
try {
    $count = $client->count(['index' => $indexName]);
    echo "\n📊 العدد النهائي في Elasticsearch: " . number_format($count['count']) . "\n";
} catch (Exception $e) {
    echo "\n❌ فشل فحص العدد: " . $e->getMessage() . "\n";
}

echo "════════════════════════════════════════════════════════\n";
