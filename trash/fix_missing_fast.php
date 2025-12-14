<?php

/**
 * ====================================================================
 * ⚡ حل سريع جداً: فهرسة الصفحات المفقودة
 * ====================================================================
 * 
 * الاستراتيجية (حسب Context7 Best Practices):
 * 1. استخدام SQL للحصول على IDs المفقودة مباشرة
 * 2. Bulk indexing بدفعات 500 (أفضل حجم حسب Elasticsearch docs)
 * 3. Eager loading للعلاقات لتجنب N+1 queries
 * 
 * ====================================================================
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Page;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\DB;

// ===================================================================
// الإعدادات
// ===================================================================

$ES_HOST = env('ELASTICSEARCH_HOST', 'http://145.223.98.97:9201');
$ES_INDEX = env('ELASTICSEARCH_INDEX', 'pages_new_search');
$BATCH_SIZE = 500; // Context7 recommendation: 500-1000

echo "\n";
echo "════════════════════════════════════════════════════════════\n";
echo "         ⚡ فهرسة سريعة للصفحات المفقودة\n";
echo "════════════════════════════════════════════════════════════\n";
echo "Strategy: SQL-based missing IDs detection + Bulk indexing\n";
echo "Batch Size: $BATCH_SIZE (Elasticsearch best practice)\n";
echo "════════════════════════════════════════════════════════════\n\n";

// ===================================================================
// الاتصال بـ Elasticsearch
// ===================================================================

$elasticsearch = ClientBuilder::create()
    ->setHosts([$ES_HOST])
    ->setRetries(2)
    ->setSSLVerification(false)
    ->build();

echo "✅ متصل بـ Elasticsearch\n\n";

// ===================================================================
// الطريقة السريعة: استخدام SQL للحصول على العدد الدقيق
// ===================================================================

echo "📊 فحص العدد الإجمالي...\n";

$mysql_count = DB::table('pages')->count();
$es_count_response = $elasticsearch->count(['index' => $ES_INDEX]);
$es_count = $es_count_response['count'];

echo "   MySQL: " . number_format($mysql_count) . "\n";
echo "   Elasticsearch: " . number_format($es_count) . "\n";
echo "   Missing: " . number_format($mysql_count - $es_count) . "\n\n";

$missing_count = $mysql_count - $es_count;

if ($missing_count <= 0) {
    echo "🎉 لا توجد صفحات مفقودة!\n\n";
    exit(0);
}

if ($missing_count > 50000) {
    echo "⚠️  عدد كبير جداً من الصفحات المفقودة ($missing_count)\n";
    echo "   يُنصح بإعادة الفهرسة الكاملة بدلاً من الإصلاح\n";
    echo "   هل تريد المتابعة؟ (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) != 'y') {
        echo "تم الإلغاء.\n";
        exit(0);
    }
}

// ===================================================================
// استراتيجية ذكية: فحص عينات للعثور على الفجوات
// ===================================================================

echo "🔍 البحث عن الصفحات المفقودة...\n";
echo "   استخدام استراتيجية الفحص بالعينات...\n\n";

$missing_ids = [];
$sample_size = 10000;
$offset = 0;

while (count($missing_ids) < $missing_count && $offset < $mysql_count) {
    // جلب عينة من IDs
    $page_ids = DB::table('pages')
        ->select('id')
        ->offset($offset)
        ->limit($sample_size)
        ->pluck('id')
        ->toArray();
    
    if (empty($page_ids)) {
        break;
    }
    
    // فحص هذه IDs في Elasticsearch (Bulk Exists API)
    $body = [];
    foreach ($page_ids as $id) {
        $body['ids'][] = (string)$id;
    }
    
    try {
        $response = $elasticsearch->mget([
            'index' => $ES_INDEX,
            'body' => $body,
            '_source' => false
        ]);
        
        foreach ($response['docs'] as $i => $doc) {
            if (!$doc['found']) {
                $missing_ids[] = $page_ids[$i];
            }
        }
        
    } catch (Exception $e) {
        // إذا فشل mget، نستخدم exists لكل ID
        foreach ($page_ids as $id) {
            try {
                if (!$elasticsearch->exists(['index' => $ES_INDEX, 'id' => $id])) {
                    $missing_ids[] = $id;
                }
            } catch (Exception $e2) {
                $missing_ids[] = $id;
            }
        }
    }
    
    $offset += $sample_size;
    $progress = min(100, ($offset / $mysql_count) * 100);
    
    echo "   تقدم: " . number_format($progress, 1) . "% | وُجد: " . count($missing_ids) . " مفقودة\r";
    
    // إذا وصلنا للعدد المتوقع، نتوقف
    if (count($missing_ids) >= $missing_count) {
        break;
    }
}

echo "\n   ✅ تم العثور على " . count($missing_ids) . " صفحة مفقودة\n\n";

if (empty($missing_ids)) {
    echo "🎉 لا توجد صفحات مفقودة (قد يكون هناك تحديث حديث)!\n\n";
    exit(0);
}

// عرض عينة من IDs المفقودة
echo "   عينة من IDs المفقودة:\n";
foreach (array_slice($missing_ids, 0, 10) as $id) {
    echo "      - $id\n";
}
if (count($missing_ids) > 10) {
    echo "      ... و " . (count($missing_ids) - 10) . " أخرى\n";
}
echo "\n";

// ===================================================================
// الفهرسة السريعة (Bulk Indexing - Context7 Best Practice)
// ===================================================================

echo "🚀 فهرسة الصفحات المفقودة...\n";
echo "   Batch Size: $BATCH_SIZE (Elasticsearch recommendation)\n";
echo "   Method: Bulk API with error handling\n\n";

$indexed_count = 0;
$error_count = 0;
$batch = [];

foreach (array_chunk($missing_ids, 100) as $chunk_ids) {
    // Eager load relationships (Context7 Laravel Best Practice)
    // استخدام authors بدلاً من mainAuthor (العلاقة الصحيحة)
    $pages = Page::with(['book' => function($query) {
            $query->with(['authors' => function($q) {
                $q->wherePivot('is_main', true);
            }, 'bookSection']);
        }])
        ->whereIn('id', $chunk_ids)
        ->get();
    
    foreach ($pages as $page) {
        $batch[] = [
            'index' => [
                '_index' => $ES_INDEX,
                '_id' => $page->id
            ]
        ];
        
        // جلب المؤلف الرئيسي
        $mainAuthor = $page->book && $page->book->authors ? 
            $page->book->authors->firstWhere('pivot.is_main', true) : null;
        
        $batch[] = [
            'id' => $page->id,
            'page_number' => $page->page_number,
            'content' => [
                'exact' => $page->content,
                'flexible' => $page->content,
                'stemmed' => $page->content
            ],
            'book_id' => $page->book_id,
            'book_title' => $page->book->title ?? null,
            'author_names' => $mainAuthor ? $mainAuthor->full_name : null,
            'book_section_id' => $page->book->book_section_id ?? null,
            'created_at' => $page->created_at ? $page->created_at->toIso8601String() : null,
            'updated_at' => $page->updated_at ? $page->updated_at->toIso8601String() : null
        ];
        
        // Bulk index when batch is full
        if (count($batch) >= ($BATCH_SIZE * 2)) {
            try {
                $response = $elasticsearch->bulk(['body' => $batch]);
                
                // Error handling (Context7 Best Practice)
                if ($response['errors']) {
                    foreach ($response['items'] as $item) {
                        if (isset($item['index']['error'])) {
                            $error_count++;
                            if ($error_count <= 5) {
                                echo "   ⚠️  Error: " . ($item['index']['error']['reason'] ?? 'Unknown') . "\n";
                            }
                        }
                    }
                }
                
                $indexed_count += ($BATCH_SIZE);
                $percent = ($indexed_count / count($missing_ids)) * 100;
                echo "   فهرس: " . number_format(min($indexed_count, count($missing_ids))) . " / " . number_format(count($missing_ids)) . " (" . number_format($percent, 1) . "%)\r";
                
            } catch (Exception $e) {
                echo "\n   ❌ خطأ في الفهرسة: " . $e->getMessage() . "\n";
                $error_count += $BATCH_SIZE;
            }
            
            $batch = [];
        }
    }
}

// Bulk index remaining
if (count($batch) > 0) {
    try {
        $response = $elasticsearch->bulk(['body' => $batch]);
        
        if ($response['errors']) {
            foreach ($response['items'] as $item) {
                if (isset($item['index']['error'])) {
                    $error_count++;
                }
            }
        }
        
        $indexed_count += (count($batch) / 2);
        
    } catch (Exception $e) {
        echo "\n   ❌ خطأ في الفهرسة: " . $e->getMessage() . "\n";
    }
}

echo "\n   ✅ تم فهرسة " . number_format(min($indexed_count, count($missing_ids))) . " صفحة\n";

if ($error_count > 0) {
    echo "   ⚠️  حدثت $error_count أخطاء أثناء الفهرسة\n";
}

echo "\n";

// ===================================================================
// التحقق النهائي
// ===================================================================

echo "🔍 التحقق النهائي...\n";

sleep(2); // Wait for ES to refresh

$final_count_response = $elasticsearch->count(['index' => $ES_INDEX]);
$final_count = $final_count_response['count'];

echo "   MySQL: " . number_format($mysql_count) . "\n";
echo "   Elasticsearch: " . number_format($final_count) . "\n";
echo "   Difference: " . number_format(abs($mysql_count - $final_count)) . "\n\n";

if ($final_count >= $mysql_count) {
    echo "🎉 تم! جميع الصفحات مفهرسة بنجاح!\n\n";
} else {
    $still_missing = $mysql_count - $final_count;
    echo "⚠️  لا تزال هناك " . number_format($still_missing) . " صفحة مفقودة.\n";
    echo "   قم بإعادة تشغيل السكريبت مرة أخرى.\n\n";
}

echo "════════════════════════════════════════════════════════════\n";
echo "                     انتهى السكريبت\n";
echo "════════════════════════════════════════════════════════════\n\n";
