<?php

/**
 * اختبار شامل للحلول المطورة - Context7 Enhanced
 * يختبر جميع الإصلاحات المطبقة على نظام البحث والفلاتر
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\UltraFastSearchService;
use App\Http\Controllers\SearchController;
use Illuminate\Http\Request;

class ComprehensiveSearchTester
{
    private $searchService;
    private $searchController;
    
    public function __construct()
    {
        $this->searchService = new UltraFastSearchService();
        $this->searchController = new SearchController();
    }
    
    public function runAllTests()
    {
        echo "🔧 اختبار الحلول المطورة - Context7 Enhanced\n";
        echo "التاريخ والوقت: " . date('Y-m-d H:i:s') . "\n";
        echo str_repeat('=', 70) . "\n";
        
        $this->testInputValidation();
        $this->testAvailableFilters();
        $this->testRealDataFiltering();
        $this->testSearchWithRealFilters();
        $this->testErrorHandling();
        $this->testPerformanceMetrics();
        
        echo "\n" . str_repeat('=', 70) . "\n";
        echo "✅ انتهى الاختبار الشامل\n";
    }
    
    private function testInputValidation()
    {
        echo "\n=== 1. اختبار التحقق من صحة المدخلات (Input Validation) ===\n";
        
        // Test 1: Valid inputs
        try {
            $result = $this->searchService->search('الله', [], 1, 10);
            echo $result['error'] ? "❌ خطأ غير متوقع: {$result['error']}\n" : "✅ المدخلات الصحيحة تعمل\n";
        } catch (Exception $e) {
            echo "❌ خطأ في المدخلات الصحيحة: {$e->getMessage()}\n";
        }
        
        // Test 2: Invalid page number
        try {
            $result = $this->searchService->search('test', [], -1, 10);
            echo isset($result['error']) ? "✅ رقم الصفحة السالب مرفوض: {$result['error']}\n" : "❌ لم يتم رفض رقم الصفحة السالب\n";
        } catch (Exception $e) {
            echo "✅ خطأ متوقع لرقم الصفحة السالب\n";
        }
        
        // Test 3: Query too long
        try {
            $longQuery = str_repeat('طويل جداً ', 100); // 1000+ characters
            $result = $this->searchService->search($longQuery, [], 1, 10);
            echo isset($result['error']) ? "✅ الاستعلام الطويل مرفوض: {$result['error']}\n" : "❌ لم يتم رفض الاستعلام الطويل\n";
        } catch (Exception $e) {
            echo "✅ خطأ متوقع للاستعلام الطويل\n";
        }
        
        // Test 4: Invalid book_id
        try {
            $result = $this->searchService->search('test', ['book_id' => ['invalid']], 1, 10);
            echo isset($result['error']) ? "✅ book_id غير صحيح مرفوض: {$result['error']}\n" : "❌ لم يتم رفض book_id غير الصحيح\n";
        } catch (Exception $e) {
            echo "✅ خطأ متوقع لـ book_id غير صحيح\n";
        }
    }
    
    private function testAvailableFilters()
    {
        echo "\n=== 2. اختبار جلب الفلاتر المتاحة الحقيقية ===\n";
        
        // Test available books
        try {
            $books = $this->searchService->getAvailableFilters('books', 10);
            if (isset($books['error'])) {
                echo "❌ خطأ في جلب الكتب: {$books['error']}\n";
            } else {
                $count = count($books['books'] ?? []);
                echo "✅ تم جلب {$count} كتاب متاح\n";
                
                if ($count > 0) {
                    $first = $books['books'][0];
                    echo "   📖 مثال: {$first['name']} (ID: {$first['id']}, العدد: {$first['count']})\n";
                }
            }
        } catch (Exception $e) {
            echo "❌ خطأ في اختبار الكتب المتاحة: {$e->getMessage()}\n";
        }
        
        // Test available sections
        try {
            $sections = $this->searchService->getAvailableFilters('sections', 10);
            if (isset($sections['error'])) {
                echo "❌ خطأ في جلب الأقسام: {$sections['error']}\n";
            } else {
                $count = count($sections['sections'] ?? []);
                echo "✅ تم جلب {$count} قسم متاح\n";
                
                if ($count > 0) {
                    $first = $sections['sections'][0];
                    echo "   📂 مثال: {$first['name']} (ID: {$first['id']}, العدد: {$first['count']})\n";
                }
            }
        } catch (Exception $e) {
            echo "❌ خطأ في اختبار الأقسام المتاحة: {$e->getMessage()}\n";
        }
    }
    
    private function testRealDataFiltering()
    {
        echo "\n=== 3. اختبار الفلترة بالبيانات الحقيقية ===\n";
        
        // Get real book and section IDs first
        $books = $this->searchService->getAvailableFilters('books', 5);
        $sections = $this->searchService->getAvailableFilters('sections', 5);
        
        if (!empty($books['books'])) {
            $realBookId = $books['books'][0]['id'];
            echo "🎯 اختبار فلتر الكتاب بـ ID حقيقي: {$realBookId}\n";
            
            try {
                $result = $this->searchService->search('', ['book_id' => [$realBookId]], 1, 5);
                $count = count($result['results'] ?? []);
                $total = $result['total'] ?? 0;
                
                echo $count > 0 ? 
                    "✅ فلتر الكتاب يعمل: {$count} نتيجة معروضة من {$total} إجمالي\n" : 
                    "❌ فلتر الكتاب لا يعطي نتائج\n";
                    
                if ($count > 0) {
                    $first = $result['results'][0];
                    echo "   📄 عينة: {$first['book_title']} - صفحة {$first['page_number']}\n";
                }
                
            } catch (Exception $e) {
                echo "❌ خطأ في اختبار فلتر الكتاب: {$e->getMessage()}\n";
            }
        }
        
        if (!empty($sections['sections'])) {
            $realSectionId = $sections['sections'][0]['id'];
            echo "🎯 اختبار فلتر القسم بـ ID حقيقي: {$realSectionId}\n";
            
            try {
                $result = $this->searchService->search('', ['section_id' => [$realSectionId]], 1, 5);
                $count = count($result['results'] ?? []);
                $total = $result['total'] ?? 0;
                
                echo $count > 0 ? 
                    "✅ فلتر القسم يعمل: {$count} نتيجة معروضة من {$total} إجمالي\n" : 
                    "❌ فلتر القسم لا يعطي نتائج\n";
                    
                if ($count > 0) {
                    $first = $result['results'][0];
                    echo "   📄 عينة: {$first['book_title']} - صفحة {$first['page_number']}\n";
                }
                
            } catch (Exception $e) {
                echo "❌ خطأ في اختبار فلتر القسم: {$e->getMessage()}\n";
            }
        }
    }
    
    private function testSearchWithRealFilters()
    {
        echo "\n=== 4. اختبار البحث مع الفلاتر الحقيقية ===\n";
        
        $books = $this->searchService->getAvailableFilters('books', 3);
        
        if (!empty($books['books'])) {
            $realBookIds = array_slice(array_column($books['books'], 'id'), 0, 2);
            echo "🎯 اختبار البحث عن 'الله' مع فلاتر الكتب: [" . implode(', ', $realBookIds) . "]\n";
            
            try {
                $result = $this->searchService->search('الله', [
                    'book_id' => $realBookIds,
                    'search_type' => 'flexible_match'
                ], 1, 10);
                
                $count = count($result['results'] ?? []);
                $total = $result['total'] ?? 0;
                $metadata = $result['search_metadata'] ?? [];
                
                echo $count > 0 ? 
                    "✅ البحث المختلط يعمل: {$count} نتيجة معروضة من {$total} إجمالي\n" : 
                    "❌ البحث المختلط لا يعطي نتائج\n";
                
                if (!empty($metadata)) {
                    echo "   📊 معلومات إضافية:\n";
                    echo "      - الفهرس المستخدم: {$metadata['index_used']}\n";
                    echo "      - وقت الاستعلام: {$metadata['query_time']}ms\n";
                    echo "      - الفلاتر المطبقة: {$metadata['filters_applied']}\n";
                }
                
                if ($count > 0) {
                    $first = $result['results'][0];
                    echo "   📄 أول نتيجة: {$first['book_title']} - صفحة {$first['page_number']}\n";
                }
                
            } catch (Exception $e) {
                echo "❌ خطأ في البحث المختلط: {$e->getMessage()}\n";
            }
        }
    }
    
    private function testErrorHandling()
    {
        echo "\n=== 5. اختبار معالجة الأخطاء ===\n";
        
        // Test with non-existent book IDs
        try {
            $result = $this->searchService->search('test', ['book_id' => [999999, 888888]], 1, 5);
            $count = count($result['results'] ?? []);
            
            echo $count === 0 ? 
                "✅ الفلاتر غير الموجودة تُعيد نتائج فارغة بشكل صحيح\n" : 
                "❌ الفلاتر غير الموجودة تُعيد نتائج غير متوقعة: {$count}\n";
                
        } catch (Exception $e) {
            echo "✅ معالجة خطأ متوقعة للفلاتر غير الموجودة\n";
        }
        
        // Test empty query with no filters
        try {
            $result = $this->searchService->search('', [], 1, 5);
            echo isset($result['error']) ? 
                "✅ الاستعلام الفارغ بدون فلاتر مرفوض: {$result['error']}\n" : 
                "❌ لم يتم رفض الاستعلام الفارغ بدون فلاتر\n";
        } catch (Exception $e) {
            echo "✅ خطأ متوقع للاستعلام الفارغ\n";
        }
    }
    
    private function testPerformanceMetrics()
    {
        echo "\n=== 6. اختبار مقاييس الأداء ===\n";
        
        $queries = ['الله', 'الإسلام', 'القرآن'];
        $totalTime = 0;
        $successCount = 0;
        
        foreach ($queries as $query) {
            $startTime = microtime(true);
            
            try {
                $result = $this->searchService->search($query, [], 1, 10);
                $endTime = microtime(true);
                $queryTime = round(($endTime - $startTime) * 1000, 2);
                $totalTime += $queryTime;
                
                $count = count($result['results'] ?? []);
                if ($count > 0) {
                    $successCount++;
                    echo "✅ '{$query}': {$count} نتيجة في {$queryTime}ms\n";
                } else {
                    echo "⚠️ '{$query}': لا توجد نتائج في {$queryTime}ms\n";
                }
                
            } catch (Exception $e) {
                echo "❌ '{$query}': خطأ - {$e->getMessage()}\n";
            }
        }
        
        $avgTime = $totalTime / count($queries);
        echo "\n📊 إحصائيات الأداء:\n";
        echo "   - متوسط وقت الاستجابة: " . round($avgTime, 2) . "ms\n";
        echo "   - معدل النجاح: {$successCount}/" . count($queries) . "\n";
        echo "   - إجمالي الوقت: " . round($totalTime, 2) . "ms\n";
        
        if ($avgTime < 500) {
            echo "✅ الأداء ممتاز (أقل من 500ms)\n";
        } elseif ($avgTime < 1000) {
            echo "✅ الأداء جيد (أقل من 1s)\n";
        } else {
            echo "⚠️ الأداء يحتاج تحسين (أكثر من 1s)\n";
        }
    }
}

// تشغيل الاختبار
$tester = new ComprehensiveSearchTester();
$tester->runAllTests();