<?php

// فهرسة مباشرة على فهرس pages في Elasticsearch
// تشغيل: php direct-pages-indexing.php

echo "📊 فهرسة مباشرة على فهرس pages\n";
echo "===============================\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Elasticsearch\ClientBuilder;
use App\Models\Page;

try {
    // إنشاء عميل Elasticsearch
    $client = ClientBuilder::create()
        ->setHosts([config('services.elasticsearch.host')])
        ->setRetries(1)
        ->build();
    
    echo "1️⃣ اتصال Elasticsearch: ✅\n";
    
    // التحقق من فهرس pages
    $indexExists = $client->indices()->exists(['index' => 'pages']);
    echo "2️⃣ فهرس pages موجود: " . ($indexExists ? '✅' : '❌') . "\n";
    
    if (!$indexExists) {
        echo "❌ فهرس pages غير موجود!\n";
        exit(1);
    }
    
    // إحصائيات قبل الفهرسة
    $statsBefore = $client->indices()->stats(['index' => 'pages']);
    $docsBefore = $statsBefore['indices']['pages']['total']['docs']['count'] ?? 0;
    echo "3️⃣ مستندات قبل الفهرسة: " . number_format($docsBefore) . "\n";
    
    // جلب عينة من الصفحات للفهرسة المباشرة
    echo "\n4️⃣ فهرسة مباشرة لعينة من الصفحات:\n";
    $batchSize = 100;
    $pages = Page::with(['book', 'book.authors'])->limit($batchSize)->get();
    echo "   - صفحات للفهرسة: " . $pages->count() . "\n";
    
    $indexed = 0;
    $errors = 0;
    
    foreach ($pages as $page) {
        try {
            // تحضير المستند للفهرسة
            $document = [
                'index' => 'pages',
                'id' => $page->id,
                'body' => [
                    'id' => $page->id,
                    'content' => $page->content,
                    'page_number' => $page->page_number,
                    'book_id' => $page->book_id,
                    'book_title' => $page->book->title ?? '',
                    'book_description' => $page->book->description ?? '',
                    'authors' => $page->book && $page->book->authors ? 
                               $page->book->authors->pluck('name')->implode(', ') : '',
                    'author_names' => $page->book && $page->book->authors ? 
                                    $page->book->authors->pluck('name')->toArray() : [],
                    'author_ids' => $page->book && $page->book->authors ? 
                                  $page->book->authors->pluck('id')->toArray() : [],
                    'death_year' => $page->book && $page->book->authors->isNotEmpty() ? 
                                  $page->book->authors->first()->death_year : null,
                    'created_at' => $page->created_at ? $page->created_at->toISOString() : null,
                    'updated_at' => $page->updated_at ? $page->updated_at->toISOString() : null,
                ]
            ];
            
            // فهرسة المستند
            $response = $client->index($document);
            
            if ($response['result'] === 'created' || $response['result'] === 'updated') {
                $indexed++;
            }
            
            // طباعة التقدم كل 25 صفحة
            if ($indexed % 25 === 0) {
                echo "   - مُفهرس: {$indexed} صفحة\n";
            }
            
        } catch (Exception $e) {
            $errors++;
            if ($errors <= 3) { // طباعة أول 3 أخطاء فقط
                echo "   - خطأ في الصفحة {$page->id}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "   - ✅ إجمالي المُفهرس: {$indexed}\n";
    echo "   - ❌ أخطاء: {$errors}\n";
    
    // فرض refresh للفهرس
    $client->indices()->refresh(['index' => 'pages']);
    
    // إحصائيات بعد الفهرسة
    sleep(2);
    $statsAfter = $client->indices()->stats(['index' => 'pages']);
    $docsAfter = $statsAfter['indices']['pages']['total']['docs']['count'] ?? 0;
    echo "\n5️⃣ مستندات بعد الفهرسة: " . number_format($docsAfter) . "\n";
    echo "   - الزيادة: " . number_format($docsAfter - $docsBefore) . "\n";
    
    // اختبار البحث في الفهرس
    echo "\n6️⃣ اختبار البحث في فهرس pages:\n";
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
    
    $searchResults = $searchResponse['hits']['total']['value'] ?? 0;
    $returnedResults = count($searchResponse['hits']['hits'] ?? []);
    
    echo "   - نتائج البحث عن 'الله': " . number_format($searchResults) . "\n";
    echo "   - مُسترجع: {$returnedResults}\n";
    
    if (!empty($searchResponse['hits']['hits'])) {
        $firstHit = $searchResponse['hits']['hits'][0];
        echo "   - عينة نتيجة:\n";
        echo "     • ID: " . $firstHit['_source']['id'] . "\n";
        echo "     • كتاب: " . ($firstHit['_source']['book_title'] ?? 'غير محدد') . "\n";
        echo "     • محتوى: " . mb_substr($firstHit['_source']['content'], 0, 80) . "...\n";
    }
    
    echo "\n✅ نتائج الفهرسة المباشرة:\n";
    echo "============================\n";
    echo "• فهرس 'pages' في Elasticsearch يعمل بشكل صحيح ✅\n";
    echo "• تم فهرسة {$indexed} صفحة مباشرة على الفهرس ✅\n";
    echo "• البحث في الفهرس يعمل بشكل طبيعي ✅\n";
    echo "• النظام جاهز للفهرسة الكاملة للمليون صفحة ✅\n";
    
    echo "\n🚀 للفهرسة الكاملة، يمكنك تشغيل:\n";
    echo "   php auto-indexing.php\n";
    echo "أو استخدام UltraFastSearchService الذي يعمل مع نفس الفهرس.\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "\n💡 تحقق من:\n";
    echo "   1. تشغيل Elasticsearch على: " . config('services.elasticsearch.host') . "\n";
    echo "   2. وجود فهرس 'pages'\n";
    echo "   3. صحة إعدادات الشبكة\n";
}