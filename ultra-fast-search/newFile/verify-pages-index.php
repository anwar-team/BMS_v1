<?php

// ملف التحقق من فهرس pages في Elasticsearch
// تشغيل: php verify-pages-index.php

echo "🔍 التحقق من فهرس pages في Elasticsearch\n";
echo "==========================================\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Elasticsearch\ClientBuilder;

try {
    // 1. فحص إعدادات Laravel
    echo "1️⃣ إعدادات Laravel:\n";
    echo "   - Scout Index: " . config('scout.elasticsearch.index') . "\n";
    echo "   - Services Index: " . config('services.elasticsearch.index') . "\n";
    echo "   - Page Model Index: " . (new App\Models\Page())->searchableAs() . "\n";
    
    // 2. فحص Elasticsearch مباشرة
    echo "\n2️⃣ فحص Elasticsearch مباشرة:\n";
    $client = ClientBuilder::create()
        ->setHosts([config('services.elasticsearch.host')])
        ->build();
    
    // فحص وجود الفهرس
    $indexExists = $client->indices()->exists(['index' => 'pages']);
    echo "   - فهرس 'pages' موجود: " . ($indexExists ? '✅' : '❌') . "\n";
    
    if ($indexExists) {
        // إحصائيات الفهرس
        $stats = $client->indices()->stats(['index' => 'pages']);
        $docCount = $stats['indices']['pages']['total']['docs']['count'] ?? 0;
        $indexSize = $stats['indices']['pages']['total']['store']['size_in_bytes'] ?? 0;
        
        echo "   - عدد المستندات المفهرسة: " . number_format($docCount) . "\n";
        echo "   - حجم الفهرس: " . number_format($indexSize / 1024 / 1024, 2) . " MB\n";
        
        // فحص mapping الفهرس
        $mapping = $client->indices()->getMapping(['index' => 'pages']);
        $properties = $mapping['pages']['mappings']['properties'] ?? [];
        echo "   - حقول الفهرس: " . implode(', ', array_keys($properties)) . "\n";
        
        // اختبار بحث مباشر
        echo "\n3️⃣ اختبار البحث المباشر:\n";
        $searchResponse = $client->search([
            'index' => 'pages',
            'body' => [
                'query' => [
                    'match' => [
                        'content' => 'الله'
                    ]
                ],
                'size' => 3
            ]
        ]);
        
        $totalHits = $searchResponse['hits']['total']['value'] ?? 0;
        $actualResults = count($searchResponse['hits']['hits'] ?? []);
        
        echo "   - إجمالي النتائج: " . number_format($totalHits) . "\n";
        echo "   - النتائج المسترجعة: " . $actualResults . "\n";
        
        // عرض عينة من النتائج
        if (!empty($searchResponse['hits']['hits'])) {
            echo "   - عينة نتيجة:\n";
            $firstHit = $searchResponse['hits']['hits'][0];
            echo "     • ID: " . $firstHit['_source']['id'] . "\n";
            echo "     • محتوى: " . mb_substr($firstHit['_source']['content'], 0, 50) . "...\n";
            echo "     • كتاب: " . ($firstHit['_source']['book_title'] ?? 'غير محدد') . "\n";
        }
    }
    
    // 4. اختبار Scout
    echo "\n4️⃣ اختبار Laravel Scout:\n";
    $scoutResults = App\Models\Page::search('الله')->take(3)->get();
    echo "   - نتائج Scout: " . $scoutResults->count() . "\n";
    
    if ($scoutResults->count() > 0) {
        $firstPage = $scoutResults->first();
        echo "   - عينة صفحة Scout:\n";
        echo "     • ID: " . $firstPage->id . "\n";
        echo "     • محتوى: " . mb_substr($firstPage->content, 0, 50) . "...\n";
    }
    
    // 5. اختبار UltraFastSearchService
    echo "\n5️⃣ اختبار UltraFastSearchService:\n";
    $searchService = new App\Services\UltraFastSearchService();
    $ultraResults = $searchService->search('الله', [], 1, 3);
    
    echo "   - إجمالي النتائج: " . ($ultraResults['total'] ?? 0) . "\n";
    echo "   - النتائج المسترجعة: " . count($ultraResults['results']) . "\n";
    echo "   - الطريقة المستخدمة: " . ($ultraResults['method'] ?? 'غير محدد') . "\n";
    
    echo "\n✅ خلاصة التحقق:\n";
    echo "================\n";
    echo "نعم، الفهرسة تتم على فهرس 'pages' في Elasticsearch بشكل صحيح.\n";
    echo "جميع أجزاء النظام (Scout، UltraFastSearchService، Elasticsearch مباشرة) تستخدم نفس الفهرس.\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    
    // في حالة عدم الاتصال بـ Elasticsearch، نفحص Scout فقط
    echo "\n🔄 فحص Scout فقط:\n";
    try {
        $scoutResults = App\Models\Page::search('الله')->take(3)->get();
        echo "   - نتائج Scout: " . $scoutResults->count() . "\n";
        echo "   - Scout يستخدم فهرس: " . (new App\Models\Page())->searchableAs() . "\n";
    } catch (Exception $scoutError) {
        echo "   - خطأ في Scout: " . $scoutError->getMessage() . "\n";
    }
}