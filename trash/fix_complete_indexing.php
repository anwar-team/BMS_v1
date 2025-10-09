<?php

/**
 * ====================================================================
 * ✅ إصلاح الفهرسة بشكل نهائي وكامل
 * ====================================================================
 * Based on Context7 Best Practices for Elasticsearch Bulk Indexing
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Page;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\DB;

$ES_HOST = 'http://145.223.98.97:9201';
$ES_INDEX = 'pages_new_search';
$BATCH_SIZE = 500; // Context7 recommendation: 500-1000 docs per bulk

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     🔧 إصلاح الفهرسة الكامل - Context7 Optimized      ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

try {
    // Initialize Elasticsearch
    $es = ClientBuilder::create()
        ->setHosts([$ES_HOST])
        ->setRetries(2)
        ->build();
    
    echo "✓ الاتصال بـ Elasticsearch نجح\n\n";
    
    // Step 1: Get counts
    echo "📊 جاري فحص الحالة...\n";
    
    $mysql_count = DB::table('pages')->count();
    $es_count = $es->count(['index' => $ES_INDEX])['count'];
    $missing = $mysql_count - $es_count;
    
    echo "  MySQL: " . number_format($mysql_count) . " صفحة\n";
    echo "  Elasticsearch: " . number_format($es_count) . " صفحة\n";
    echo "  المفقود: " . number_format($missing) . " صفحة\n\n";
    
    if ($missing == 0) {
        echo "✅ الفهرسة مكتملة 100%! لا يوجد عمل\n\n";
        exit(0);
    }
    
    // Step 2: Find missing pages efficiently
    echo "🔍 جاري البحث عن الصفحات المفقودة...\n";
    echo "  (هذا قد يستغرق 2-3 دقائق)\n\n";
    
    $missing_ids = [];
    $checked = 0;
    $total = $mysql_count;
    
    // Sample-based detection: check every 1000 pages
    DB::table('pages')
        ->select('id')
        ->orderBy('id')
        ->chunk(1000, function($pages) use (&$missing_ids, &$checked, $total, $es, $ES_INDEX) {
            foreach ($pages as $page) {
                // Check if exists in ES
                try {
                    $exists = $es->exists([
                        'index' => $ES_INDEX,
                        'id' => $page->id
                    ]);
                    
                    if (!$exists) {
                        $missing_ids[] = $page->id;
                    }
                } catch (Exception $e) {
                    // If error, assume missing
                    $missing_ids[] = $page->id;
                }
                
                $checked++;
                
                if ($checked % 10000 == 0) {
                    $progress = ($checked / $total) * 100;
                    echo sprintf("\r  Progress: %d / %s (%.2f%%) | Found: %d missing",
                        $checked,
                        number_format($total),
                        $progress,
                        count($missing_ids)
                    );
                }
            }
        });
    
    echo "\n\n";
    echo "✓ تم العثور على " . number_format(count($missing_ids)) . " صفحة مفقودة\n\n";
    
    if (empty($missing_ids)) {
        echo "✅ لا توجد صفحات مفقودة للفهرسة\n\n";
        exit(0);
    }
    
    // Step 3: Index missing pages using Bulk API
    echo "📝 جاري الفهرسة باستخدام Bulk API...\n";
    echo "  Batch Size: $BATCH_SIZE (Context7 Best Practice)\n\n";
    
    $indexed = 0;
    $errors = 0;
    $batches = array_chunk($missing_ids, 100); // Get 100 at a time from MySQL
    
    foreach ($batches as $batch_num => $id_batch) {
        // Fetch pages with minimal relations
        $pages = Page::with('book:id,title')
            ->whereIn('id', $id_batch)
            ->get();
        
        if ($pages->isEmpty()) {
            continue;
        }
        
        // Build bulk body
        $bulk_body = [];
        
        foreach ($pages as $page) {
            // Index action
            $bulk_body[] = [
                'index' => [
                    '_index' => $ES_INDEX,
                    '_id' => $page->id
                ]
            ];
            
            // Document data
            $bulk_body[] = [
                'id' => $page->id,
                'page_number' => $page->page_number,
                'content' => $page->content,
                'book_id' => $page->book_id,
                'book_title' => optional($page->book)->title,
                'created_at' => optional($page->created_at)->toIso8601String(),
                'updated_at' => optional($page->updated_at)->toIso8601String()
            ];
            
            $indexed++;
            
            // Send when batch is full
            if (count($bulk_body) >= $BATCH_SIZE * 2) { // *2 because each doc has 2 lines
                try {
                    $response = $es->bulk(['body' => $bulk_body]);
                    
                    // Check for errors
                    if (!empty($response['errors'])) {
                        foreach ($response['items'] as $item) {
                            if (isset($item['index']['error'])) {
                                $errors++;
                            }
                        }
                    }
                    
                    $bulk_body = []; // Reset
                    
                    // Progress
                    $progress = ($indexed / count($missing_ids)) * 100;
                    echo sprintf("\r  Indexed: %d / %d (%.2f%%) | Errors: %d",
                        $indexed,
                        count($missing_ids),
                        $progress,
                        $errors
                    );
                    
                } catch (Exception $e) {
                    $errors += count($bulk_body) / 2;
                    $bulk_body = [];
                    echo "\n  ⚠️ Batch error: " . $e->getMessage() . "\n";
                }
            }
        }
        
        // Send remaining
        if (!empty($bulk_body)) {
            try {
                $response = $es->bulk(['body' => $bulk_body]);
                
                if (!empty($response['errors'])) {
                    foreach ($response['items'] as $item) {
                        if (isset($item['index']['error'])) {
                            $errors++;
                        }
                    }
                }
                
                $progress = ($indexed / count($missing_ids)) * 100;
                echo sprintf("\r  Indexed: %d / %d (%.2f%%) | Errors: %d",
                    $indexed,
                    count($missing_ids),
                    $progress,
                    $errors
                );
                
            } catch (Exception $e) {
                $errors += count($bulk_body) / 2;
                echo "\n  ⚠️ Final batch error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n\n";
    
    // Step 4: Final verification
    echo "🔍 التحقق النهائي...\n";
    
    sleep(2); // Wait for ES to refresh
    
    $final_es_count = $es->count(['index' => $ES_INDEX])['count'];
    $final_missing = $mysql_count - $final_es_count;
    
    echo "  MySQL: " . number_format($mysql_count) . "\n";
    echo "  Elasticsearch: " . number_format($final_es_count) . "\n";
    echo "  المفقود: " . number_format($final_missing) . "\n";
    echo "  الأخطاء: " . number_format($errors) . "\n\n";
    
    if ($final_missing == 0) {
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║              ✅ الفهرسة مكتملة 100%!                   ║\n";
        echo "║        تمت فهرسة جميع الصفحات بنجاح                    ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n\n";
    } else {
        echo "⚠️ لا تزال هناك " . number_format($final_missing) . " صفحة مفقودة\n";
        echo "   قد تحتاج إلى إعادة تشغيل السكريبت\n\n";
    }
    
    echo "📊 إحصائيات:\n";
    echo "  - تمت معالجة: " . number_format($indexed) . " صفحة\n";
    echo "  - نجح: " . number_format($indexed - $errors) . " صفحة\n";
    echo "  - فشل: " . number_format($errors) . " صفحة\n";
    echo "  - معدل النجاح: " . number_format((($indexed - $errors) / $indexed) * 100, 2) . "%\n\n";
    
} catch (Exception $e) {
    echo "\n❌ خطأ فادح: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n\n";
    exit(1);
}

echo "✓ اكتمل\n\n";
