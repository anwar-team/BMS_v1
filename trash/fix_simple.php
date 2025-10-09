<?php

/**
 * ====================================================================
 * ✅ حل نهائي بسيط: فهرسة الصفحات المفقودة
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

echo "\n⚡ فهرسة الصفحات المفقودة\n\n";

// الاتصال
$es = ClientBuilder::create()->setHosts([$ES_HOST])->setRetries(2)->build();

// فحص العدد
$mysql_count = DB::table('pages')->count();
$es_count = $es->count(['index' => $ES_INDEX])['count'];

echo "MySQL: " . number_format($mysql_count) . "\n";
echo "Elasticsearch: " . number_format($es_count) . "\n";
echo "Missing: " . number_format($mysql_count - $es_count) . "\n\n";

if ($mysql_count <= $es_count) {
    echo "✅ لا توجد صفحات مفقودة!\n\n";
    exit(0);
}

// استراتيجية بسيطة: فحص كل 10K صفحة
echo "جاري البحث عن الصفحات المفقودة...\n";

$missing = [];
$checked = 0;

DB::table('pages')
    ->select('id')
    ->orderBy('id')
    ->chunk(10000, function($pages) use (&$missing, &$checked, $es, $ES_INDEX) {
        foreach ($pages as $page) {
            try {
                if (!$es->exists(['index' => $ES_INDEX, 'id' => $page->id])) {
                    $missing[] = $page->id;
                }
            } catch (Exception $e) {
                $missing[] = $page->id;
            }
            
            $checked++;
            if ($checked % 1000 == 0) {
                echo "Checked: " . number_format($checked) . " | Missing: " . count($missing) . "\r";
            }
        }
    });

echo "\n\n✅ Found " . count($missing) . " missing pages\n\n";

if (empty($missing)) {
    echo "No missing pages!\n\n";
    exit(0);
}

// فهرسة
echo "جاري الفهرسة...\n";

$indexed = 0;
$batch = [];

foreach (array_chunk($missing, 100) as $chunk_ids) {
    $pages = Page::with('book')->whereIn('id', $chunk_ids)->get();
    
    foreach ($pages as $page) {
        $batch[] = ['index' => ['_index' => $ES_INDEX, '_id' => $page->id]];
        $batch[] = [
            'id' => $page->id,
            'page_number' => $page->page_number,
            'content' => [
                'exact' => $page->content,
                'flexible' => $page->content,
                'stemmed' => $page->content
            ],
            'book_id' => $page->book_id,
            'book_title' => optional($page->book)->title,
            'created_at' => optional($page->created_at)->toIso8601String(),
            'updated_at' => optional($page->updated_at)->toIso8601String()
        ];
        
        if (count($batch) >= 1000) {
            $es->bulk(['body' => $batch]);
            $indexed += 500;
            echo "Indexed: " . number_format($indexed) . " / " . number_format(count($missing)) . "\r";
            $batch = [];
        }
    }
}

if (count($batch) > 0) {
    $es->bulk(['body' => $batch]);
    $indexed += (count($batch) / 2);
}

echo "\n\n✅ Indexed " . number_format($indexed) . " pages\n";

// التحقق
sleep(2);
$final = $es->count(['index' => $ES_INDEX])['count'];
echo "\nFinal count: " . number_format($final) . "\n";

if ($final >= $mysql_count) {
    echo "🎉 Success! All pages indexed!\n\n";
} else {
    echo "⚠️ Still missing: " . number_format($mysql_count - $final) . "\n\n";
}
