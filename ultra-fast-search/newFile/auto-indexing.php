<?php

// ملف أتمتة الفهرسة الشاملة مع مراقبة الأداء
// تشغيل: php auto-indexing.php

echo "📊 أتمتة الفهرسة الشاملة\n";
echo "========================\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Page;

class IndexingManager
{
    private $startTime;
    private $batchSize = 1000;
    private $totalPages = 0;
    private $processedPages = 0;
    private $errors = [];
    
    public function __construct($batchSize = 1000)
    {
        $this->batchSize = $batchSize;
        $this->startTime = microtime(true);
    }
    
    public function start()
    {
        echo "🚀 بدء عملية الفهرسة الشاملة\n";
        echo "============================\n\n";
        
        // 1. فحص النظام
        $this->systemCheck();
        
        // 2. إحصائيات قاعدة البيانات
        $this->getDatabaseStats();
        
        // 3. تنظيف الفهرس (اختياري)
        if ($this->askForIndexCleanup()) {
            $this->cleanupIndex();
        }
        
        // 4. بدء الفهرسة المتدرجة
        $this->startBatchIndexing();
        
        // 5. التحقق من النتائج
        $this->verifyIndexing();
        
        // 6. تقرير الأداء النهائي
        $this->generatePerformanceReport();
    }
    
    private function systemCheck()
    {
        echo "1️⃣ فحص النظام:\n";
        
        // فحص الذاكرة
        $memoryLimit = ini_get('memory_limit');
        echo "   - حد الذاكرة: {$memoryLimit}\n";
        
        // فحص Scout
        echo "   - Scout Driver: " . config('scout.driver') . "\n";
        
        // فحص نموذج Page
        $pageModel = new Page();
        $traits = class_uses($pageModel);
        echo "   - Page Searchable: " . (isset($traits['Laravel\Scout\Searchable']) ? '✅' : '❌') . "\n";
        
        echo "\n";
    }
    
    private function getDatabaseStats()
    {
        echo "2️⃣ إحصائيات قاعدة البيانات:\n";
        
        $this->totalPages = Page::count();
        echo "   - إجمالي الصفحات: " . number_format($this->totalPages) . "\n";
        
        $avgContentLength = Page::selectRaw('AVG(LENGTH(content)) as avg_length')->first()->avg_length;
        echo "   - متوسط طول المحتوى: " . number_format($avgContentLength) . " حرف\n";
        
        $booksCount = Page::distinct('book_id')->count('book_id');
        echo "   - عدد الكتب: " . number_format($booksCount) . "\n";
        
        $estimatedTime = ($this->totalPages / $this->batchSize) * 2; // تقدير دقيقتين لكل دفعة
        echo "   - الوقت المقدر: " . number_format($estimatedTime) . " دقيقة\n";
        
        echo "\n";
    }
    
    private function askForIndexCleanup()
    {
        echo "3️⃣ تنظيف الفهرس:\n";
        echo "   هل تريد حذف البيانات المفهرسة السابقة؟ (y/N): ";
        $handle = fopen("php://stdin", "r");
        $input = trim(fgets($handle));
        fclose($handle);
        
        return strtolower($input) === 'y';
    }
    
