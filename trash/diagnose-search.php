<?php

/**
 * تحليل شامل لحالة البحث
 */

require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

echo "═══════════════════════════════════════════════════════\n";
echo "   تحليل شامل لحالة نظام البحث\n";
echo "═══════════════════════════════════════════════════════\n\n";

// 1. التحقق من Index Settings
echo "1️⃣ فحص Index Settings:\n";
echo "─────────────────────────────────────────────────────\n";

try {
    $settings = $client->indices()->getSettings(['index' => 'pages_new_search']);
    $analyzers = $settings['pages_new_search']['settings']['index']['analysis']['analyzer'] ?? [];
    
    echo "✅ Analyzers الموجودة:\n";
    foreach ($analyzers as $name => $config) {
        echo "   • $name\n";
    }
    
    if (count($analyzers) === 0) {
        echo "❌ لا توجد Analyzers مخصصة!\n";
    }
} catch (Exception $e) {
    echo "❌ خطأ في قراءة Settings: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. التحقق من Mapping
echo "2️⃣ فحص Mapping:\n";
echo "─────────────────────────────────────────────────────\n";

try {
    $mapping = $client->indices()->getMapping(['index' => 'pages_new_search']);
    $contentFields = $mapping['pages_new_search']['mappings']['properties']['content']['fields'] ?? [];
    
    if (!empty($contentFields)) {
        echo "✅ Multi-fields للـ content:\n";
        foreach ($contentFields as $fieldName => $fieldConfig) {
            $analyzer = $fieldConfig['analyzer'] ?? 'N/A';
            echo "   • content.$fieldName → analyzer: $analyzer\n";
        }
    } else {
        echo "❌ لا توجد Multi-fields للـ content!\n";
    }
} catch (Exception $e) {
    echo "❌ خطأ في قراءة Mapping: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. اختبار البحث بكل نوع
echo "3️⃣ اختبار البحث:\n";
echo "─────────────────────────────────────────────────────\n";

$testQuery = "الصلاة";

// Test 1: Exact Match
echo "🎯 البحث المطابق (exact_match):\n";
try {
    $params = [
        'index' => 'pages_new_search',
        'body' => [
            'query' => [
                'match_phrase' => [
                    'content.exact' => [
                        'query' => $testQuery,
                        'slop' => 0
                    ]
                ]
            ],
            'size' => 0
        ]
    ];
    
    $response = $client->search($params);
    $count = $response['hits']['total']['value'] ?? 0;
    echo "   النتائج: $count\n";
    
    if ($count === 0) {
        echo "   ⚠️  لا توجد نتائج - ربما المشكلة في analyzer\n";
    }
} catch (Exception $e) {
    echo "   ❌ خطأ: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Flexible Match
echo "🔄 البحث المرن (flexible_match):\n";
try {
    $params = [
        'index' => 'pages_new_search',
        'body' => [
            'query' => [
                'match' => [
                    'content.flexible' => [
                        'query' => $testQuery,
                        'operator' => 'and'
                    ]
                ]
            ],
            'size' => 0
        ]
    ];
    
    $response = $client->search($params);
    $count = $response['hits']['total']['value'] ?? 0;
    echo "   النتائج: $count\n";
    
    if ($count === 0) {
        echo "   ⚠️  لا توجد نتائج - ربما المشكلة في analyzer\n";
    }
} catch (Exception $e) {
    echo "   ❌ خطأ: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Morphological
echo "🌳 البحث الصرفي (morphological):\n";
try {
    $params = [
        'index' => 'pages_new_search',
        'body' => [
            'query' => [
                'bool' => [
                    'should' => [
                        [
                            'match' => [
                                'content.stemmed' => [
                                    'query' => $testQuery,
                                    'boost' => 2.0
                                ]
                            ]
                        ],
                        [
                            'match' => [
                                'content.flexible' => [
                                    'query' => $testQuery,
                                    'boost' => 1.0
                                ]
                            ]
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ],
            'size' => 0
        ]
    ];
    
    $response = $client->search($params);
    $count = $response['hits']['total']['value'] ?? 0;
    echo "   النتائج: $count\n";
    
    if ($count === 0) {
        echo "   ⚠️  لا توجد نتائج - ربما المشكلة في analyzer\n";
    }
} catch (Exception $e) {
    echo "   ❌ خطأ: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. اختبار Analyzer مباشرة
echo "4️⃣ اختبار Analyzers مباشرة:\n";
echo "─────────────────────────────────────────────────────\n";

$analyzersToTest = ['arabic_exact', 'arabic_flexible', 'arabic_stemmed'];

foreach ($analyzersToTest as $analyzer) {
    echo "$analyzer:\n";
    try {
        $response = $client->indices()->analyze([
            'index' => 'pages_new_search',
            'body' => [
                'analyzer' => $analyzer,
                'text' => $testQuery
            ]
        ]);
        
        $tokens = array_column($response['tokens'], 'token');
        echo "   Tokens: " . implode(', ', $tokens) . "\n";
    } catch (Exception $e) {
        echo "   ❌ Analyzer غير موجود: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// 5. التحقق من عدد المستندات
echo "5️⃣ إحصائيات Index:\n";
echo "─────────────────────────────────────────────────────\n";

try {
    $stats = $client->count(['index' => 'pages_new_search']);
    $count = $stats['count'] ?? 0;
    echo "✅ عدد المستندات: " . number_format($count) . "\n";
    
    if ($count === 0) {
        echo "❌ Index فارغ! يجب فهرسة البيانات\n";
    }
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "   انتهى التحليل\n";
echo "═══════════════════════════════════════════════════════\n";
