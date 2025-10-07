<?php

/**
 * ====================================================================
 * 🔧 سكريبت فهرسة الصفحات المفقودة
 * ====================================================================
 * 
 * الهدف: فهرسة 8,199 صفحة مفقودة من Elasticsearch
 * الطريقة: مقارنة IDs بين MySQL و Elasticsearch
 * الأداء: Bulk indexing (500 صفحة في كل batch)
 * 
 * الاستخدام:
 *   php fix_missing_pages.php
 * 
 * ====================================================================
 */

require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Page;
use Elasticsearch\ClientBuilder;

// ===================================================================
// الإعدادات
// ===================================================================

$ES_HOST = env('ELASTICSEARCH_HOST', 'http://145.223.98.97:9201');
$ES_INDEX = env('ELASTICSEARCH_INDEX', 'pages_new_search');
$BATCH_SIZE = 500; // عدد الصفحات في كل batch
$SCROLL_SIZE = 10000; // عدد IDs في كل scroll

echo "\n";
echo "════════════════════════════════════════════════════════════\n";
echo "         🔧 فهرسة الصفحات المفقودة\n";
echo "════════════════════════════════════════════════════════════\n";
echo "Elasticsearch: $ES_HOST\n";
echo "Index: $ES_INDEX\n";
echo "Batch Size: $BATCH_SIZE\n";
echo "════════════════════════════════════════════════════════════\n\n";

// ===================================================================
// الاتصال بـ Elasticsearch
// ===================================================================

try {
    $elasticsearch = ClientBuilder::create()
        ->setHosts([$ES_HOST])
        ->setRetries(2)
        ->setSSLVerification(false)
        ->build();
    
    // Test connection
    $health = $elasticsearch->cluster()->health();
    echo "✅ الاتصال بـ Elasticsearch ناجح\n";
    echo "   Status: {$health['status']}\n";
    echo "   Cluster: {$health['cluster_name']}\n\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الاتصال بـ Elasticsearch:\n";
    echo "   {$e->getMessage()}\n";
    exit(1);
}

// ===================================================================
// الخطوة 1: استخدام طريقة أسرع - التحقق بالعينات
// ===================================================================

echo "📥 الخطوة 1/3: فحص الصفحات المفقودة (طريقة محسنة)...\n";

// بدلاً من جلب كل IDs، سنستخدم استعلام MySQL مباشر
// ونفحص كل صفحة في Elasticsearch (أسرع لـ 8K صفحة فقط)

echo "   💡 استخدام طريقة التحقق المباشر (أسرع للعدد القليل)...\n";

$es_ids = []; // سنملأها لاحقاً إذا لزم الأمر

// تخطي هذه الخطوة للسرعة

// ===================================================================
// الخطوة 2: البحث السريع عن الصفحات المفقودة (مُحسّن)
// ===================================================================

echo "🔍 الخطوة 2/3: البحث عن الصفحات المفقودة (طريقة محسنة)...\n";

$missing_count = 0;
$total_pages = 0;
$missing_ids = [];

try {
    // استخدام lazy() بدلاً من chunk() للذاكرة الأقل (Context7 Best Practice)
    echo "   استخدام LazyCollection للكفاءة العالية...\n";
    
    $checked = 0;
    foreach (Page::lazy(1000) as $page) {
        $total_pages++;
        $checked++;
        
        // فحص وجود الصفحة في ES
        try {
            $exists = $elasticsearch->exists([
                'index' => $ES_INDEX,
                'id' => $page->id
            ]);
            
            if (!$exists) {
                $missing_count++;
                $missing_ids[] = $page->id;
                
                if ($missing_count <= 20) {
                    echo "   📝 صفحة مفقودة: ID={$page->id}, Book={$page->book_id}\n";
                }
            }
        } catch (Exception $e) {
            // إذا حدث خطأ، نعتبرها مفقودة
            $missing_count++;
            $missing_ids[] = $page->id;
        }
        
        if ($checked % 1000 == 0) {
            echo "   فحص: " . number_format($checked) . " | مفقود: " . number_format($missing_count) . "...\r";
        }
        
        // توقف مبكراً إذا وصلنا للحد المتوقع
        if ($missing_count >= 10000) {
            echo "\n   ⚠️  تم إيجاد أكثر من 10,000 صفحة مفقودة - سيتم معالجتها لاحقاً\n";
            break;
        }
    }
    
    echo "   ✅ تم فحص " . number_format($total_pages) . " صفحة                           \n";
    echo "   📊 الصفحات المفقودة: " . number_format($missing_count) . "\n\n";
    
    if ($missing_count == 0) {
        echo "🎉 رائع! لا توجد صفحات مفقودة!\n\n";
        exit(0);
    }
    
} catch (Exception $e) {
    echo "\n   ❌ خطأ في البحث عن الصفحات المفقودة:\n";
    echo "   {$e->getMessage()}\n";
    exit(1);
}

