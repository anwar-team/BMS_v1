<?php
/**
 * تحديث Elasticsearch Template لإصلاح مشكلة النصوص الطويلة
 * 
 * المشكلة: content.exact يرفض النصوص أكبر من 32766 بايت
 * الحل: إضافة ignore_above: 32000
 */

$elasticsearchUrl = "http://145.223.98.97:9201";
$templateName = "pages_search_template";

// Template الجديد مع ignore_above
$template = [
    "index_patterns" => ["pages_new_search"],
    "template" => [
        "settings" => [
            "number_of_shards" => 3,
            "number_of_replicas" => 1,
            "analysis" => [
                "analyzer" => [
                    "arabic_exact" => [
                        "type" => "custom",
                        "tokenizer" => "keyword",
                        "filter" => ["lowercase", "arabic_normalization"]
                    ],
                    "arabic_flexible" => [
                        "type" => "custom",
                        "tokenizer" => "standard",
                        "filter" => ["lowercase", "arabic_normalization"]
                    ],
                    "arabic_stemmed" => [
                        "type" => "custom",
                        "tokenizer" => "standard",
                        "filter" => ["lowercase", "arabic_normalization", "arabic_stem"]
                    ]
                ]
            ]
        ],
        "mappings" => [
            "properties" => [
                "id" => ["type" => "long"],
                "page_number" => ["type" => "integer"],
                "content" => [
                    "type" => "text",
                    "analyzer" => "arabic_flexible",
                    "fields" => [
                        "exact" => [
                            "type" => "keyword",
                            "ignore_above" => 32000  // 🎯 FIX: تجاهل النصوص الأطول من 32000 بايت
                        ],
                        "stemmed" => [
                            "type" => "text",
                            "analyzer" => "arabic_stemmed"
                        ],
                        "flexible" => [
                            "type" => "text",
                            "analyzer" => "arabic_flexible"
                        ]
                    ]
                ],
                "content_normalized" => [
                    "type" => "text",
                    "analyzer" => "arabic_flexible"
                ],
                "book_id" => ["type" => "integer"],
                "book_title" => [
                    "type" => "text",
                    "analyzer" => "arabic_flexible"
                ],
                "book_author" => [
                    "type" => "text",
                    "analyzer" => "arabic_flexible"
                ],
                "book_section" => [
                    "type" => "text",
                    "analyzer" => "arabic_flexible"
                ],
                "book_section_id" => ["type" => "keyword"],
                "author_ids" => ["type" => "keyword"],
                "author_names" => [
                    "type" => "text",
                    "analyzer" => "arabic_flexible"
                ],
                "created_at" => ["type" => "date"],
                "updated_at" => ["type" => "date"],
                "type" => ["type" => "keyword"],
                "language" => ["type" => "keyword"],
                "difficulty_level" => ["type" => "keyword"]
            ]
        ]
    ]
];

echo "🔧 تحديث Template: $templateName\n";
echo str_repeat("=", 60) . "\n\n";

// تحديث Template
$ch = curl_init("$elasticsearchUrl/_index_template/$templateName");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($template));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode == 200 && isset($result['acknowledged']) && $result['acknowledged']) {
    echo "✅ Template تم تحديثه بنجاح!\n\n";
    
    echo "📋 التغييرات:\n";
    echo "  - أضفنا ignore_above: 32000 للحقل content.exact\n";
    echo "  - الصفحات الطويلة (أكبر من 32KB) سيتم فهرستها عادي\n";
    echo "  - لكن content.exact سيتجاهلها (لن يحدث ERROR)\n\n";
    
    echo "⚠️  ملاحظة مهمة:\n";
    echo "  - الصفحات الطويلة جداً لن تعمل معها البحث المطابق\n";
    echo "  - لكن ستعمل البحث العادي والمرن بدون مشاكل\n";
    echo "  - الحد الأقصى للبحث المطابق: ~32,000 حرف\n\n";
    
    echo "🔄 الخطوة التالية:\n";
    echo "  - Logstash سيستأنف الفهرسة تلقائياً\n";
    echo "  - سيتخطى الصفحات الطويلة بدون أخطاء\n";
    
} else {
    echo "❌ فشل تحديث Template!\n\n";
    echo "Response: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
