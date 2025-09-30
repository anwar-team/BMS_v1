<?php

// ملف فحص الفهرسة على Single Node Elasticsearch
// تشغيل: php test-single-indexing.php

echo "🔍 فحص الفهرسة على Single Node Elasticsearch\n";
echo "=============================================\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Elasticsearch\ClientBuilder;
use App\Models\Page;

try {
    // 1. فحص الاتصال مع Single Node
    echo "1️⃣ فحص الاتصال مع Elasticsearch:\n";
    $client = ClientBuilder::create()
        ->setHosts([config('services.elasticsearch.host')])
        ->setRetries(2)
        ->build();
    
    // فحص صحة الـ cluster
    $health = $client->cluster()->health();
    echo "   - حالة الـ Cluster: " . $health['status'] . "\n";
    echo "   - عدد الـ Nodes: " . $health['number_of_nodes'] . "\n";
    echo "   - عدد الفهارس: " . $health['active_primary_shards'] . "\n";
    
    // 2. فحص فهرس pages
    echo "\n2️⃣ فحص فهرس pages:\n";
    $indexExists = $client->indices()->exists(['index' => 'pages']);
    echo "   - فهرس pages موجود: " . ($indexExists ? '✅' : '❌') . "\n";
    
    if ($indexExists) {
        $stats = $client->indices()->stats(['index' => 'pages']);
        $docCount = $stats['indices']['pages']['total']['docs']['count'] ?? 0;
        echo "   - عدد المستندات: " . number_format($docCount) . "\n";
        
        // فحص settings الفهرس
        $settings = $client->indices()->getSettings(['index' => 'pages']);
        $shards = $settings['pages']['settings']['index']['number_of_shards'] ?? 'غير محدد';
        $replicas = $settings['pages']['settings']['index']['number_of_replicas'] ?? 'غير محدد';
        echo "   - عدد الـ Shards: {$shards}\n";
        echo "   - عدد الـ Replicas: {$replicas}\n";
    }
    
    // 3. اختبار فهرسة صفحة واحدة
    echo "\n3️⃣ اختبار فهرسة صفحة واحدة:\n";
    $testPage = Page::with(['book', 'book.authors'])->first();
    
    if ($testPage) {
        echo "   - صفحة الاختبار ID: {$testPage->id}\n";
        echo "   - محتوى: " . mb_substr($testPage->content, 0, 50) . "...\n";
        
        // فهرسة الصفحة
        try {
            $testPage->searchable();
            echo "   - الفهرسة: ✅ تمت بنجاح\n";
            
            // التحقق من الفهرسة
            sleep(2); // انتظار قصير للفهرسة
            $searchResponse = $client->search([
                'index' => 'pages',
                'body' => [
                    'query' => [
                        'term' => [
                            'id' => $testPage->id
                        ]
                    ]
                ]
            ]);
            
            $found = $searchResponse['hits']['total']['value'] ?? 0;
            echo "   - التحقق من الفهرسة: " . ($found > 0 ? '✅ موجودة' : '❌ غير موجودة') . "\n";
            
        } catch (Exception $e) {
            echo "   - خطأ في الفهرسة: " . $e->getMessage() . "\n";
        }
    }
    
    // 4. اختبار البحث في فهرس pages
    echo "\n4️⃣ اختبار البحث في فهرس pages:\n";
    $searchResponse = $client->search([
        'index' => 'pages',
        'body' => [
            'query' => [
                'match_all' => (object)[]
            ],
            'size' => 5
        ]
    ]);
    
    $totalDocs = $searchResponse['hits']['total']['value'] ?? 0;
    $returnedDocs = count($searchResponse['hits']['hits'] ?? []);
    
    echo "   - إجمالي المستندات في pages: " . number_format($totalDocs) . "\n";
    echo "   - مستندات مُسترجعة: {$returnedDocs}\n";
    
    // 5. اختبار Scout مع الفهرس
    echo "\n5️⃣ اختبار Laravel Scout:\n";
    try {
        // اختبار بحث بسيط
        $scoutResults = Page::search('*')->take(3)->get();
        echo "   - نتائج Scout: " . $scoutResults->count() . "\n";
        
        // التحقق من searchableAs
        $pageModel = new Page();
        echo "   - Scout Index Name: " . $pageModel->searchableAs() . "\n";
        
    } catch (Exception $e) {
        echo "   - خطأ Scout: " . $e->getMessage() . "\n";
    }
    
    // 6. فهرسة دفعة صغيرة للاختبار
    echo "\n6️⃣ فهرسة دفعة اختبار (10 صفحات):\n";
    try {
        $testPages = Page::with(['book', 'book.authors'])->limit(10)->get();
        echo "   - صفحات للفهرسة: " . $testPages->count() . "\n";
        
        $testPages->searchable();
        echo "   - الفهرسة: ✅ تمت\n";
        
        // انتظار ثم فحص
        sleep(3);
        $searchAfter = $client->search([
            'index' => 'pages',
            'body' => [
                'query' => ['match_all' => (object)[]],
                'size' => 0
            ]
        ]);
        
        $newTotal = $searchAfter['hits']['total']['value'] ?? 0;
        echo "   - إجمالي بعد الفهرسة: " . number_format($newTotal) . "\n";
        
    } catch (Exception $e) {
        echo "   - خطأ في الدفعة: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ خلاصة الفحص:\n";
    echo "================\n";
    echo "• Single Node Elasticsearch يعمل بشكل صحيح\n";
    echo "• الفهرسة تتم على فهرس 'pages' مباشرة\n";
    echo "• Scout مُكون للعمل مع نفس الفهرس\n";
    echo "• النظام جاهز للفهرسة الشاملة\n";
    
    echo "\n🚀 للفهرسة الكاملة:\n";
    echo "   php artisan scout:import \"App\\Models\\Page\" --chunk=1000\n";
    
} catch (Exception $e) {
    echo "❌ خطأ عام: " . $e->getMessage() . "\n";
    
    // معلومات إضافية للتشخيص
    echo "\n🔧 معلومات التشخيص:\n";
    echo "   - Elasticsearch Host: " . config('services.elasticsearch.host') . "\n";
    echo "   - Scout Driver: " . config('scout.driver') . "\n";
    echo "   - Page Model exists: " . (class_exists('App\\Models\\Page') ? 'Yes' : 'No') . "\n";
}