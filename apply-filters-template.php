<?php

// تطبيق Elasticsearch template مع حقول الفلاتر

$elasticsearchHost = 'http://145.223.98.97:9201';
$templateName = 'pages_new_search_template';

$template = [
    "index_patterns" => ["pages_new_search*"],
    "priority" => 100,
    "template" => [
        "settings" => [
            "number_of_shards" => 1,
            "number_of_replicas" => 0,
            "refresh_interval" => "1s",
            "max_result_window" => 100000,
            "analysis" => [
                "char_filter" => [
                    "arabic_normalization_char_filter" => [
                        "type" => "mapping",
                        "mappings" => [
                            "أ => ا",
                            "إ => ا",
                            "آ => ا",
                            "ؤ => و",
                            "ئ => ي",
                            "ة => ه",
                            "ى => ي"
                        ]
                    ]
                ],
                "filter" => [
                    "arabic_stop_words" => [
                        "type" => "stop",
                        "stopwords" => ["في", "من", "إلى", "على", "عن", "مع", "أو", "أم", "هل", "قد", "لم", "لن", "إن", "أن", "كان", "التي", "الذي"]
                    ],
                    "arabic_stemmer" => [
                        "type" => "stemmer",
                        "language" => "arabic"
                    ]
                ],
                "analyzer" => [
                    "arabic_exact" => [
                        "type" => "custom",
                        "tokenizer" => "keyword",
                        "filter" => ["lowercase"]
                    ],
                    "arabic_flexible" => [
                        "type" => "custom",
                        "tokenizer" => "standard",
                        "char_filter" => ["arabic_normalization_char_filter"],
                        "filter" => ["lowercase", "arabic_normalization", "arabic_stop_words"]
                    ],
                    "arabic_stemmed" => [
                        "type" => "custom",
                        "tokenizer" => "standard",
                        "char_filter" => ["arabic_normalization_char_filter"],
                        "filter" => ["lowercase", "arabic_normalization", "arabic_stemmer"]
                    ]
                ]
            ]
        ],
        "mappings" => [
            "properties" => [
                "id" => ["type" => "integer"],
                "content" => [
                    "type" => "text",
                    "analyzer" => "arabic_flexible",
                    "fields" => [
                        "exact" => ["type" => "text", "analyzer" => "arabic_exact"],
                        "flexible" => ["type" => "text", "analyzer" => "arabic_flexible"],
                        "stemmed" => ["type" => "text", "analyzer" => "arabic_stemmed"],
                        "keyword" => ["type" => "keyword", "ignore_above" => 256]
                    ]
                ],
                "page_number" => ["type" => "integer"],
                "book_id" => ["type" => "integer"],
                "book_title" => [
                    "type" => "text",
                    "analyzer" => "arabic_flexible",
                    "fields" => [
                        "exact" => ["type" => "keyword"],
                        "flexible" => ["type" => "text", "analyzer" => "arabic_flexible"]
                    ]
                ],
                "book_author" => [
                    "type" => "text",
                    "analyzer" => "arabic_flexible",
                    "fields" => [
                        "exact" => ["type" => "keyword"]
                    ]
                ],
                "author_names" => ["type" => "text", "analyzer" => "arabic_flexible"],
                "author_ids" => ["type" => "keyword"], // NEW: للفلترة بالمؤلفين
                "book_section" => ["type" => "keyword"],
                "book_section_id" => ["type" => "keyword"], // UPDATED: keyword بدلاً من integer للفلترة
                "book_category" => ["type" => "keyword"],
                "difficulty_level" => ["type" => "keyword"],
                "language" => ["type" => "keyword"],
                "content_length" => ["type" => "integer"],
                "search_boost" => ["type" => "float"],
                "last_modified" => ["type" => "date"],
                "created_date" => ["type" => "date"],
                "document_id" => ["type" => "keyword"]
            ]
        ]
    ]
];

echo "🔧 تطبيق Elasticsearch Template المحدث...\n";
echo "════════════════════════════════════════\n\n";

$url = "{$elasticsearchHost}/_index_template/{$templateName}";
$jsonData = json_encode($template, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

echo "URL: {$url}\n";
echo "Template Name: {$templateName}\n";
echo "Index Pattern: pages_new_search*\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ خطأ: {$error}\n";
    exit(1);
}

$result = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ تم تطبيق Template بنجاح!\n\n";
    echo "📋 التفاصيل:\n";
    echo "   - HTTP Code: {$httpCode}\n";
    echo "   - Index Pattern: pages_new_search*\n";
    echo "   - Priority: 100\n";
    echo "   - Analyzers: arabic_exact, arabic_flexible, arabic_stemmed\n";
    echo "   - Filter Fields: author_ids ✓, book_section_id ✓\n";
    
    if (isset($result['acknowledged']) && $result['acknowledged']) {
        echo "\n✨ Template acknowledged by Elasticsearch!\n";
    }
} else {
    echo "❌ فشل تطبيق Template (HTTP {$httpCode})\n\n";
    echo "الاستجابة:\n";
    print_r($result);
    exit(1);
}

echo "\n════════════════════════════════════════\n";
echo "📊 التحقق من Template...\n\n";

// التحقق من Template
$verifyUrl = "{$elasticsearchHost}/_index_template/{$templateName}";
$ch = curl_init($verifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $templateData = json_decode($response, true);
    $template = $templateData['index_templates'][0]['index_template'] ?? null;
    
    if ($template) {
        echo "✅ Template موجود ومُطبق\n";
        
        $mappings = $template['template']['mappings']['properties'] ?? [];
        
        echo "\n🔍 الحقول المتاحة:\n";
        $filterFields = ['author_ids', 'book_section_id', 'book_id'];
        foreach ($filterFields as $field) {
            if (isset($mappings[$field])) {
                $type = $mappings[$field]['type'] ?? 'unknown';
                echo "   ✅ {$field} ({$type})\n";
            } else {
                echo "   ❌ {$field} غير موجود!\n";
            }
        }
    }
} else {
    echo "⚠️ لا يمكن التحقق من Template (HTTP {$httpCode})\n";
}

echo "\n════════════════════════════════════════\n";
echo "✨ اكتمل التطبيق بنجاح!\n\n";

echo "📝 الخطوة التالية:\n";
echo "   1. حذف index القديم (اختياري):\n";
echo "      curl -X DELETE 'http://145.223.98.97:9201/pages_new_search'\n\n";
echo "   2. بدء Logstash لإعادة الفهرسة:\n";
echo "      cd logstash-setup\n";
echo "      docker-compose up -d\n\n";
echo "   3. مراقبة التقدم:\n";
echo "      php check-count.php\n\n";

echo "════════════════════════════════════════\n";
