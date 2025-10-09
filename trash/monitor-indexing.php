<?php
require 'vendor/autoload.php';

$client = Elasticsearch\ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

echo "════════════════════════════════════════════════════════\n";
echo "   مراقبة تقدم الفهرسة\n";
echo "════════════════════════════════════════════════════════\n\n";

$targetCount = 4309914; // العدد الإجمالي للصفحات

while (true) {
    try {
        $stats = $client->indices()->stats(['index' => 'pages_new_search']);
        $currentCount = $stats['indices']['pages_new_search']['primaries']['docs']['count'] ?? 0;
        $size = ($stats['indices']['pages_new_search']['primaries']['store']['size_in_bytes'] ?? 0) / (1024*1024*1024);
        
        $percentage = ($currentCount / $targetCount) * 100;
        $bar = str_repeat('█', (int)($percentage / 2));
        $space = str_repeat('░', 50 - (int)($percentage / 2));
        
        echo "\r";
        echo sprintf(
            "[%s%s] %0.1f%% | %s / %s | %.2f GB",
            $bar,
            $space,
            $percentage,
            number_format($currentCount),
            number_format($targetCount),
            $size
        );
        
        if ($currentCount >= $targetCount) {
            echo "\n\n✅ اكتملت الفهرسة!\n";
            break;
        }
        
        sleep(5); // تحديث كل 5 ثواني
        
    } catch (Exception $e) {
        echo "\n❌ خطأ: " . $e->getMessage() . "\n";
        break;
    }
}

echo "════════════════════════════════════════════════════════\n";
