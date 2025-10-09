<?php

/**
 * التحقق من التكامل الكامل للنظام
 */

require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

echo "════════════════════════════════════════════════════════\n";
echo "   التحقق من التكامل الكامل للنظام\n";
echo "════════════════════════════════════════════════════════\n\n";

$client = ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

// 1. التحقق من الـ Index
echo "1️⃣ التحقق من Index 'pages_new_search':\n";
echo "─────────────────────────────────────────────────────\n";

try {
    $exists = $client->indices()->exists(['index' => 'pages_new_search']);
    if ($exists) {
        echo "✅ Index موجود\n";
        
        $stats = $client->indices()->stats(['index' => 'pages_new_search']);
        $count = $stats['_all']['primaries']['docs']['count'];
        echo "✅ عدد المستندات: " . number_format($count) . "\n";
        
        $settings = $client->indices()->getSettings(['index' => 'pages_new_search']);
        echo "✅ الإعدادات محفوظة\n";
    } else {
        echo "❌ Index غير موجود!\n";
    }
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. التحقق من Analyzers
echo "2️⃣ التحقق من Analyzers الثلاثة:\n";
echo "─────────────────────────────────────────────────────\n";

$testWord = "الصلاة";
$analyzers = [
    'arabic_exact' => '🎯',
    'arabic_flexible' => '🔄',
    'arabic_stemmed' => '🌳'
];

foreach ($analyzers as $analyzer => $icon) {
    try {
        $response = $client->indices()->analyze([
            'index' => 'pages_new_search',
            'body' => [
                'analyzer' => $analyzer,
                'text' => $testWord
            ]
        ]);
        
        $tokens = array_map(function($token) {
            return $token['token'];
        }, $response['tokens']);
        
        $result = implode(' ', $tokens);
        echo "$icon $analyzer: $result\n";
    } catch (Exception $e) {
        echo "❌ $analyzer: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// 3. التحقق من Search Types في Backend
echo "3️⃣ التحقق من Search Types في Backend:\n";
echo "─────────────────────────────────────────────────────\n";

$servicePath = __DIR__ . '/app/Services/UltraFastSearchService.php';
if (file_exists($servicePath)) {
    $content = file_get_contents($servicePath);
    
    // Check constants
    if (strpos($content, "const SEARCH_TYPE_EXACT = 'exact_match'") !== false) {
        echo "✅ SEARCH_TYPE_EXACT مُعرف\n";
    } else {
        echo "❌ SEARCH_TYPE_EXACT غير موجود\n";
    }
    
    if (strpos($content, "const SEARCH_TYPE_FLEXIBLE = 'flexible_match'") !== false) {
        echo "✅ SEARCH_TYPE_FLEXIBLE مُعرف\n";
    } else {
        echo "❌ SEARCH_TYPE_FLEXIBLE غير موجود\n";
    }
    
    if (strpos($content, "const SEARCH_TYPE_MORPHOLOGICAL = 'morphological'") !== false) {
        echo "✅ SEARCH_TYPE_MORPHOLOGICAL مُعرف\n";
    } else {
        echo "❌ SEARCH_TYPE_MORPHOLOGICAL غير موجود\n";
    }
    
    // Check methods
    if (strpos($content, 'protected function buildExactMatchQuery') !== false) {
        echo "✅ buildExactMatchQuery موجودة\n";
    } else {
        echo "❌ buildExactMatchQuery غير موجودة\n";
    }
    
    if (strpos($content, 'protected function buildFlexibleMatchQuery') !== false) {
        echo "✅ buildFlexibleMatchQuery موجودة\n";
    } else {
        echo "❌ buildFlexibleMatchQuery غير موجودة\n";
    }
    
    if (strpos($content, 'protected function buildMorphologicalQuery') !== false) {
        echo "✅ buildMorphologicalQuery موجودة\n";
    } else {
        echo "❌ buildMorphologicalQuery غير موجودة\n";
    }
    
    // Check switch case
    if (strpos($content, "case self::SEARCH_TYPE_EXACT:") !== false) {
        echo "✅ Switch case للـ EXACT موجود\n";
    } else {
        echo "❌ Switch case للـ EXACT غير موجود\n";
    }
} else {
    echo "❌ UltraFastSearchService.php غير موجود!\n";
}

echo "\n";

// 4. التحقق من Frontend
echo "4️⃣ التحقق من Frontend (Blade Template):\n";
echo "─────────────────────────────────────────────────────\n";

$bladePath = __DIR__ . '/resources/views/ultra-fast-search/views/ultra-fast.blade.php';
if (file_exists($bladePath)) {
    $content = file_get_contents($bladePath);
    
    // Check radio buttons
    if (strpos($content, 'value="exact_match"') !== false) {
        echo "✅ Radio button للـ exact_match موجود\n";
    } else {
        echo "❌ Radio button للـ exact_match غير موجود\n";
    }
    
    if (strpos($content, 'value="flexible_match"') !== false) {
        echo "✅ Radio button للـ flexible_match موجود\n";
    } else {
        echo "❌ Radio button للـ flexible_match غير موجود\n";
    }
    
    if (strpos($content, 'value="morphological"') !== false) {
        echo "✅ Radio button للـ morphological موجود\n";
    } else {
        echo "❌ Radio button للـ morphological غير موجود\n";
    }
    
    // Check JavaScript
    if (strpos($content, 'search_type: searchType') !== false) {
        echo "✅ JavaScript يرسل search_type\n";
    } else {
        echo "❌ JavaScript لا يرسل search_type\n";
    }
    
    // Check labels
    if (strpos($content, 'البحث المطابق') !== false) {
        echo "✅ تسمية 'البحث المطابق' موجودة\n";
    } else {
        echo "❌ تسمية 'البحث المطابق' غير موجودة\n";
    }
    
    if (strpos($content, 'البحث المرن') !== false) {
        echo "✅ تسمية 'البحث المرن' موجودة\n";
    } else {
        echo "❌ تسمية 'البحث المرن' غير موجودة\n";
    }
    
    if (strpos($content, 'البحث الصرفي') !== false) {
        echo "✅ تسمية 'البحث الصرفي' موجودة\n";
    } else {
        echo "❌ تسمية 'البحث الصرفي' غير موجودة\n";
    }
} else {
    echo "❌ ultra-fast.blade.php غير موجود!\n";
}

echo "\n";

// 5. اختبار عملي للبحث
echo "5️⃣ اختبار عملي للأنواع الثلاثة:\n";
echo "─────────────────────────────────────────────────────\n";

$searchTypes = [
    'exact_match' => ['🎯', 'content.exact'],
    'flexible_match' => ['🔄', 'content.flexible'],
    'morphological' => ['🌳', 'content.stemmed']
];

foreach ($searchTypes as $type => $info) {
    list($icon, $field) = $info;
    
    try {
        $query = ($type === 'exact_match') 
            ? ['match_phrase' => [$field => ['query' => 'الصلاة', 'slop' => 0]]]
            : ['match' => [$field => ['query' => 'الصلاة']]];
        
        $response = $client->search([
            'index' => 'pages_new_search',
            'body' => [
                'query' => $query,
                'size' => 0
            ]
        ]);
        
        $count = $response['hits']['total']['value'];
        echo "$icon $type: " . number_format($count) . " نتيجة\n";
    } catch (Exception $e) {
        echo "❌ $type: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// 6. التحقق من .env
echo "6️⃣ التحقق من ملف .env:\n";
echo "─────────────────────────────────────────────────────\n";

$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $content = file_get_contents($envPath);
    
    if (strpos($content, 'ELASTICSEARCH_HOST=http://145.223.98.97:9201') !== false) {
        echo "✅ ELASTICSEARCH_HOST صحيح\n";
    } else {
        echo "❌ ELASTICSEARCH_HOST غير صحيح\n";
    }
    
    if (strpos($content, 'ELASTICSEARCH_INDEX=') !== false) {
        preg_match('/ELASTICSEARCH_INDEX=(.+)/', $content, $matches);
        $index = trim($matches[1] ?? 'غير موجود');
        echo "ℹ️  ELASTICSEARCH_INDEX=$index\n";
        
        if ($index !== 'pages_new_search') {
            echo "⚠️  تحذير: يُفضل تغييره إلى pages_new_search\n";
        }
    } else {
        echo "❌ ELASTICSEARCH_INDEX غير موجود\n";
    }
} else {
    echo "❌ ملف .env غير موجود!\n";
}

echo "\n════════════════════════════════════════════════════════\n";
echo "   ✅ اكتمل الفحص!\n";
echo "════════════════════════════════════════════════════════\n";
