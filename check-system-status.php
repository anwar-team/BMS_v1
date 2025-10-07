<?php
/**
 * فحص شامل لحالة الفهرسة والسرعة
 */

$esHost = 'http://145.223.98.97:9201';
$index = 'pages_new_search';

echo "════════════════════════════════════════════════════════════\n";
echo "         🔍 فحص شامل لحالة نظام الفهرسة                    \n";
echo "════════════════════════════════════════════════════════════\n\n";

// 1. فحص Elasticsearch
echo "📡 فحص اتصال Elasticsearch...\n";
$ch = curl_init("$esHost/_cluster/health");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Elasticsearch يعمل بشكل صحيح\n\n";
} else {
    echo "❌ خطأ في الاتصال بـ Elasticsearch\n\n";
    exit(1);
}

// 2. فحص Index
echo "📊 فحص Index: $index\n";
$ch = curl_init("$esHost/$index/_count");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

$currentCount = $response['count'] ?? 0;
echo "📄 عدد الصفحات المفهرسة: " . number_format($currentCount) . "\n";

// 3. فحص آخر صفحة مفهرسة
$ch = curl_init("$esHost/$index/_search");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'size' => 1,
    'sort' => [['id' => 'desc']],
    '_source' => ['id', 'page_number', 'book_title', 'book_author']
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!empty($response['hits']['hits'])) {
    $lastDoc = $response['hits']['hits'][0]['_source'];
    echo "🔖 آخر صفحة مفهرسة: ID " . $lastDoc['id'] . "\n";
    echo "   الكتاب: " . ($lastDoc['book_title'] ?? 'غير محدد') . "\n";
    echo "   المؤلف: " . ($lastDoc['book_author'] ?? 'غير محدد') . "\n\n";
}

// 4. قياس السرعة
echo "════════════════════════════════════════════════════════════\n";
echo "                    ⚡ قياس سرعة الفهرسة                   \n";
echo "════════════════════════════════════════════════════════════\n\n";

$measurements = [];
$startCount = $currentCount;
$startTime = time();

echo "⏱️ قياس السرعة لمدة 30 ثانية...\n\n";

for ($i = 1; $i <= 6; $i++) {
    sleep(5);
    
    $ch = curl_init("$esHost/$index/_count");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    
    $currentCount = $response['count'] ?? 0;
    $delta = $currentCount - $startCount;
    $elapsed = time() - $startTime;
    $pagesPerSecond = $elapsed > 0 ? $delta / $elapsed : 0;
    
    $measurements[] = $pagesPerSecond;
    
    $status = $pagesPerSecond >= 500 ? '🚀' : ($pagesPerSecond >= 100 ? '⚡' : ($pagesPerSecond > 0 ? '🐌' : '⏸️'));
    
    echo sprintf(
        "[%02ds] %s %s صفحات | +%s | %.0f صفحة/ثانية\n",
        $elapsed,
        $status,
        number_format($currentCount),
        number_format($delta),
        $pagesPerSecond
    );
}

echo "\n════════════════════════════════════════════════════════════\n";
echo "                      📈 نتائج التحليل                     \n";
echo "════════════════════════════════════════════════════════════\n\n";

$avgSpeed = array_sum($measurements) / count($measurements);
$maxSpeed = max($measurements);

echo "📊 متوسط السرعة: " . number_format($avgSpeed, 0) . " صفحة/ثانية\n";
echo "⬆️ أعلى سرعة: " . number_format($maxSpeed, 0) . " صفحة/ثانية\n\n";

// 5. تقدير الوقت المتبقي
$totalPages = 5024544;
$remaining = $totalPages - $currentCount;
$progressPercent = ($currentCount / $totalPages) * 100;

echo "📊 التقدم: " . number_format($progressPercent, 2) . "%\n";
echo "📄 المتبقي: " . number_format($remaining) . " صفحة\n";

if ($avgSpeed > 0) {
    $remainingSeconds = $remaining / $avgSpeed;
    $remainingMinutes = $remainingSeconds / 60;
    $remainingHours = $remainingMinutes / 60;
    
    echo "⏱️ الوقت المتوقع للإكمال: ";
    if ($remainingHours < 1) {
        echo number_format($remainingMinutes, 1) . " دقيقة\n";
    } else if ($remainingHours < 24) {
        $hours = floor($remainingHours);
        $mins = round(($remainingHours - $hours) * 60);
        echo "$hours ساعة و $mins دقيقة\n";
    } else {
        $days = floor($remainingHours / 24);
        $hours = round($remainingHours - ($days * 24));
        echo "$days يوم و $hours ساعة\n";
    }
}

echo "\n";

// 6. الحالة العامة
echo "════════════════════════════════════════════════════════════\n";
echo "                      ✅ الحالة العامة                     \n";
echo "════════════════════════════════════════════════════════════\n\n";

if ($avgSpeed > 500) {
    echo "🎉 ممتاز! النظام يعمل بسرعة جيدة جداً\n";
    echo "✅ Logstash يفهرس بشكل صحيح\n";
    echo "✅ السرعة مقبولة للغاية\n";
} else if ($avgSpeed > 100) {
    echo "⚡ جيد! النظام يعمل لكن السرعة يمكن تحسينها\n";
    echo "✅ Logstash يفهرس بشكل صحيح\n";
    echo "⚠️ السرعة أقل من المتوقع\n";
} else if ($avgSpeed > 0) {
    echo "🐌 بطيء! النظام يعمل لكن السرعة بطيئة جداً\n";
    echo "✅ Logstash يفهرس لكن ببطء شديد\n";
    echo "⚠️ يحتاج تحسينات عاجلة\n";
} else {
    echo "⏸️ توقف! لا يوجد فهرسة جارية\n";
    echo "❌ Logstash متوقف أو يوجد مشكلة\n";
    echo "🔧 يحتاج فحص وإصلاح\n";
}

echo "\n";

// 7. فحص Logstash logs (آخر خطأ)
echo "════════════════════════════════════════════════════════════\n";
echo "                   📋 آخر نشاط Logstash                    \n";
echo "════════════════════════════════════════════════════════════\n\n";

exec('docker logs bms_logstash_arabic --tail=20 2>&1 | findstr /C:"ERROR" /C:"SELECT" /C:"INFO"', $output);

if (empty($output)) {
    echo "✅ لا توجد أخطاء واضحة في Logs\n";
} else {
    $lastLines = array_slice($output, -5);
    foreach ($lastLines as $line) {
        if (stripos($line, 'ERROR') !== false) {
            echo "❌ " . substr($line, 0, 100) . "...\n";
        } else if (stripos($line, 'SELECT') !== false) {
            echo "✅ Query يعمل\n";
        } else {
            echo "ℹ️  " . substr($line, 0, 100) . "...\n";
        }
    }
}

echo "\n";
