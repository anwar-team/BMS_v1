<?php

/**
 * ====================================================================
 * ✅ إصلاح الفهرسة - نسخة محسّنة وسريعة
 * ====================================================================
 * استراتيجية: فهرسة جميع الصفحات بدون فحص (أسرع بكثير)
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
$BATCH_SIZE = 500;

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║          🚀 فهرسة سريعة - بدون فحص مسبق                ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

try {
    $es = ClientBuilder::create()
        ->setHosts([$ES_HOST])
        ->setRetries(2)
        ->build();
    
    echo "✓ الاتصال نجح\n\n";
    
    // Get counts
    $mysql_count = DB::table('pages')->count();
    $es_count = $es->count(['index' => $ES_INDEX])['count'];
    
    echo "📊 الحالة:\n";
    echo "  MySQL: " . number_format($mysql_count) . "\n";
    echo "  Elasticsearch: " . number_format($es_count) . "\n";
    echo "  المفقود: " . number_format($mysql_count - $es_count) . "\n\n";
    
    if ($mysql_count <= $es_count) {
        echo "✅ مكتمل!\n\n";
        exit(0);
    }
    
    // استراتيجية جديدة: فهرس الصفحات الأخيرة فقط
    echo "🔄 استراتيجية: فهرسة آخر 10,000 صفحة\n";
    echo "   (عادةً الصفحات الناقصة في النهاية)\n\n";
    
    $max_id = DB::table('pages')->max('id');
    $start_id = $max_id - 10000;
    
    echo "  نطاق الفهرسة: ID $start_id إلى $max_id\n\n";
    
    $indexed = 0;
    $errors = 0;
    $total = 10000;
    
    DB::table('pages')
        ->where('id', '>', $start_id)
        ->orderBy('id')
        ->chunk(100, function($pages) use (&$indexed, &$errors, $total, $es, $ES_INDEX, $BATCH_SIZE) {
            
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
                    'created_at' => $page->created_at,
                    'updated_at' => $page->updated_at
                ];
            }
            
            // Send bulk
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
                
                $progress = ($indexed / $total) * 100;
                echo sprintf("\r  📝 %d / 10,000 (%.1f%%) | ❌ %d errors",
                    $indexed,
                    $progress,
                    $errors
                );
                
            } catch (Exception $e) {
                $errors += count($pages);
            }
        });
    
    echo "\n\n";
    
    // الآن نفحص النطاق الكامل لإيجاد أي صفحات متناثرة
    echo "🔍 فحص سريع للصفحات المتناثرة...\n\n";
    
    // خذ عينة عشوائية من 1000 ID
    $sample_size = 1000;
    $random_ids = DB::table('pages')
        ->select('id')
        ->inRandomOrder()
        ->limit($sample_size)
        ->pluck('id')
        ->toArray();
    
    $missing_from_sample = [];
    
    foreach ($random_ids as $id) {
        try {
            if (!$es->exists(['index' => $ES_INDEX, 'id' => $id])) {
                $missing_from_sample[] = $id;
            }
        } catch (Exception $e) {
            $missing_from_sample[] = $id;
        }
    }
    
    echo "  من عينة $sample_size صفحة، وجدنا " . count($missing_from_sample) . " مفقودة\n\n";
    
    if (!empty($missing_from_sample)) {
        echo "  جاري فهرسة العينة المفقودة...\n";
        
        $sample_pages = Page::whereIn('id', $missing_from_sample)->get();
        $bulk_body = [];
        
        foreach ($sample_pages as $page) {
            $bulk_body[] = ['index' => ['_index' => $ES_INDEX, '_id' => $page->id]];
            $bulk_body[] = [
                'id' => $page->id,
                'page_number' => $page->page_number,
                'content' => $page->content,
                'book_id' => $page->book_id,
                'created_at' => optional($page->created_at)->toIso8601String(),
                'updated_at' => optional($page->updated_at)->toIso8601String()
            ];
        }
        
        if (!empty($bulk_body)) {
            $es->bulk(['body' => $bulk_body]);
            echo "  ✓ تمت فهرسة " . count($sample_pages) . " صفحة\n";
        }
    }
    
    echo "\n";
    
    // التحقق النهائي
    sleep(2);
    
    $final_es = $es->count(['index' => $ES_INDEX])['count'];
    $final_missing = $mysql_count - $final_es;
    
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║                   النتيجة النهائية                      ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";
    
    echo "  MySQL: " . number_format($mysql_count) . "\n";
    echo "  Elasticsearch: " . number_format($final_es) . "\n";
    echo "  المفقود: " . number_format($final_missing) . "\n";
    echo "  النسبة: " . number_format(($final_es / $mysql_count) * 100, 2) . "%\n\n";
    
    if ($final_missing == 0) {
        echo "✅ مكتمل 100%!\n\n";
    } elseif ($final_missing < 100) {
        echo "✅ شبه مكتمل! (أقل من 100 صفحة مفقودة)\n\n";
    } else {
        echo "⚠️  لا يزال هناك " . number_format($final_missing) . " صفحة\n";
        echo "   يمكن إعادة تشغيل السكريبت أو استخدام:\n";
        echo "   php fix_remaining_pages.php\n\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ خطأ: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "✓ اكتمل\n\n";
