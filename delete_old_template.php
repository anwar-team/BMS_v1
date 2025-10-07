<?php
$elasticsearchUrl = "http://145.223.98.97:9201";

echo "🗑️  حذف Template القديم...\n\n";

// حذف pages_template القديم
$ch = curl_init("$elasticsearchUrl/_index_template/pages_template");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ تم حذف pages_template\n\n";
} else {
    echo "⚠️  pages_template غير موجود أو محذوف\n\n";
}

echo "✅ جاهز! الآن شغل: php update_template_fix_long_content.php\n";
