<?php
/**
 * إعادة الفهرسة من الصفر بعد تطبيق ignore_above
 */

$elasticsearchUrl = "http://145.223.98.97:9201";
$indexName = "pages_new_search";

echo "🔄 إعادة تشغيل الفهرسة من الصفر\n";
echo str_repeat("=", 60) . "\n\n";

// 1. حذف Index القديم
echo "🗑️  خطوة 1: حذف Index القديم...\n";
$ch = curl_init("$elasticsearchUrl/$indexName");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200 || $httpCode == 404) {
    echo "✅ Index محذوف\n\n";
} else {
    echo "❌ فشل حذف Index: $response\n\n";
    exit(1);
}

// 2. إنشاء Index جديد (سيستخدم Template تلقائياً)
echo "📝 خطوة 2: إنشاء Index جديد...\n";
$ch = curl_init("$elasticsearchUrl/$indexName");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Index جديد تم إنشاؤه بنجاح\n\n";
} else {
    echo "❌ فشل إنشاء Index: $response\n\n";
    exit(1);
}

// 3. حذف Logstash tracking file
echo "🔄 خطوة 3: حذف Logstash tracking file...\n";
$trackingFile = __DIR__ . "/logstash-setup/data/.logstash_jdbc_last_run_pages";
if (file_exists($trackingFile)) {
    unlink($trackingFile);
    echo "✅ Tracking file محذوف\n\n";
} else {
    echo "ℹ️  Tracking file غير موجود (سيبدأ من ID 0)\n\n";
}

echo "════════════════════════════════════════════════════════════\n";
echo "                    ✅ جاهز للتشغيل!\n";
echo "════════════════════════════════════════════════════════════\n\n";

echo "📋 ما تم:\n";
echo "  ✅ Index القديم محذوف\n";
echo "  ✅ Index جديد تم إنشاؤه (مع ignore_above: 32000)\n";
echo "  ✅ Tracking file محذوف\n\n";

echo "🔄 الخطوة التالية:\n";
echo "  cd logstash-setup\n";
echo "  docker-compose restart\n\n";

echo "⏱️  سرعة الفهرسة المتوقعة:\n";
echo "  - ~16,000 صفحة/ثانية\n";
echo "  - الوقت الإجمالي: ~5 دقائق\n";
echo "  - النصوص الطويلة سيتم تجاهلها في content.exact فقط\n";