// ===================================================================
// الخطوة 3: فهرسة الصفحات المفقودة
// ===================================================================

echo "🚀 الخطوة 3/3: فهرسة الصفحات المفقودة...\n";

$indexed_count = 0;
$batch = [];
$errors = [];

try {
    foreach (array_chunk($missing_ids, 100) as $chunk_ids) {
        // Fetch pages with relations
        $pages = Page::with(['book.mainAuthor', 'book.section'])
            ->whereIn('id', $chunk_ids)
            ->get();
        
        foreach ($pages as $page) {
            // Add to bulk batch
            $batch[] = [
                'index' => [
                    '_index' => $ES_INDEX,
                    '_id' => $page->id
                ]
            ];
            
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
                'author_names' => $page->book->mainAuthor->full_name ?? null,
                'book_section_id' => $page->book->book_section_id ?? null,
                'created_at' => $page->created_at ? $page->created_at->toIso8601String() : null,
                'updated_at' => $page->updated_at ? $page->updated_at->toIso8601String() : null
            ];
            
            // Bulk index every BATCH_SIZE documents
            if (count($batch) >= ($BATCH_SIZE * 2)) {
                $bulk_response = $elasticsearch->bulk(['body' => $batch]);
                
                // Check for errors
                if ($bulk_response['errors']) {
                    foreach ($bulk_response['items'] as $item) {
                        if (isset($item['index']['error'])) {
                            $errors[] = $item['index']['error'];
                        }
                    }
                }
                
                $indexed_count += ($BATCH_SIZE);
                echo "   فهرس: " . number_format($indexed_count) . " / " . number_format($missing_count) . " صفحة...\r";
                
                $batch = [];
            }
        }
    }
    
    // Index remaining
    if (count($batch) > 0) {
        $bulk_response = $elasticsearch->bulk(['body' => $batch]);
        
        if ($bulk_response['errors']) {
            foreach ($bulk_response['items'] as $item) {
                if (isset($item['index']['error'])) {
                    $errors[] = $item['index']['error'];
                }
            }
        }
        
        $indexed_count += (count($batch) / 2);
    }
    
    echo "   ✅ تم فهرسة " . number_format($indexed_count) . " صفحة                    \n\n";
    
    if (count($errors) > 0) {
        echo "⚠️  حدثت بعض الأخطاء:\n";
        foreach (array_slice($errors, 0, 5) as $error) {
            echo "   - " . ($error['reason'] ?? json_encode($error)) . "\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "\n   ❌ خطأ في الفهرسة:\n";
    echo "   {$e->getMessage()}\n";
    echo "   Trace: {$e->getTraceAsString()}\n";
    exit(1);
}

// ===================================================================
// التحقق النهائي
// ===================================================================

echo "🔍 التحقق النهائي...\n";

try {
    sleep(2); // Wait for ES to refresh
    
    $count_response = $elasticsearch->count(['index' => $ES_INDEX]);
    $final_count = $count_response['count'];
    
    echo "   📊 إجمالي الصفحات في Elasticsearch: " . number_format($final_count) . "\n";
    echo "   📊 إجمالي الصفحات في MySQL: " . number_format($total_pages) . "\n";
    
    if ($final_count >= $total_pages) {
        echo "\n🎉 تم! جميع الصفحات مفهرسة بنجاح!\n\n";
    } else {
        $still_missing = $total_pages - $final_count;
        echo "\n⚠️  لا تزال هناك " . number_format($still_missing) . " صفحة مفقودة.\n";
        echo "   قم بإعادة تشغيل السكريبت مرة أخرى.\n\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ خطأ في التحقق النهائي:\n";
    echo "   {$e->getMessage()}\n";
}

echo "════════════════════════════════════════════════════════════\n";
echo "                     انتهى السكريبت\n";
echo "════════════════════════════════════════════════════════════\n\n";
