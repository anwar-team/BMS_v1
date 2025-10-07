<?php
/**
 * فحص سرعة الفهرسة - قياس دقيق للصفحات/الثانية
 */

$esHost = 'http://145.223.98.97:9201';
$index = 'pages_new_search';

echo "════════════════════════════════════════════════════════════\n";
echo "         📊 قياس سرعة الفهرسة - الهدف: 10,000/ثانية        \n";
echo "════════════════════════════════════════════════════════════\n\n";

// قراءة العدد الأولي
$ch = curl_init("$esHost/$index/_count");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch), true);
$initialCount = $response['count'] ?? 0;
curl_close($ch);

echo "📈 العدد الأولي: " . number_format($initialCount) . "\n";
echo "⏱️ بدء القياس لمدة 30 ثانية...\n\n";

$measurements = [];
$startTime = time();

// قياس كل 5 ثوان لمدة 30 ثانية
for ($i = 0; $i < 6; $i++) {
    sleep(5);
    
    $ch = curl_init("$esHost/$index/_count");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    $currentCount = $response['count'] ?? 0;
    curl_close($ch);
    
    $delta = $currentCount - $initialCount;
    $elapsed = time() - $startTime;
    $pagesPerSecond = $elapsed > 0 ? $delta / $elapsed : 0;
    
    $measurements[] = $pagesPerSecond;
    
    $status = $pagesPerSecond >= 8000 ? '✅' : ($pagesPerSecond >= 5000 ? '⚡' : '🐌');
    
    echo sprintf(
        "[%02d ثانية] %s %s | +%s | %.0f صفحة/ثانية\n",
        $elapsed,
        $status,
        number_format($currentCount),
        number_format($delta),
        $pagesPerSecond
    );
    
    $initialCount = $currentCount;
}

echo "\n════════════════════════════════════════════════════════════\n";
echo "                    📊 النتائج النهائية                    \n";
echo "════════════════════════════════════════════════════════════\n\n";

$avgSpeed = array_sum($measurements) / count($measurements);
$maxSpeed = max($measurements);
$minSpeed = min($measurements);

echo "📈 متوسط السرعة: " . number_format($avgSpeed, 0) . " صفحة/ثانية\n";
echo "⬆️ أعلى سرعة: " . number_format($maxSpeed, 0) . " صفحة/ثانية\n";
echo "⬇️ أدنى سرعة: " . number_format($minSpeed, 0) . " صفحة/ثانية\n\n";

echo "🎯 الهدف: 10,000 صفحة/ثانية\n";

if ($avgSpeed >= 8000) {
    echo "✅ ممتاز! السرعة قريبة من الهدف!\n";
} elseif ($avgSpeed >= 5000) {
    echo "⚡ جيد! لكن يمكن تحسينها أكثر\n";
} else {
    echo "🐌 بطيء! حاجة تحسين كبير\n";
}

echo "\n";
