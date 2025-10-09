<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Page;
use App\Models\Book;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     🚀 فهرسة سريعة للصفحات مع حقول الفلاتر              ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$elasticsearchHost = env('ELASTICSEARCH_HOST', 'http://145.223.98.97:9201');
$indexName = 'pages_new_search';

// إعدادات
$batchSize = 5000;  // حجم الدفعة
$startFrom = 0;      // ابدأ من ID

echo "⚙️ الإعدادات:\n";
echo "   - Elasticsearch: {$elasticsearchHost}\n";
echo "   - Index: {$indexName}\n";
echo "   - Batch Size: " . number_format($batchSize) . "\n";
echo "   - Start From ID: {$startFrom}\n\n";

// احصل على العدد الكلي
$totalPages = Page::count();
echo "📊 إجمالي الصفحات: " . number_format($totalPages) . "\n\n";

// احصل على آخر ID تم فهرسته
$lastIndexedId = $startFrom;

// التحقق من Index
$url = "{$elasticsearchHost}/{$indexName}/_count";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $result = json_decode($response, true);
    $currentCount = $result['count'] ?? 0;
    echo "📈 العدد الحالي في Index: " . number_format($currentCount) . "\n";
    
    // احصل على آخر ID
    if ($currentCount > 0) {
        $url = "{$elasticsearchHost}/{$indexName}/_search?size=1&sort=id:desc";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        
        if (isset($result['hits']['hits'][0]['_source']['id'])) {
            $lastIndexedId = $result['hits']['hits'][0]['_source']['id'];
            echo "📌 آخر ID مفهرس: " . number_format($lastIndexedId) . "\n";
            echo "⏭️ سيبدأ من ID: " . number_format($lastIndexedId + 1) . "\n";
        }
    }
} else {
    echo "⚠️ Index غير موجود أو Elasticsearch غير متاح\n";
    echo "❓ هل تريد المتابعة؟ (سيتم إنشاء index جديد)\n";
    echo "   اضغط Enter للمتابعة أو Ctrl+C للإلغاء...\n";
    fgets(STDIN);
}

echo "\n";
echo "════════════════════════════════════════════════════════════\n";
echo "🚀 بدء الفهرسة...\n";
echo "════════════════════════════════════════════════════════════\n\n";

$startTime = microtime(true);
$totalIndexed = 0;
$totalErrors = 0;
$batchNumber = 0;

while (true) {
    $batchNumber++;
    $batchStartTime = microtime(true);
    
    // جلب الدفعة التالية
    $pages = Page::where('id', '>', $lastIndexedId)
        ->with(['book.authors', 'book.bookSection'])
        ->orderBy('id')
        ->limit($batchSize)
        ->get();
    
    if ($pages->isEmpty()) {
        break;
    }
    
    // بناء bulk request
    $bulkData = '';
    foreach ($pages as $page) {
        // معلومات الكتاب
        $book = $page->book;
        if (!$book) continue;
        
        // جمع IDs المؤلفين
        $authorIds = [];
        $authorNames = [];
        if ($book->authors && $book->authors->isNotEmpty()) {
            foreach ($book->authors as $author) {
                if ($author->id) {
                    $authorIds[] = (string)$author->id;
                }
                if ($author->full_name) {
                    $authorNames[] = $author->full_name;
                }
            }
        }
        
        // معلومات القسم
        $sectionId = $book->book_section_id ? (string)$book->book_section_id : null;
        $sectionName = $book->bookSection ? $book->bookSection->name : null;
        
        // بناء الوثيقة
        $doc = [
            'id' => $page->id,
            'page_number' => $page->page_number ?? 1,
            'content' => $page->content ?? '',
            'book_id' => $book->id,
            'book_title' => $book->title ?? '',
            'book_author' => $authorNames[0] ?? '',
            'author_names' => implode(' | ', $authorNames),
            'author_ids' => $authorIds, // Array of author IDs
            'book_section' => $sectionName,
            'book_section_id' => $sectionId,
            'content_length' => strlen($page->content ?? ''),
            'language' => 'arabic',
            'created_date' => $page->created_at ? $page->created_at->toIso8601String() : null,
            'last_modified' => $page->updated_at ? $page->updated_at->toIso8601String() : null,
            'document_id' => 'page_' . $page->id,
        ];
        
        // Bulk action
        $action = json_encode(['index' => ['_index' => $indexName, '_id' => $page->id]]);
        $document = json_encode($doc, JSON_UNESCAPED_UNICODE);
        
        $bulkData .= $action . "\n" . $document . "\n";
        
        $lastIndexedId = $page->id;
    }
    
    // إرسال bulk request
    if (!empty($bulkData)) {
        $url = "{$elasticsearchHost}/_bulk";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bulkData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-ndjson',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $result = json_decode($response, true);
            $errors = $result['errors'] ?? false;
            
            if ($errors) {
                $totalErrors++;
                echo "⚠️ Batch {$batchNumber}: بعض الأخطاء\n";
            }
            
            $batchCount = $pages->count();
            $totalIndexed += $batchCount;
            
            $batchTime = round((microtime(true) - $batchStartTime) * 1000);
            $elapsedTime = microtime(true) - $startTime;
            $rate = $totalIndexed / $elapsedTime;
            $remaining = $totalPages - ($lastIndexedId);
            $eta = $remaining / $rate;
            
            $progress = ($lastIndexedId / $totalPages) * 100;
            
            echo sprintf(
                "✅ Batch %d: %s صفحات | الإجمالي: %s | التقدم: %.2f%% | السرعة: %d/sec | المتبقي: %s | الوقت المتبقي: %s\n",
                $batchNumber,
                number_format($batchCount),
                number_format($totalIndexed),
                $progress,
                round($rate),
                number_format($remaining),
                gmdate('H:i:s', $eta)
            );
            
        } else {
            $totalErrors++;
            echo "❌ Batch {$batchNumber}: فشل (HTTP {$httpCode})\n";
        }
    }
    
    // راحة صغيرة لتجنب الضغط على الخادم
    usleep(100000); // 100ms
}

$totalTime = microtime(true) - $startTime;
$avgRate = $totalIndexed / $totalTime;

echo "\n";
echo "════════════════════════════════════════════════════════════\n";
echo "🎉 اكتملت الفهرسة!\n";
echo "════════════════════════════════════════════════════════════\n\n";

echo "📊 الإحصائيات النهائية:\n";
echo "   ✅ تم فهرسة: " . number_format($totalIndexed) . " صفحة\n";
echo "   ⚠️ أخطاء: {$totalErrors} دفعة\n";
echo "   ⏱️ الوقت الكلي: " . gmdate('H:i:s', $totalTime) . "\n";
echo "   🚀 المعدل: " . number_format($avgRate) . " صفحة/ثانية\n";
echo "   📈 آخر ID: " . number_format($lastIndexedId) . "\n";

// التحقق النهائي
echo "\n🔍 التحقق من Index...\n";
$url = "{$elasticsearchHost}/{$indexName}/_count";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

$finalCount = $result['count'] ?? 0;
echo "   📊 العدد في Elasticsearch: " . number_format($finalCount) . "\n";

if ($finalCount >= $totalIndexed) {
    echo "   ✅ النجاح! جميع الصفحات تم فهرستها\n";
} else {
    echo "   ⚠️ تحذير: عدم تطابق الأرقام\n";
}

echo "\n";
echo "════════════════════════════════════════════════════════════\n";
echo "✨ تم بنجاح! نظام الفلاتر جاهز الآن\n";
echo "════════════════════════════════════════════════════════════\n";
