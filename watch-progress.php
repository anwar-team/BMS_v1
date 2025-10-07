<?php

// مراقبة مستمرة للفهرسة
$elasticsearchHost = 'http://145.223.98.97:9201';
$indexName = 'pages_new_search';
$totalPages = 5024544;
$checkInterval = 10; // ثواني

echo "════════════════════════════════════════════════════════════\n";
echo "📊 مراقبة الفهرسة - تحديث كل {$checkInterval} ثواني\n";
echo "════════════════════════════════════════════════════════════\n\n";

$startTime = time();
$lastCount = 0;
$checks = 0;

while (true) {
    $checks++;
    
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
        
        $progress = ($currentCount / $totalPages) * 100;
        $elapsed = time() - $startTime;
        $rate = $elapsed > 0 ? ($currentCount - 56005) / $elapsed : 0;
        $delta = $currentCount - $lastCount;
        $lastCount = $currentCount;
        
        $timestamp = date('H:i:s');
        
        echo "[$timestamp] ";
        echo number_format($currentCount) . " / " . number_format($totalPages) . " ";
        echo "(" . number_format($progress, 2) . "%) ";
        echo "| +" . number_format($delta) . " ";
        echo "| " . number_format($rate) . "/sec ";
        
        if ($rate > 0) {
            $remaining = $totalPages - $currentCount;
            $eta = $remaining / $rate;
            echo "| ETA: " . gmdate('H:i:s', $eta);
        }
        
        echo "\n";
        
        if ($currentCount >= $totalPages) {
            echo "\n🎉 اكتملت الفهرسة!\n";
            break;
        }
    } else {
        echo "[$timestamp] ❌ خطأ في الاتصال\n";
    }
    
    sleep($checkInterval);
    
    // كل 100 فحص، اعرض إحصائيات
    if ($checks % 100 == 0) {
        echo "\n--- إحصائيات بعد " . gmdate('H:i:s', time() - $startTime) . " ---\n";
        echo "Checks: {$checks} | Avg Rate: " . number_format($rate) . "/sec\n\n";
    }
}

echo "\n✨ الفهرسة اكتملت بنجاح!\n";
