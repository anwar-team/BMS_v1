<?php

require __DIR__.'/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$elasticsearchHost = getenv('ELASTICSEARCH_HOST') ?: 'http://145.223.98.97:9201';
$indexName = 'pages_new_search';

$client = ClientBuilder::create()
    ->setHosts([$elasticsearchHost])
    ->setSSLVerification(false)
    ->build();

echo "════════════════════════════════════════════════════════\n";
echo "   اختبار Elasticsearch Analyzers\n";
echo "════════════════════════════════════════════════════════\n\n";

// التحقق من وجود Index
try {
    if (!$client->indices()->exists(['index' => $indexName])) {
        echo "❌ Index غير موجود: $indexName\n";
        echo "   قم بتشغيل: php create-new-search-index.php\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ خطأ في الاتصال: " . $e->getMessage() . "\n";
    exit(1);
}

// نصوص الاختبار
$testTexts = [
    'صلاة',
    'الصلاة',
    'بالصلاة',
    'صلى',
    'يصلي',
    'إسلام',
    'اسلام',
    'الإسلام',
    'كتاب',
    'الكتاب',
    'كاتب',
];

foreach ($testTexts as $text) {
    echo "📝 النص: \"$text\"\n";
    echo str_repeat('-', 60) . "\n";
    
    // 1. اختبار arabic_exact
    try {
        $response = $client->indices()->analyze([
            'index' => $indexName,
            'body' => [
                'analyzer' => 'arabic_exact',
                'text' => $text
            ]
        ]);
        
        $tokens = array_map(function($token) {
            return $token['token'];
        }, $response['tokens']);
        
        echo "🎯 البحث المطابق (exact):    " . implode(', ', $tokens) . "\n";
    } catch (Exception $e) {
        echo "❌ خطأ: " . $e->getMessage() . "\n";
    }
    
    // 2. اختبار arabic_flexible
    try {
        $response = $client->indices()->analyze([
            'index' => $indexName,
            'body' => [
                'analyzer' => 'arabic_flexible',
                'text' => $text
            ]
        ]);
        
        $tokens = array_map(function($token) {
            return $token['token'];
        }, $response['tokens']);
        
        echo "🔄 البحث المرن (flexible):   " . implode(', ', $tokens) . "\n";
    } catch (Exception $e) {
        echo "❌ خطأ: " . $e->getMessage() . "\n";
    }
    
    // 3. اختبار arabic_stemmed
    try {
        $response = $client->indices()->analyze([
            'index' => $indexName,
            'body' => [
                'analyzer' => 'arabic_stemmed',
                'text' => $text
            ]
        ]);
        
        $tokens = array_map(function($token) {
            return $token['token'];
        }, $response['tokens']);
        
        echo "🌳 البحث الصرفي (stemmed):   " . implode(', ', $tokens) . "\n";
    } catch (Exception $e) {
        echo "❌ خطأ: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "════════════════════════════════════════════════════════\n";
echo "   التحليل المتوقع:\n";
echo "════════════════════════════════════════════════════════\n";
echo "🎯 Exact:      يحتفظ بالنص كما هو (صلاة ≠ الصلاة)\n";
echo "🔄 Flexible:   يزيل اللواصق ويوحد الأحرف (صلاة = الصلاة)\n";
echo "🌳 Stemmed:    يرجع للجذر (صلاة = صلى = يصلي)\n";
echo "════════════════════════════════════════════════════════\n";
