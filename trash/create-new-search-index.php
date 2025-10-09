<?php

require __DIR__.'/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

// إعدادات الاتصال
$elasticsearchHost = getenv('ELASTICSEARCH_HOST') ?: 'http://145.223.98.97:9201';
$indexName = 'pages_new_search';

echo "════════════════════════════════════════════════════════\n";
echo "   إنشاء Elasticsearch Index الجديد للبحث المتقدم\n";
echo "════════════════════════════════════════════════════════\n\n";

// إنشاء اتصال Elasticsearch
$client = ClientBuilder::create()
    ->setHosts([$elasticsearchHost])
    ->setConnectionPool('\\Elasticsearch\\ConnectionPool\\StaticNoPingConnectionPool')
    ->setRetries(1)
    ->setSSLVerification(false)
    ->build();

// التحقق من الاتصال
try {
    $ping = $client->ping();
    echo "✅ الاتصال بـ Elasticsearch ناجح\n";
    echo "   Host: $elasticsearchHost\n\n";
} catch (Exception $e) {
    echo "❌ فشل الاتصال بـ Elasticsearch\n";
    echo "   Error: " . $e->getMessage() . "\n";
    exit(1);
}

// حذف الـ index إذا كان موجود
if ($client->indices()->exists(['index' => $indexName])) {
    echo "⚠️  Index موجود مسبقاً: $indexName\n";
    echo "   جاري حذف Index القديم...\n";
    $client->indices()->delete(['index' => $indexName]);
    echo "✅ تم حذف Index القديم\n\n";
}

// إعداد الـ Index
echo "📝 جاري إنشاء Index جديد: $indexName\n\n";

$params = [
    'index' => $indexName,
    'body' => [
        'settings' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 1,
            'max_result_window' => 100000,
            'analysis' => [
                // Character Filters
                'char_filter' => [
                    'arabic_normalization_custom' => [
                        'type' => 'mapping',
                        'mappings' => [
                            'أ => ا',
                            'إ => ا',
                            'آ => ا',
                            'ة => ه',
                        ]
                    ]
                ],
                
                // Token Filters
                'filter' => [
                    'arabic_stop_without_stem' => [
                        'type' => 'stop',
                        'stopwords' => '_arabic_'
                    ],
                    'arabic_stop' => [
                        'type' => 'stop',
                        'stopwords' => '_arabic_'
                    ],
                    'arabic_stemmer' => [
                        'type' => 'stemmer',
                        'language' => 'arabic'
                    ]
                ],
                
                // Analyzers
                'analyzer' => [
                    // 1. البحث المطابق - بدون أي تعديل
                    'arabic_exact' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase']
                    ],
                    
                    // 2. البحث المرن - مع اللواصق فقط
                    'arabic_flexible' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'char_filter' => ['arabic_normalization_custom'],
                        'filter' => [
                            'lowercase',
                            'decimal_digit',
                            'arabic_normalization',
                            'arabic_stop_without_stem'
                        ]
                    ],
                    
                    // 3. البحث الصرفي - مع الجذور والمشتقات
                    'arabic_stemmed' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'char_filter' => ['arabic_normalization_custom'],
                        'filter' => [
                            'lowercase',
                            'decimal_digit',
                            'arabic_normalization',
                            'arabic_stop',
                            'arabic_stemmer'
                        ]
                    ]
                ]
            ]
        ],
        
        'mappings' => [
            'properties' => [
                'id' => [
                    'type' => 'long'
                ],
                'page_number' => [
                    'type' => 'integer'
                ],
                'book_id' => [
                    'type' => 'long'
                ],
                'book_title' => [
                    'type' => 'text',
                    'analyzer' => 'arabic_flexible',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword',
                            'ignore_above' => 256
                        ]
                    ]
                ],
                'author_names' => [
                    'type' => 'text',
                    'analyzer' => 'arabic_flexible'
                ],
                'author_ids' => [
                    'type' => 'long'
                ],
                'book_section_id' => [
                    'type' => 'long'
                ],
                
                // الحقل الرئيسي مع 3 أنواع analyzers
                'content' => [
                    'type' => 'text',
                    'analyzer' => 'arabic_flexible',
                    'fields' => [
                        // البحث المطابق
                        'exact' => [
                            'type' => 'text',
                            'analyzer' => 'arabic_exact'
                        ],
                        // البحث المرن
                        'flexible' => [
                            'type' => 'text',
                            'analyzer' => 'arabic_flexible'
                        ],
                        // البحث الصرفي
                        'stemmed' => [
                            'type' => 'text',
                            'analyzer' => 'arabic_stemmed'
                        ]
                    ]
                ]
            ]
        ]
    ]
];

try {
    $response = $client->indices()->create($params);
    
    echo "✅ تم إنشاء Index بنجاح!\n\n";
    echo "════════════════════════════════════════════════════════\n";
    echo "   معلومات Index\n";
    echo "════════════════════════════════════════════════════════\n";
    echo "📌 اسم Index: $indexName\n";
    echo "📌 عدد Shards: 1\n";
    echo "📌 عدد Replicas: 1\n";
    echo "📌 أنواع Analyzers: 3 (exact, flexible, stemmed)\n\n";
    
    echo "════════════════════════════════════════════════════════\n";
    echo "   الخطوات التالية\n";
    echo "════════════════════════════════════════════════════════\n";
    echo "1️⃣  اختبار Analyzers:\n";
    echo "    php test-analyzers.php\n\n";
    echo "2️⃣  فهرسة بيانات تجريبية (100 صفحة):\n";
    echo "    php index-sample-pages.php\n\n";
    echo "3️⃣  اختبار البحث:\n";
    echo "    php test-search-types.php\n\n";
    echo "4️⃣  فهرسة جميع البيانات:\n";
    echo "    php artisan scout:import \"App\\Models\\Page\"\n\n";
    
    // عرض Response
    if (isset($response['acknowledged']) && $response['acknowledged']) {
        echo "✅ Index acknowledged: true\n";
    }
    
} catch (Exception $e) {
    echo "❌ فشل إنشاء Index\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "\n";
    exit(1);
}

echo "════════════════════════════════════════════════════════\n";
echo "   اكتمل بنجاح! ✨\n";
echo "════════════════════════════════════════════════════════\n";
