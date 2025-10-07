<?php

/**
 * تطبيق Index Template الجديد على Elasticsearch
 */

require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

echo "════════════════════════════════════════════════════════\n";
echo "   تطبيق Index Template الجديد\n";
echo "════════════════════════════════════════════════════════\n\n";

$client = ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

// قراءة Template من الملف
$templatePath = __DIR__ . '/logstash-setup/elasticsearch/pages_new_search_template.json';
if (!file_exists($templatePath)) {
    echo "❌ خطأ: ملف Template غير موجود: $templatePath\n";
    exit(1);
}

$templateContent = file_get_contents($templatePath);
$template = json_decode($templateContent, true);

if (!$template) {
    echo "❌ خطأ: فشل قراءة Template JSON\n";
    exit(1);
}

echo "📄 Template File: $templatePath\n";
echo "🔗 Elasticsearch: http://145.223.98.97:9201\n\n";

// حذف Template القديم إن وجد
echo "1️⃣ حذف Template القديم (إن وجد)...\n";
try {
    $client->indices()->deleteIndexTemplate(['name' => 'pages_new_search_template']);
    echo "✅ تم حذف Template القديم\n\n";
} catch (Exception $e) {
    echo "ℹ️  لا يوجد template سابق\n\n";
}

// تطبيق Template الجديد
echo "2️⃣ تطبيق Template الجديد...\n";
try {
    $response = $client->indices()->putIndexTemplate([
        'name' => 'pages_new_search_template',
        'body' => $template
    ]);
    
    if (isset($response['acknowledged']) && $response['acknowledged']) {
        echo "✅ تم تطبيق Template بنجاح!\n\n";
    } else {
        echo "❌ فشل تطبيق Template\n";
        print_r($response);
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

// التحقق من Template
echo "3️⃣ التحقق من Template...\n";
try {
    $response = $client->indices()->getIndexTemplate(['name' => 'pages_new_search_template']);
    
    if (isset($response['index_templates'][0])) {
        $tmpl = $response['index_templates'][0]['index_template'];
        
        echo "✅ Index Patterns: " . json_encode($tmpl['index_patterns']) . "\n";
        
        $analyzers = array_keys($tmpl['template']['settings']['analysis']['analyzer']);
        echo "✅ Analyzers: " . json_encode($analyzers) . "\n";
        
        $fields = array_keys($tmpl['template']['mappings']['properties']['content']['fields']);
        echo "✅ Content Fields: " . json_encode($fields) . "\n\n";
    }
} catch (Exception $e) {
    echo "❌ خطأ في التحقق: " . $e->getMessage() . "\n";
}

echo "════════════════════════════════════════════════════════\n";
echo "   ✅ اكتمل بنجاح!\n";
echo "════════════════════════════════════════════════════════\n\n";

echo "الخطوة التالية:\n";
echo "  cd logstash-setup\n";
echo "  docker-compose up -d\n\n";
