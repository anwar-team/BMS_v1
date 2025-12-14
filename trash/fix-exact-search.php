<?php

/**
 * إصلاح البحث المطابق - يجب أن يكون حرفياً 100%
 */

require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

echo "═══════════════════════════════════════════════════════\n";
echo "   إصلاح البحث المطابق\n";
echo "═══════════════════════════════════════════════════════\n\n";

// حذف الـ Index القديم
echo "1️⃣ حذف Index القديم...\n";
try {
    $client->indices()->delete(['index' => 'pages_new_search']);
    echo "✅ تم حذف pages_new_search\n";
} catch (Exception $e) {
    echo "⚠️  Index غير موجود أو خطأ: " . $e->getMessage() . "\n";
}

echo "\n2️⃣ إنشاء Index جديد بـ analyzers صحيحة...\n";

// إنشاء Index جديد مع analyzers صحيحة
$indexSettings = [
    'index' => 'pages_new_search',
    'body' => [
        'settings' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
            'analysis' => [
                'char_filter' => [
                    'arabic_normalization' => [
                        'type' => 'mapping',
                        'mappings' => [
                            'آ => ا',
                            'أ => ا',
                            'إ => ا',
                            'ة => ه',
                            'ى => ي'
                        ]
                    ]
                ],
                'filter' => [
                    'arabic_stop_words' => [
                        'type' => 'stop',
                        'stopwords' => ['في', 'من', 'إلى', 'على', 'هذا', 'ذلك', 'التي', 'الذي']
                    ]
                ],
                'analyzer' => [
                    // 1. البحث المطابق - بدون أي تعديل نهائياً
                    'arabic_exact' => [
                        'type' => 'custom',
                        'tokenizer' => 'keyword',  // keyword بدلاً من standard
                        'filter' => ['lowercase']
                    ],
                    
                    // 2. البحث المرن - مع التطبيع فقط
                    'arabic_flexible' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'char_filter' => ['arabic_normalization'],
                        'filter' => [
                            'lowercase',
                            'arabic_normalization',
                            'arabic_stop_words'
                        ]
                    ],
                    
                    // 3. البحث الصرفي - مع stemmer
                    'arabic_stemmed' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'char_filter' => ['arabic_normalization'],
                        'filter' => [
                            'lowercase',
                            'arabic_normalization',
                            'arabic_stemmer'
                        ]
                    ]
                ]
            ]
        ],
        'mappings' => [
            'properties' => [
                'id' => ['type' => 'long'],
                'book_id' => ['type' => 'long'],
                'page_number' => ['type' => 'integer'],
                'content' => [
                    'type' => 'text',
                    'analyzer' => 'arabic_flexible',
                    'fields' => [
                        'exact' => [
                            'type' => 'text',
                            'analyzer' => 'arabic_exact'  // للبحث الحرفي
                        ],
                        'flexible' => [
                            'type' => 'text',
                            'analyzer' => 'arabic_flexible'  // للبحث المرن
                        ],
                        'stemmed' => [
                            'type' => 'text',
                            'analyzer' => 'arabic_stemmed'  // للبحث الصرفي
                        ]
                    ]
                ],
                'book_title' => ['type' => 'text', 'analyzer' => 'arabic_flexible'],
                'section_title' => ['type' => 'text', 'analyzer' => 'arabic_flexible'],
                'author_name' => ['type' => 'text', 'analyzer' => 'arabic_flexible'],
                'death_year' => ['type' => 'integer'],
                'created_at' => ['type' => 'date']
            ]
        ]
    ]
];

try {
    $response = $client->indices()->create($indexSettings);
    echo "✅ تم إنشاء Index بنجاح\n";
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3️⃣ اختبار Analyzers:\n";
echo "─────────────────────────────────────────────────────\n";

$testCases = [
    'الصلاة',
    'الصلاه',
    'صلى',
    'يصلي'
];

foreach ($testCases as $text) {
    echo "\n📝 اختبار: \"$text\"\n";
    
    // Test arabic_exact
    try {
        $response = $client->indices()->analyze([
            'index' => 'pages_new_search',
            'body' => [
                'analyzer' => 'arabic_exact',
                'text' => $text
            ]
        ]);
        $tokens = array_column($response['tokens'], 'token');
        echo "   🎯 exact:    " . implode(', ', $tokens) . "\n";
    } catch (Exception $e) {
        echo "   ❌ exact: " . $e->getMessage() . "\n";
    }
    
    // Test arabic_flexible
    try {
        $response = $client->indices()->analyze([
            'index' => 'pages_new_search',
            'body' => [
                'analyzer' => 'arabic_flexible',
                'text' => $text
            ]
        ]);
        $tokens = array_column($response['tokens'], 'token');
        echo "   🔄 flexible: " . implode(', ', $tokens) . "\n";
    } catch (Exception $e) {
        echo "   ❌ flexible: " . $e->getMessage() . "\n";
    }
    
    // Test arabic_stemmed
    try {
        $response = $client->indices()->analyze([
            'index' => 'pages_new_search',
            'body' => [
                'analyzer' => 'arabic_stemmed',
                'text' => $text
            ]
        ]);
        $tokens = array_column($response['tokens'], 'token');
        echo "   🌳 stemmed:  " . implode(', ', $tokens) . "\n";
    } catch (Exception $e) {
        echo "   ❌ stemmed: " . $e->getMessage() . "\n";
    }
}

echo "\n\n✅ Index جاهز الآن!\n";
echo "📌 الخطوة التالية: تشغيل الفهرسة\n";
echo "   php index-all-pages.php\n\n";

echo "═══════════════════════════════════════════════════════\n";
