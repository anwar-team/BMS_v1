<?php

/**
 * اختبار شامل لمشاكل البحث والفلاتر
 * Context7 Enhanced Debugging Script
 */

require_once 'vendor/autoload.php';

// تحميل Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\UltraFastSearchService;
use Elasticsearch\ClientBuilder;

class SearchDebugger
{
    private $elasticsearch;
    private $searchService;
    
    public function __construct()
    {
        $this->elasticsearch = ClientBuilder::create()
            ->setHosts(['http://145.223.98.97:9201'])
            ->setSSLVerification(false)
            ->build();
            
        $this->searchService = new UltraFastSearchService();
    }
    
    public function testBasicConnectivity()
    {
        echo "=== اختبار الاتصال الأساسي ===\n";
        
        try {
            $info = $this->elasticsearch->info();
            echo "✅ الاتصال ناجح - Elasticsearch {$info['version']['number']}\n";
            return true;
        } catch (Exception $e) {
            echo "❌ فشل الاتصال: {$e->getMessage()}\n";
            return false;
        }
    }
    
    public function testIndexExists()
    {
        echo "\n=== اختبار وجود الفهارس ===\n";
        
        $indices = ['pages_new_search', 'pages', 'pages_test', 'pages_optimized'];
        $existingIndex = null;
        
        foreach ($indices as $index) {
            try {
                $exists = $this->elasticsearch->indices()->exists(['index' => $index]);
                echo $exists ? "✅ {$index} موجود\n" : "❌ {$index} غير موجود\n";
                
                if ($exists && !$existingIndex) {
                    $existingIndex = $index;
                }
            } catch (Exception $e) {
                echo "❌ خطأ في فحص {$index}: {$e->getMessage()}\n";
            }
        }
        
        return $existingIndex;
    }
    
    public function testFieldMapping($index)
    {
        echo "\n=== اختبار تركيب الحقول في {$index} ===\n";
        
        try {
            $mapping = $this->elasticsearch->indices()->getMapping(['index' => $index]);
            $properties = $mapping[$index]['mappings']['properties'] ?? [];
            
            $requiredFields = ['book_id', 'book_section_id', 'author_ids', 'content'];
            
            foreach ($requiredFields as $field) {
                if (isset($properties[$field])) {
                    $type = $properties[$field]['type'] ?? 'unknown';
                    echo "✅ {$field}: {$type}\n";
                } else {
                    echo "❌ {$field}: غير موجود\n";
                }
            }
            
            // Test author_ids specifically
            if (!isset($properties['author_ids'])) {
                echo "🔍 تأكيد: حقل author_ids غير موجود - الفلاتر المؤلف معطلة\n";
            }
            
        } catch (Exception $e) {
            echo "❌ خطأ في فحص الحقول: {$e->getMessage()}\n";
        }
    }
    
    public function testSearchTypes($index)
    {
        echo "\n=== اختبار أنواع البحث ===\n";
        
        $testQuery = "الإسلام";
        $searchTypes = [
            'exact_match' => 'البحث المطابق',
            'flexible_match' => 'البحث المرن', 
            'morphological' => 'البحث الصرفي'
        ];
        
        foreach ($searchTypes as $type => $desc) {
            try {
                $filters = ['search_type' => $type];
                $results = $this->searchService->search($testQuery, $filters, 1, 5);
                $count = count($results['results'] ?? []);
                
                echo "✅ {$desc} ({$type}): {$count} نتيجة\n";
                
                if ($count === 0) {
                    echo "⚠️  تحذير: {$desc} لم يعطِ نتائج\n";
                }
                
            } catch (Exception $e) {
                echo "❌ {$desc}: خطأ - {$e->getMessage()}\n";
            }
        }
    }
    
