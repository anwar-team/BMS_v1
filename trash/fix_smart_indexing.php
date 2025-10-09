<?php

/**
 * ====================================================================
 * 🎯 إصلاح نهائي للفهرسة - البحث الذكي عن الصفحات المفقودة
 * ====================================================================
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

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║       🎯 البحث الذكي عن الصفحات المفقودة               ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

try {
    $es = ClientBuilder::create()
        ->setHosts([$ES_HOST])
        ->setRetries(2)
        ->build();
    
    // الحالة الحالية
    $mysql_count = DB::table('pages')->count();
    $es_count = $es->count(['index' => $ES_INDEX])['count'];
    
    echo "📊 الوضع الحالي:\n";
    echo "  MySQL: " . number_format($mysql_count) . "\n";
    echo "  Elasticsearch: " . number_format($es_count) . "\n";
    echo "  المفقود: " . number_format($mysql_count - $es_count) . "\n\n";
    
    // استراتيجية ذكية: فحص نطاقات معينة
    echo "🔍 استراتيجية البحث الذكي:\n";
    echo "   1. فحص النطاقات ذات الفجوات الكبيرة\n";
    echo "   2. استخدام التقسيم للبحث السريع\n\n";
    
    // خذ نطاقات الـ IDs
    $id_ranges = DB::select("
        SELECT 
            FLOOR(id / 100000) as range_group,
            MIN(id) as min_id,
            MAX(id) as max_id,
            COUNT(*) as total
        FROM pages
        GROUP BY FLOOR(id / 100000)
        ORDER BY range_group
    ");
    
    echo "📈 نطاقات البيانات:\n\n";
    
    $missing_ids = [];
    
    foreach ($id_ranges as $range) {
        $range_name = ($range->range_group * 100000) . " - " . (($range->range_group + 1) * 100000);
        echo "  Range $range_name: " . number_format($range->total) . " صفحات | ";
        
        // فحص عينة من كل نطاق
        $sample_ids = DB::table('pages')
            ->whereBetween('id', [$range->min_id, $range->max_id])
            ->orderBy('id')
            ->limit(100)
            ->pluck('id')
            ->toArray();
        
        $missing_in_range = 0;
        foreach ($sample_ids as $id) {
            try {
                if (!$es->exists(['index' => $ES_INDEX, 'id' => $id])) {
                    $missing_ids[] = $id;
                    $missing_in_range++;
                }
            } catch (Exception $e) {
                $missing_ids[] = $id;
                $missing_in_range++;
            }
        }
        
        if ($missing_in_range > 0) {
            echo "⚠️ $missing_in_range مفقود من 100 عينة\n";
            
            // إذا كان هناك مفقودات، افحص النطاق كاملاً
            echo "    → جاري فحص النطاق كاملاً...\n";
            
            $all_ids_in_range = DB::table('pages')
                ->whereBetween('id', [$range->min_id, $range->max_id])
                ->pluck('id')
                ->toArray();
            
            foreach (array_chunk($all_ids_in_range, 500) as $chunk) {
                foreach ($chunk as $id) {
                    try {
                        if (!$es->exists(['index' => $ES_INDEX, 'id' => $id])) {
                            if (!in_array($id, $missing_ids)) {
                                $missing_ids[] = $id;
                            }
                        }
                    } catch (Exception $e) {
                        if (!in_array($id, $missing_ids)) {
                            $missing_ids[] = $id;
                        }
                    }
                }
            }
            
            echo "    → إجمالي المفقود في هذا النطاق: " . count(array_filter($missing_ids, function($id) use ($range) {
                return $id >= $range->min_id && $id <= $range->max_id;
            })) . "\n";
        } else {
            echo "✅ نطاق كامل\n";
        }
    }
    
    echo "\n";
    echo "✓ تم العثور على " . number_format(count($missing_ids)) . " صفحة مفقودة\n\n";
    
    if (empty($missing_ids)) {
        echo "✅ لا توجد صفحات مفقودة!\n\n";
        exit(0);
    }
    
    // فهرسة الصفحات المفقودة
    echo "📝 جاري الفهرسة...\n\n";
    
    $indexed = 0;
    $errors = 0;
    
    foreach (array_chunk($missing_ids, 100) as $batch_ids) {
        $pages = Page::with('book:id,title')
            ->whereIn('id', $batch_ids)
            ->get();
        
        if ($pages->isEmpty()) continue;
        
        $bulk_body = [];
        
        foreach ($pages as $page) {
            $bulk_body[] = [
                'index' => [
                    '_index' => $ES_INDEX,
                    '_id' => $page->id
                ]
            ];
            
            $bulk_body[] = [
                'id' => $page->id,
                'page_number' => $page->page_number,
                'content' => $page->content,
                'book_id' => $page->book_id,
                'book_title' => optional($page->book)->title,
                'created_at' => optional($page->created_at)->toIso8601String(),
                'updated_at' => optional($page->updated_at)->toIso8601String()
            ];
        }
        
        try {
            $response = $es->bulk(['body' => $bulk_body]);
            
            if (!empty($response['errors'])) {
                foreach ($response['items'] as $item) {
                    if (isset($item['index']['error'])) {
                        $errors++;
                    }
                }
            }
            
            $indexed += count($pages);
            
            echo sprintf("\r  Progress: %d / %d | Errors: %d",
                $indexed,
                count($missing_ids),
                $errors
            );
            
        } catch (Exception $e) {
            $errors += count($pages);
        }
    }
    
    echo "\n\n";
    
    // التحقق النهائي
    sleep(2);
    
    $final_count = $es->count(['index' => $ES_INDEX])['count'];
    $final_missing = $mysql_count - $final_count;
    
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║                    النتيجة النهائية                     ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";
    
    echo "  MySQL: " . number_format($mysql_count) . "\n";
    echo "  Elasticsearch: " . number_format($final_count) . "\n";
    echo "  المفقود: " . number_format($final_missing) . "\n";
    echo "  النسبة: " . number_format(($final_count / $mysql_count) * 100, 4) . "%\n\n";
    
    if ($final_missing == 0) {
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║              ✅ الفهرسة مكتملة 100%!                   ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n\n";
    } else {
        echo "⚠️  لا يزال " . number_format($final_missing) . " صفحة مفقودة\n\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ خطأ: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "✓ اكتمل\n\n";
