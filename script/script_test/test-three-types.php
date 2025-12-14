<?php

/**
 * اختبار سريع للأنواع الثلاثة
 */

require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

echo "═══════════════════════════════════════════════════════\n";
echo "   اختبار الأنواع الثلاثة للبحث\n";
echo "═══════════════════════════════════════════════════════\n\n";

// فهرسة بيانات تجريبية
echo "1️⃣ فهرسة بيانات تجريبية...\n";
echo "─────────────────────────────────────────────────────\n";

$testData = [
    ['id' => 1, 'content' => 'الصلاة عماد الدين'],
    ['id' => 2, 'content' => 'الصلاه في المسجد'],
    ['id' => 3, 'content' => 'صلى النبي في المسجد'],
    ['id' => 4, 'content' => 'يصلي المسلمون خمس مرات'],
    ['id' => 5, 'content' => 'مصلى العيد قريب'],
];

$indexed = 0;
foreach ($testData as $doc) {
    try {
        $client->index([
            'index' => 'pages_new_search',
            'id' => $doc['id'],
            'body' => [
                'id' => $doc['id'],
                'content' => $doc['content'],
                'book_id' => 1,
                'page_number' => $doc['id']
            ]
        ]);
        $indexed++;
        echo "✅ {$doc['content']}\n";
    } catch (Exception $e) {
        echo "❌ خطأ: " . $e->getMessage() . "\n";
    }
}

// Refresh للتأكد من ظهور البيانات
$client->indices()->refresh(['index' => 'pages_new_search']);

echo "\n✅ تم فهرسة $indexed مستند\n";

// الانتظار قليلاً
sleep(1);

echo "\n2️⃣ اختبار البحث:\n";
echo "─────────────────────────────────────────────────────\n\n";

// Test 1: البحث المطابق
echo "🎯 البحث المطابق - البحث عن: \"الصلاة\"\n";
echo "   (يجب أن يجد فقط: \"الصلاة عماد الدين\")\n";
try {
    $response = $client->search([
        'index' => 'pages_new_search',
        'body' => [
            'query' => [
                'match_phrase' => [
                    'content.exact' => [
                        'query' => 'الصلاة',
                        'slop' => 0
                    ]
                ]
            ]
        ]
    ]);
    
    $hits = $response['hits']['hits'];
    echo "   النتائج (" . count($hits) . "):\n";
    foreach ($hits as $hit) {
        echo "   • " . $hit['_source']['content'] . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ خطأ: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: البحث المرن
echo "🔄 البحث المرن - البحث عن: \"الصلاة\"\n";
echo "   (يجب أن يجد: \"الصلاة\" و \"الصلاه\")\n";
try {
    $response = $client->search([
        'index' => 'pages_new_search',
        'body' => [
            'query' => [
                'match' => [
                    'content.flexible' => [
                        'query' => 'الصلاة',
                        'operator' => 'and'
                    ]
                ]
            ]
        ]
    ]);
    
    $hits = $response['hits']['hits'];
    echo "   النتائج (" . count($hits) . "):\n";
    foreach ($hits as $hit) {
        echo "   • " . $hit['_source']['content'] . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ خطأ: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: البحث الصرفي
echo "🌳 البحث الصرفي - البحث عن: \"صلى\"\n";
echo "   (يجب أن يجد: صلى، الصلاة، الصلاه، يصلي، مصلى)\n";
try {
    $response = $client->search([
        'index' => 'pages_new_search',
        'body' => [
            'query' => [
                'bool' => [
                    'should' => [
                        [
                            'match' => [
                                'content.stemmed' => [
                                    'query' => 'صلى',
                                    'boost' => 2.0
                                ]
                            ]
                        ],
                        [
                            'match' => [
                                'content.flexible' => [
                                    'query' => 'صلى',
                                    'boost' => 1.0
                                ]
                            ]
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        ]
    ]);
    
    $hits = $response['hits']['hits'];
    echo "   النتائج (" . count($hits) . "):\n";
    foreach ($hits as $hit) {
        $score = round($hit['_score'], 2);
        echo "   • " . $hit['_source']['content'] . " (score: $score)\n";
    }
} catch (Exception $e) {
    echo "   ❌ خطأ: " . $e->getMessage() . "\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "   ✅ اكتمل الاختبار!\n";
echo "═══════════════════════════════════════════════════════\n";
