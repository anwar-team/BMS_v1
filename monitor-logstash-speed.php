<?php

// مراقبة سرعة Logstash

$elasticsearchHost = 'http://145.223.98.97:9201';
$indexName = 'pages_new_search';
$totalPages = 5024544;
$targetRate = 10000; // الهدف: 10,000 صفحة/دقيقة

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║       🚀 مراقبة سرعة Logstash - الهدف 10K/min           ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$startTime = time();
$lastCount = 0;
$lastCheckTime = time();
$measurements = [];

// احصل على العدد الأولي
$url = "{$elasticsearchHost}/{$indexName}/_count";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    $lastCount = $result['count'] ?? 0;
}

echo "📊 العدد الأولي: " . number_format($lastCount) . "\n";
echo "🎯 الهدف: " . number_format($targetRate) . " صفحة/دقيقة\n";
echo "⏱️ التحديث كل 10 ثواني\n\n";
echo "════════════════════════════════════════════════════════════\n\n";

while (true) {
    sleep(10);
    
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
        
        $now = time();
        $elapsedSinceCheck = $now - $lastCheckTime;
        $delta = $currentCount - $lastCount;
        
        // حساب السرعة
        $ratePerSecond = $elapsedSinceCheck > 0 ? $delta / $elapsedSinceCheck : 0;
        $ratePerMinute = $ratePerSecond * 60;
        
        // حفظ القياس
        $measurements[] = [
            'time' => $now,
            'count' => $currentCount,
            'delta' => $delta,
            'rate_per_min' => $ratePerMinute
        ];
        
        // الاحتفاظ بآخر 20 قياس فقط
        if (count($measurements) > 20) {
            array_shift($measurements);
        }
        
        // متوسط السرعة
        $avgRate = 0;
        if (count($measurements) > 1) {
            $totalRate = array_sum(array_column($measurements, 'rate_per_min'));
            $avgRate = $totalRate / count($measurements);
        }
        
        // الإحصائيات
        $progress = ($currentCount / $totalPages) * 100;
        $totalElapsed = $now - $startTime;
        $overallRate = $totalElapsed > 0 ? ($currentCount - $measurements[0]['count']) / $totalElapsed * 60 : 0;
        
        $remaining = $totalPages - $currentCount;
        $eta = $avgRate > 0 ? ($remaining / $avgRate) * 60 : 0;
        
        // الألوان (emoji)
        $speedEmoji = $ratePerMinute >= $targetRate ? '🚀' : ($ratePerMinute >= $targetRate * 0.5 ? '⚡' : '🐌');
        
        $timestamp = date('H:i:s');
        
        echo sprintf(
            "[%s] %s %s/%s (%.2f%%) | +%s | %s %.0f/min (avg: %.0f) | ETA: %s\n",
            $timestamp,
            $speedEmoji,
            number_format($currentCount),
            number_format($totalPages),
            $progress,
            number_format($delta),
            $ratePerMinute >= $targetRate ? '✅' : '⚠️',
            $ratePerMinute,
            $avgRate,
            gmdate('H:i:s', $eta)
        );
        
        // تنبيهات
        if ($ratePerMinute < $targetRate * 0.3 && count($measurements) > 3) {
            echo "   ⚠️ السرعة منخفضة جداً! المتوقع: " . number_format($targetRate) . "/min\n";
        }
        
        // إحصائيات كل دقيقة
        if (count($measurements) % 6 == 0 && count($measurements) > 0) {
            echo "\n   📊 إحصائيات الدقيقة الأخيرة:\n";
            echo sprintf(
                "      معدل: %.0f/min | أعلى: %.0f/min | أدنى: %.0f/min\n\n",
                $avgRate,
                max(array_column($measurements, 'rate_per_min')),
                min(array_column($measurements, 'rate_per_min'))
            );
        }
        
        $lastCount = $currentCount;
        $lastCheckTime = $now;
        
        if ($currentCount >= $totalPages) {
            echo "\n🎉 اكتملت الفهرسة!\n";
            echo "════════════════════════════════════════════════════════════\n";
            echo "   الوقت الكلي: " . gmdate('H:i:s', $totalElapsed) . "\n";
            echo "   المعدل النهائي: " . number_format($overallRate) . " صفحة/دقيقة\n";
            echo "════════════════════════════════════════════════════════════\n";
            break;
        }
        
    } else {
        echo "❌ خطأ في الاتصال\n";
    }
}

echo "\n✨ انتهت المراقبة\n";