    public function testFilters($index)
    {
        echo "\n=== اختبار الفلاتر ===\n";
        
        // Test 1: Book filter
        try {
            $filters = ['book_id' => [1]];
            $results = $this->searchService->search('', $filters, 1, 5);
            $count = count($results['results'] ?? []);
            echo "✅ فلتر الكتاب (ID: 1): {$count} نتيجة\n";
        } catch (Exception $e) {
            echo "❌ فلتر الكتاب: {$e->getMessage()}\n";
        }
        
        // Test 2: Section filter
        try {
            $filters = ['section_id' => ['1']]; // Use string for keyword field
            $results = $this->searchService->search('', $filters, 1, 5);
            $count = count($results['results'] ?? []);
            echo "✅ فلتر القسم (ID: 1): {$count} نتيجة\n";
        } catch (Exception $e) {
            echo "❌ فلتر القسم: {$e->getMessage()}\n";
        }
        
        // Test 3: Author filter (should be disabled)
        try {
            $filters = ['author_id' => [1]];
            $results = $this->searchService->search('', $filters, 1, 5);
            $count = count($results['results'] ?? []);
            echo "⚠️  فلتر المؤلف (معطل): {$count} نتيجة\n";
        } catch (Exception $e) {
            echo "❌ فلتر المؤلف: {$e->getMessage()}\n";
        }
    }
    
    public function testCombinedSearchAndFilters($index)
    {
        echo "\n=== اختبار البحث + الفلاتر معاً ===\n";
        
        try {
            $query = "الله";
            $filters = [
                'book_id' => [1, 2],
                'search_type' => 'flexible_match'
            ];
            
            $results = $this->searchService->search($query, $filters, 1, 10);
            $count = count($results['results'] ?? []);
            
            echo "✅ البحث عن '{$query}' مع فلتر الكتب [1,2]: {$count} نتيجة\n";
            
            if ($count > 0) {
                $first = $results['results'][0];
                echo "   📖 أول نتيجة: {$first['book_title']} - صفحة {$first['page_number']}\n";
            }
            
        } catch (Exception $e) {
            echo "❌ البحث المختلط: {$e->getMessage()}\n";
        }
    }
    
    public function testDirectElasticsearchQuery($index)
    {
        echo "\n=== اختبار Elasticsearch مباشر ===\n";
        
        try {
            // Test basic bool query with filters (Context7 best practice)
            $params = [
                'index' => $index,
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['match' => ['content' => 'الله']]
                            ],
                            'filter' => [
                                ['terms' => ['book_id' => [1, 2]]]
                            ]
                        ]
                    ],
                    'size' => 5
                ]
            ];
            
            $response = $this->elasticsearch->search($params);
            $count = $response['hits']['total']['value'] ?? 0;
            
            echo "✅ استعلام مباشر: {$count} نتيجة\n";
            
            // Print first result details
            if (!empty($response['hits']['hits'])) {
                $hit = $response['hits']['hits'][0];
                echo "   📄 النتيجة الأولى: كتاب {$hit['_source']['book_id']}, صفحة {$hit['_source']['page_number']}\n";
            }
            
        } catch (Exception $e) {
            echo "❌ الاستعلام المباشر: {$e->getMessage()}\n";
        }
    }
    
    public function runAllTests()
    {
        echo "🔍 بدء الاختبار الشامل لنظام البحث والفلاتر\n";
        echo "التاريخ: " . date('Y-m-d H:i:s') . "\n";
        echo str_repeat('=', 60) . "\n";
        
        if (!$this->testBasicConnectivity()) {
            echo "❌ فشل الاتصال - توقف الاختبار\n";
            return;
        }
        
        $index = $this->testIndexExists();
        if (!$index) {
            echo "❌ لا توجد فهارس متاحة - توقف الاختبار\n";
            return;
        }
        
        echo "\n🎯 استخدام الفهرس: {$index}\n";
        
        $this->testFieldMapping($index);
        $this->testSearchTypes($index);
        $this->testFilters($index);
        $this->testCombinedSearchAndFilters($index);
        $this->testDirectElasticsearchQuery($index);
        
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "✅ انتهى الاختبار الشامل\n";
    }
}

// تشغيل الاختبار
$debugger = new SearchDebugger();
$debugger->runAllTests();