    private function cleanupIndex()
    {
        echo "   🧹 تنظيف الفهرس...\n";
        try {
            Page::removeAllFromSearch();
            echo "   ✅ تم تنظيف الفهرس\n\n";
        } catch (Exception $e) {
            echo "   ❌ خطأ في التنظيف: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function startBatchIndexing()
    {
        echo "4️⃣ بدء الفهرسة المتدرجة:\n";
        echo "   حجم الدفعة: " . number_format($this->batchSize) . "\n";
        echo "   عدد الدفعات: " . ceil($this->totalPages / $this->batchSize) . "\n\n";
        
        $batchNumber = 1;
        $offset = 0;
        
        while ($offset < $this->totalPages) {
            $batchStart = microtime(true);
            
            echo "   📦 دفعة {$batchNumber}: ";
            
            try {
                // جلب الدفعة مع العلاقات
                $pages = Page::with(['book', 'book.authors'])
                    ->offset($offset)
                    ->limit($this->batchSize)
                    ->get();
                
                if ($pages->count() === 0) {
                    break;
                }
                
                // فهرسة الدفعة
                $pages->searchable();
                
                $this->processedPages += $pages->count();
                $batchTime = microtime(true) - $batchStart;
                $pagesPerSecond = $pages->count() / $batchTime;
                
                echo "✅ " . $pages->count() . " صفحة";
                echo " (" . number_format($batchTime, 2) . "s";
                echo ", " . number_format($pagesPerSecond, 1) . " صفحة/ثانية)\n";
                
                // تحديث التقدم
                $progress = ($this->processedPages / $this->totalPages) * 100;
                echo "   📊 التقدم: " . number_format($progress, 1) . "%\n";
                
                // استراحة قصيرة لتجنب الضغط على النظام
                sleep(1);
                
            } catch (Exception $e) {
                echo "❌ خطأ: " . $e->getMessage() . "\n";
                $this->errors[] = [
                    'batch' => $batchNumber,
                    'offset' => $offset,
                    'error' => $e->getMessage(),
                    'time' => date('Y-m-d H:i:s')
                ];
            }
            
            $offset += $this->batchSize;
            $batchNumber++;
        }
        
        echo "\n";
    }
    
    private function verifyIndexing()
    {
        echo "5️⃣ التحقق من الفهرسة:\n";
        
        try {
            // اختبار بحث بسيط
            $searchResults = Page::search('الله')->take(5)->get();
            echo "   - اختبار البحث: ✅ (" . $searchResults->count() . " نتيجة)\n";
            
            // اختبار UltraFastSearchService
            $searchService = new App\Services\UltraFastSearchService();
            $results = $searchService->search('النبي', [], 1, 3);
            echo "   - البحث المتقدم: ✅ (" . ($results['total'] ?? 0) . " نتيجة)\n";
            
        } catch (Exception $e) {
            echo "   - خطأ في الاختبار: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function generatePerformanceReport()
    {
        $totalTime = microtime(true) - $this->startTime;
        $pagesPerSecond = $this->processedPages / $totalTime;
        
        echo "6️⃣ تقرير الأداء النهائي:\n";
        echo "======================\n";
        echo "   • إجمالي الصفحات: " . number_format($this->totalPages) . "\n";
        echo "   • صفحات مُعالجة: " . number_format($this->processedPages) . "\n";
        echo "   • الوقت الإجمالي: " . number_format($totalTime / 60, 2) . " دقيقة\n";
        echo "   • السرعة: " . number_format($pagesPerSecond, 1) . " صفحة/ثانية\n";
        echo "   • الأخطاء: " . count($this->errors) . "\n";
        
        if (!empty($this->errors)) {
            echo "\n❌ تفاصيل الأخطاء:\n";
            foreach ($this->errors as $error) {
                echo "   - دفعة {$error['batch']}: {$error['error']}\n";
            }
        }
        
        echo "\n🎉 اكتملت عملية الفهرسة!\n";
        echo "\n🔍 للاختبار:\n";
        echo "   • واجهة البحث: http://localhost:8000/search\n";
        echo "   • API البحث: http://localhost:8000/api/ultra-search?q=البحث\n";
    }
    
    // إضافة دالة لمراقبة الذاكرة
    private function getMemoryUsage()
    {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true)
        ];
    }
    
    // إضافة دالة لحفظ التقرير
    public function saveReport()
    {
        $report = [
            'indexing_date' => date('Y-m-d H:i:s'),
            'total_pages' => $this->totalPages,
            'processed_pages' => $this->processedPages,
            'batch_size' => $this->batchSize,
            'total_time' => microtime(true) - $this->startTime,
            'errors' => $this->errors,
            'memory_usage' => $this->getMemoryUsage()
        ];
        
        file_put_contents('indexing-report-' . date('Y-m-d-H-i-s') . '.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "\n📄 تم حفظ التقرير: indexing-report-" . date('Y-m-d-H-i-s') . ".json\n";
    }
}

// التحقق من المعاملات
$batchSize = 1000;
if (isset($argv[1]) && is_numeric($argv[1])) {
    $batchSize = (int)$argv[1];
}

echo "🎯 حجم الدفعة: " . number_format($batchSize) . "\n";
echo "💡 لتغيير حجم الدفعة: php auto-indexing.php 500\n\n";

// بدء العملية
$indexer = new IndexingManager($batchSize);
$indexer->start();
$indexer->saveReport();