<?php
echo "════════════════════════════════════════════════════════════\n";
echo "     🔄 حذف Index وإعادة البدء من الصفر الكامل\n";
echo "════════════════════════════════════════════════════════════\n\n";

$elasticsearchUrl = "http://145.223.98.97:9201";

// خطوة 1: حذف Index القديم
echo "🗑️  خطوة 1: حذف Index القديم...\n";
$ch = curl_init("{$elasticsearchUrl}/pages_new_search");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
$response = curl_exec($ch);
curl_close($ch);
echo "✅ Index محذوف\n\n";

// خطوة 2: إنشاء Index جديد
echo "📝 خطوة 2: إنشاء Index جديد...\n";
$ch = curl_init("{$elasticsearchUrl}/pages_new_search");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
$response = curl_exec($ch);
curl_close($ch);
echo "✅ Index جديد تم إنشاؤه\n\n";

// خطوة 3: حذف كل ملفات tracking
echo "🔄 خطوة 3: حذف ملفات tracking...\n";
exec("docker exec bms_logstash_arabic sh -c 'rm -rf /usr/share/logstash/data/.logstash_jdbc_last_run*'", $output, $return);
echo "✅ ملفات tracking محذوفة\n\n";

// خطوة 4: إيقاف Container
echo "⏸️  خطوة 4: إيقاف Logstash...\n";
exec("cd logstash-setup && docker-compose stop", $output, $return);
echo "✅ Logstash متوقف\n\n";

// خطوة 5: تشغيل Container من جديد
echo "🚀 خطوة 5: تشغيل Logstash...\n";
exec("cd logstash-setup && docker-compose start", $output, $return);
echo "✅ Logstash يعمل الآن\n\n";

echo "════════════════════════════════════════════════════════════\n";
echo "                    ✅ تم بنجاح!\n";
echo "════════════════════════════════════════════════════════════\n\n";

echo "📋 ما تم:\n";
echo "  ✅ حذف Index القديم (3.5M صفحة)\n";
echo "  ✅ إنشاء Index جديد فارغ\n";
echo "  ✅ حذف كل ملفات tracking\n";
echo "  ✅ إعادة تشغيل Logstash\n\n";

echo "⏱️  انتظر 30 ثانية ثم شيك: php check-system-status.php\n\n";

echo "📊 المتوقع:\n";
echo "  - البدء من ID = 822 (أول صفحة في MySQL)\n";
echo "  - فهرسة 5,024,544 صفحة\n";
echo "  - السرعة: ~1,000-2,000 صفحة/ثانية\n";
echo "  - الوقت الكلي: ~45-90 دقيقة\n";
