<?php

$elasticsearchHost = 'http://145.223.98.97:9201';
$indexName = 'pages_new_search';

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
    $totalPages = 5024544;
    $progress = ($currentCount / $totalPages) * 100;
    
    echo "📊 حالة الفهرسة:\n";
    echo "════════════════════════════════════════════════════════════\n";
    echo "✅ المفهرس: " . number_format($currentCount) . " / " . number_format($totalPages) . "\n";
    echo "📈 التقدم: " . number_format($progress, 2) . "%\n";
    
    $bar = str_repeat('█', (int)($progress / 2));
    $space = str_repeat('░', 50 - (int)($progress / 2));
    echo "Progress: [{$bar}{$space}]\n";
    
    echo "════════════════════════════════════════════════════════════\n";
} else {
    echo "❌ خطأ في الاتصال (HTTP {$httpCode})\n";
}
