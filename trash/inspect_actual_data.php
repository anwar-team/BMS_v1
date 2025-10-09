<?php

/**
 * فحص البيانات الفعلية الموجودة في Elasticsearch
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Elasticsearch\ClientBuilder;

$elasticsearch = ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->setSSLVerification(false)
    ->build();

echo "🔍 فحص البيانات الفعلية في pages_new_search\n";
echo str_repeat('=', 60) . "\n";

try {
    // 1. Get sample documents
    echo "=== عينة من الوثائق ===\n";
    $sampleParams = [
        'index' => 'pages_new_search',
        'body' => [
            'query' => ['match_all' => new stdClass()],
            'size' => 3,
            '_source' => ['book_id', 'book_section_id', 'author_ids', 'book_title', 'page_number']
        ]
    ];
    
    $sampleResponse = $elasticsearch->search($sampleParams);
    
    foreach ($sampleResponse['hits']['hits'] as $i => $hit) {
        $source = $hit['_source'];
        echo "📄 وثيقة " . ($i + 1) . ":\n";
        echo "   📖 book_id: " . ($source['book_id'] ?? 'N/A') . "\n";
        echo "   📂 book_section_id: " . ($source['book_section_id'] ?? 'N/A') . "\n";
        echo "   👤 author_ids: " . ($source['author_ids'] ?? 'N/A') . "\n";
        echo "   📚 book_title: " . ($source['book_title'] ?? 'N/A') . "\n";
        echo "   📄 page_number: " . ($source['page_number'] ?? 'N/A') . "\n";
        echo "\n";
    }
    
    // 2. Get aggregations to see what book_ids actually exist
    echo "=== أعلى 10 كتب من حيث عدد الصفحات ===\n";
    $bookAggParams = [
        'index' => 'pages_new_search',
        'body' => [
            'query' => ['match_all' => new stdClass()],
            'aggs' => [
                'top_books' => [
                    'terms' => [
                        'field' => 'book_id',
                        'size' => 10,
                        'order' => ['_count' => 'desc']
                    ]
                ]
            ],
            'size' => 0 // No hits, only aggregations
        ]
    ];
    
    $bookAggResponse = $elasticsearch->search($bookAggParams);
    
    if (isset($bookAggResponse['aggregations']['top_books']['buckets'])) {
        foreach ($bookAggResponse['aggregations']['top_books']['buckets'] as $bucket) {
            echo "📖 book_id: {$bucket['key']} - {$bucket['doc_count']} صفحة\n";
        }
    }
    
    // 3. Get section aggregations
    echo "\n=== أعلى 10 أقسام من حيث عدد الصفحات ===\n";
    $sectionAggParams = [
        'index' => 'pages_new_search',
        'body' => [
            'query' => ['match_all' => new stdClass()],
            'aggs' => [
                'top_sections' => [
                    'terms' => [
                        'field' => 'book_section_id',
                        'size' => 10,
                        'order' => ['_count' => 'desc']
                    ]
                ]
            ],
            'size' => 0
        ]
    ];
    
    $sectionAggResponse = $elasticsearch->search($sectionAggParams);
    
    if (isset($sectionAggResponse['aggregations']['top_sections']['buckets'])) {
        foreach ($sectionAggResponse['aggregations']['top_sections']['buckets'] as $bucket) {
            echo "📂 section_id: '{$bucket['key']}' - {$bucket['doc_count']} صفحة\n";
        }
    }
    
    // 4. Test filter with actual data
    $realBookId = $bookAggResponse['aggregations']['top_books']['buckets'][0]['key'] ?? null;
    $realSectionId = $sectionAggResponse['aggregations']['top_sections']['buckets'][0]['key'] ?? null;
    
    if ($realBookId) {
        echo "\n=== اختبار فلتر مع book_id حقيقي: {$realBookId} ===\n";
        $filterTestParams = [
            'index' => 'pages_new_search',
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['terms' => ['book_id' => [$realBookId]]]
                        ]
                    ]
                ],
                'size' => 3,
                '_source' => ['book_id', 'book_title', 'page_number']
            ]
        ];
        
        $filterTestResponse = $elasticsearch->search($filterTestParams);
        $count = $filterTestResponse['hits']['total']['value'];
        echo "✅ فلتر الكتاب {$realBookId}: {$count} نتيجة\n";
        
        if (!empty($filterTestResponse['hits']['hits'])) {
            $first = $filterTestResponse['hits']['hits'][0]['_source'];
            echo "   📄 عينة: {$first['book_title']} - صفحة {$first['page_number']}\n";
        }
    }
    
    if ($realSectionId) {
        echo "\n=== اختبار فلتر مع section_id حقيقي: '{$realSectionId}' ===\n";
        $sectionFilterParams = [
            'index' => 'pages_new_search',
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['terms' => ['book_section_id' => [$realSectionId]]]
                        ]
                    ]
                ],
                'size' => 3,
                '_source' => ['book_section_id', 'book_title', 'page_number']
            ]
        ];
        
        $sectionFilterResponse = $elasticsearch->search($sectionFilterParams);
        $count = $sectionFilterResponse['hits']['total']['value'];
        echo "✅ فلتر القسم '{$realSectionId}': {$count} نتيجة\n";
        
        if (!empty($sectionFilterResponse['hits']['hits'])) {
            $first = $sectionFilterResponse['hits']['hits'][0]['_source'];
            echo "   📄 عينة: {$first['book_title']} - صفحة {$first['page_number']}\n";
        }
    }
    
    // 5. Test combined search + filter
    if ($realBookId) {
        echo "\n=== اختبار البحث + فلتر معاً ===\n";
        $combinedParams = [
            'index' => 'pages_new_search',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['content' => 'الله']]
                        ],
                        'filter' => [
                            ['terms' => ['book_id' => [$realBookId]]]
                        ]
                    ]
                ],
                'size' => 3,
                '_source' => ['book_id', 'book_title', 'page_number', 'content']
            ]
        ];
        
        $combinedResponse = $elasticsearch->search($combinedParams);
        $count = $combinedResponse['hits']['total']['value'];
        echo "✅ البحث عن 'الله' في الكتاب {$realBookId}: {$count} نتيجة\n";
        
        if (!empty($combinedResponse['hits']['hits'])) {
            $first = $combinedResponse['hits']['hits'][0]['_source'];
            echo "   📄 عينة: {$first['book_title']} - صفحة {$first['page_number']}\n";
            echo "   💬 محتوى: " . substr($first['content'], 0, 100) . "...\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: {$e->getMessage()}\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ انتهى فحص البيانات\n";