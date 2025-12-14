<?php

/**
 * ====================================================================
 * فحص حالة الفهرسة بسرعة
 * ====================================================================
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Elasticsearch\ClientBuilder;

$ES_HOST = 'http://145.223.98.97:9201';
$ES_INDEX = 'pages_new_search';

echo "\n=== فحص حالة الفهرسة ===\n\n";

try {
    // عدد الصفحات في MySQL
    $mysql_count = DB::table('pages')->count();
    echo "✓ MySQL Total Pages: " . number_format($mysql_count) . "\n";
    
    // عدد الصفحات في Elasticsearch
    $es = ClientBuilder::create()->setHosts([$ES_HOST])->build();
    $es_response = $es->count(['index' => $ES_INDEX]);
    $es_count = $es_response['count'];
    
    echo "✓ Elasticsearch Indexed: " . number_format($es_count) . "\n";
    
    // الفرق
    $missing = $mysql_count - $es_count;
    $percentage = ($es_count / $mysql_count) * 100;
    
    echo "\n";
    echo "Missing Pages: " . number_format($missing) . "\n";
    echo "Progress: " . number_format($percentage, 2) . "%\n";
    
    if ($missing == 0) {
        echo "\n✅ الفهرسة مكتملة 100%!\n\n";
    } else {
        echo "\n⚠️ يوجد " . number_format($missing) . " صفحة لم تُفهرس بعد\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n\n";
}
