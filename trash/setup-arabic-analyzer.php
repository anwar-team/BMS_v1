<?php

// ملف إعداد المحلل العربي المتقدم للبحث الفوري
// تشغيل: php setup-arabic-analyzer.php

echo "🔧 إعداد المحلل العربي المتقدم\n";
echo "===============================\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Elasticsearch\ClientBuilder;

try {
    // إنشاء عميل Elasticsearch
    $client = ClientBuilder::create()
        ->setHosts([config('services.elasticsearch.host')])
        ->build();
    
    echo "1️⃣ الاتصال بـ Elasticsearch: ✅\n";
    
    $indexName = config('services.elasticsearch.index', 'pages');
    
    // حذف الفهرس إذا كان موجوداً (اختياري)
    if ($client->indices()->exists(['index' => $indexName])) {
        echo "2️⃣ حذف الفهرس القديم...\n";
        $client->indices()->delete(['index' => $indexName]);
    }
    
    // إعداد المحلل العربي المتقدم
    $indexSettings = [
        'index' => $indexName,
        'body' => [
            'settings' => [
                'number_of_shards' => 2,
                'number_of_replicas' => 1,
                'analysis' => [
                    'char_filter' => [
                        'arabic_normalize' => [
                            'type' => 'mapping',
                            'mappings' => [
                                'ً=>',   // تنوين فتح
                                'ٌ=>',   // تنوين ضم
                                'ٍ=>',   // تنوين كسر
                                'َ=>',   // فتحة
                                'ُ=>',   // ضمة
                                'ِ=>',   // كسرة
                                'ّ=>',   // شدة
                                'ْ=>',   // سكون
                                'ٰ=>',   // ألف صغيرة
                                '۔=>.', // نقطة عربية
                                '؍=>',   // فاصلة عربية
                                'أ=>ا',  // همزة على الألف
                                'إ=>ا',  // همزة تحت الألف
                                'آ=>ا',  // مد بالألف
                                'ة=>ه',  // تاء مربوطة
                                'ى=>ي',  // ألف مقصورة
                                'ئ=>ي',  // همزة على الياء
                                'ؤ=>و',  // همزة على الواو
                            ]
                        ]
                    ],
                    'filter' => [
                        'arabic_stop' => [
                            'type' => 'stop',
                            'stopwords' => [
                                'في', 'من', 'إلى', 'على', 'عن', 'مع', 'كل', 'بعض', 'كان', 'لكن',
                                'أن', 'إن', 'كي', 'لا', 'ما', 'لم', 'لن', 'قد', 'كأن', 'لعل',
                                'هذا', 'هذه', 'ذلك', 'تلك', 'التي', 'الذي', 'اللذان', 'اللتان',
                                'هو', 'هي', 'هم', 'هن', 'أنت', 'أنتم', 'أنتن', 'أنا', 'نحن',
                                'الله', 'قال', 'يقول', 'تقول', 'فقال', 'وقال', 'أم', 'أو'
                            ]
                        ],
                        'arabic_stemmer' => [
                            'type' => 'stemmer',
                            'language' => 'arabic'
                        ],
                        'arabic_synonyms' => [
                            'type' => 'synonym',
                            'synonyms' => [
                                'رحمن,رحيم',
                                'نبي,رسول',
                                'صحابي,صاحب',
                                'اسلام,دين',
                                'قران,كتاب,مصحف',
                                'سنه,حديث'
                            ]
                        ]
                    ],
                    'analyzer' => [
                        'arabic_search' => [
                            'type' => 'custom',
                            'char_filter' => ['arabic_normalize'],
                            'tokenizer' => 'standard',
                            'filter' => [
                                'lowercase',
                                'arabic_stop',
                                'arabic_stemmer',
                                'arabic_synonyms'
                            ]
                        ],
                        'arabic_index' => [
                            'type' => 'custom',
                            'char_filter' => ['arabic_normalize'],
                            'tokenizer' => 'standard',
                            'filter' => [
                                'lowercase',
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
                    'content' => [
                        'type' => 'text',
                        'analyzer' => 'arabic_index',
                        'search_analyzer' => 'arabic_search',
                        'fields' => [
                            'exact' => [
                                'type' => 'keyword'
                            ],
                            'raw' => [
                                'type' => 'text',
                                'analyzer' => 'standard'
                            ]
                        ]
                    ],
                    'page_number' => [
                        'type' => 'integer'
                    ],
                    'book_id' => [
                        'type' => 'long'
                    ],
                    'book_title' => [
                        'type' => 'text',
                        'analyzer' => 'arabic_index',
                        'search_analyzer' => 'arabic_search'
                    ],
                    'book_description' => [
                        'type' => 'text',
                        'analyzer' => 'arabic_index',
                        'search_analyzer' => 'arabic_search'
                    ],
                    'authors' => [
                        'type' => 'text',
                        'analyzer' => 'arabic_index',
                        'search_analyzer' => 'arabic_search'
                    ],
                    'death_year' => [
                        'type' => 'integer'
                    ],
                    'categories' => [
                        'type' => 'keyword'
                    ],
                    'created_at' => [
                        'type' => 'date'
                    ],
                    'updated_at' => [
                        'type' => 'date'
                    ]
                ]
            ]
        ]
    ];
    
    echo "3️⃣ إنشاء الفهرس بالمحلل العربي المتقدم...\n";
    $response = $client->indices()->create($indexSettings);
    
    if ($response['acknowledged']) {
        echo "   ✅ تم إنشاء الفهرس بنجاح\n";
    } else {
        echo "   ❌ فشل في إنشاء الفهرس\n";
        exit(1);
    }
    
    // اختبار المحلل
    echo "\n4️⃣ اختبار المحلل العربي:\n";
    $testText = "بسم الله الرحمن الرحيم، قال رسول الله صلى الله عليه وسلم";
    
    $analyzeResponse = $client->indices()->analyze([
        'index' => $indexName,
        'body' => [
            'analyzer' => 'arabic_search',
            'text' => $testText
        ]
    ]);
    
    echo "   النص الأصلي: {$testText}\n";
    echo "   الرموز المُحللة: ";
    foreach ($analyzeResponse['tokens'] as $token) {
        echo $token['token'] . ' ';
    }
    echo "\n";
    
    echo "\n5️⃣ إعادة فهرسة البيانات:\n";
    echo "   يجب تشغيل الأمر التالي لإعادة الفهرسة:\n";
    echo "   php artisan scout:import \"App\\Models\\Page\"\n";
    
    echo "\n✅ تم إعداد المحلل العربي المتقدم بنجاح!\n";
    echo "\n📋 الميزات المُضافة:\n";
    echo "   • إزالة التشكيل والحركات\n";
    echo "   • توحيد الأحرف المتشابهة (أ/إ/آ => ا)\n";
    echo "   • إزالة كلمات الإيقاف العربية\n";
    echo "   • استخدام Stemmer عربي\n";
    echo "   • إضافة مرادفات إسلامية\n";
    echo "   • فهرسة متعددة المستويات\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "\n💡 تأكد من:\n";
    echo "   1. تشغيل Elasticsearch على العنوان المحدد\n";
    echo "   2. وجود إذن الكتابة على الفهرس\n";
    echo "   3. صحة إعدادات الاتصال\n";
}