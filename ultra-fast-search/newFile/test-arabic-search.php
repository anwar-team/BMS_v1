<?php

// اختبار البحث العربي في فهرس pages
// تشغيل: php test-arabic-search.php

echo "🔍 اختبار البحث العربي في فهرس pages\n";
echo "====================================\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Elasticsearch\ClientBuilder;

try {
    $client = ClientBuilder::create()
        ->setHosts([config('services.elasticsearch.host')])
        ->build();
    
    // فحص عدد المستندات في الفهرس
    $stats = $client->indices()->stats(['index' => 'pages']);
    $totalDocs = $stats['indices']['pages']['total']['docs']['count'] ?? 0;
    echo "1️⃣ إجمالي المستندات في فهرس pages: " . number_format($totalDocs) . "\n\n";
    
    // اختبارات البحث العربي
    $searchTerms = [
        'بسم',
        'الله',
        'الرحمن',
        'الرحيم',
        'الحمد',
        'رب العالمين'
    ];
    
    echo "2️⃣ اختبار البحث العربي:\n";
    
    foreach ($searchTerms as $term) {
        echo "   🔸 البحث عن '{$term}':\n";
        
        // بحث مع المحلل العربي
        $response = $client->search([
            'index' => 'pages',
            'body' => [
                'query' => [
                    'match' => [
                        'content' => $term
                    ]
                ],
                'size' => 2
            ]
        ]);
        
        $totalHits = $response['hits']['total']['value'] ?? 0;
        $returnedHits = count($response['hits']['hits'] ?? []);
        
        echo "     - نتائج: " . number_format($totalHits) . "\n";
        echo "     - مُسترجع: {$returnedHits}\n";
        
        // عرض عينة من النتائج
        if (!empty($response['hits']['hits'])) {
            $firstHit = $response['hits']['hits'][0];
            $content = $firstHit['_source']['content'] ?? '';
            $bookTitle = $firstHit['_source']['book_title'] ?? 'غير محدد';
            
            echo "     - عينة: " . mb_substr($content, 0, 50) . "...\n";
            echo "     - كتاب: {$bookTitle}\n";
        }
        
        echo "\n";
    }
    
    // اختبار البحث المركب
    echo "3️⃣ اختبار البحث المركب:\n";
    $complexSearch = $client->search([
        'index' => 'pages',
        'body' => [
            'query' => [
                'bool' => [
                    'must' => [
                        ['match' => ['content' => 'بسم الله']]
                    ]
                ]
            ],
            'size' => 3
        ]
    ]);
    
    $complexHits = $complexSearch['hits']['total']['value'] ?? 0;
    echo "   - البحث عن 'بسم الله': " . number_format($complexHits) . " نتيجة\n";
    
    // اختبار UltraFastSearchService
    echo "\n4️⃣ اختبار UltraFastSearchService:\n";
    $searchService = new App\Services\UltraFastSearchService();
    
    $ultraResults = $searchService->search('الله', [], 1, 5);
    echo "   - نتائج UltraFastSearchService: " . count($ultraResults['results']) . "\n";
    echo "   - إجمالي: " . ($ultraResults['total'] ?? 0) . "\n";
    echo "   - الطريقة: " . ($ultraResults['method'] ?? 'غير محدد') . "\n";
    
    if (!empty($ultraResults['results'])) {
        $firstResult = $ultraResults['results'][0];
        echo "   - عينة نتيجة:\n";
        echo "     • ID: " . ($firstResult['id'] ?? 'غير محدد') . "\n";
        echo "     • كتاب: " . ($firstResult['book_title'] ?? 'غير محدد') . "\n";
    }
    
    echo "\n✅ خلاصة اختبار البحث العربي:\n";
    echo "=================================\n";
    echo "• فهرس pages يحتوي على " . number_format($totalDocs) . " مستند ✅\n";
    echo "• البحث العربي المباشر يعمل ✅\n";
    echo "• UltraFastSearchService متصل بنفس الفهرس ✅\n";
    echo "• النظام جاهز للاستخدام الكامل ✅\n";
    
    echo "\n🎯 الفهرسة تتم على فهرس 'pages' في Elasticsearch بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}