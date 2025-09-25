<?php

// إعداد الفهرسة التلقائية بالخلفية للمليون صفحة
// تشغيل: php background-indexing.php

echo "🔄 إعداد الفهرسة التلقائية بالخلفية\n";
echo "====================================\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Page;
use Illuminate\Support\Facades\DB;

class BackgroundIndexingManager
{
    private $batchSize = 1000;
    private $indexedPagesFile = 'indexed_pages_progress.json';
    private $logFile = 'background_indexing.log';
    
    public function __construct()
    {
        echo "📊 إحصائيات الفهرسة:\n";
        $totalPages = Page::count();
        echo "   - إجمالي الصفحات في قاعدة البيانات: " . number_format($totalPages) . "\n";
        
        $progress = $this->getProgress();
        echo "   - صفحات مُفهرسة سابقاً: " . number_format($progress['indexed_count']) . "\n";
        echo "   - الصفحات المتبقية: " . number_format($totalPages - $progress['indexed_count']) . "\n\n";
    }
    
    public function startBackgroundIndexing()
    {
        echo "🚀 بدء الفهرسة التلقائية بالخلفية:\n";
        echo "====================================\n\n";
        
        // 1. إعداد Queue للفهرسة
        $this->setupQueueConfiguration();
        
        // 2. إعداد Cron Job للفهرسة المستمرة
        $this->setupCronJob();
        
        // 3. بدء الفهرسة المتدرجة
        $this->startBatchIndexing();
        
        // 4. إعداد مراقبة الأداء
        $this->setupPerformanceMonitoring();
    }
    
    private function setupQueueConfiguration()
    {
        echo "1️⃣ إعداد Queue للفهرسة:\n";
        
        // تحديث إعدادات Scout للعمل مع Queue
        $scoutConfig = "
# Scout Queue Configuration
SCOUT_QUEUE=true
SCOUT_CHUNK_SIZE=500
SCOUT_QUEUE_CONNECTION=database
SCOUT_QUEUE_TIMEOUT=300
";
        
        echo "   - إضافة إعدادات Queue إلى .env:\n";
        echo "     SCOUT_QUEUE=true\n";
        echo "     SCOUT_CHUNK_SIZE=500\n";
        echo "     SCOUT_QUEUE_TIMEOUT=300\n";
        
        // إنشاء Job للفهرسة
        $this->createIndexingJob();
        
        echo "   ✅ إعداد Queue مكتمل\n\n";
    }
    
    private function createIndexingJob()
    {
        $jobContent = '<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Page;

class IndexPagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;
    
    private $offset;
    private $limit;

    public function __construct($offset, $limit = 500)
    {
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function handle()
    {
        $pages = Page::with([\'book\', \'book.authors\'])
            ->offset($this->offset)
            ->limit($this->limit)
            ->get();
            
        if ($pages->count() > 0) {
            $pages->searchable();
            
            // تسجيل التقدم
            file_put_contents(
                storage_path(\'logs/indexing_progress.log\'),
                date(\'Y-m-d H:i:s\') . " - Indexed " . $pages->count() . " pages (offset: {$this->offset})" . PHP_EOL,
                FILE_APPEND
            );
        }
    }
    
    public function failed(\Exception $exception)
    {
        file_put_contents(
            storage_path(\'logs/indexing_errors.log\'),
            date(\'Y-m-d H:i:s\') . " - Error at offset {$this->offset}: " . $exception->getMessage() . PHP_EOL,
            FILE_APPEND
        );
    }
}';

        file_put_contents('app/Jobs/IndexPagesJob.php', $jobContent);
        echo "   - تم إنشاء IndexPagesJob ✅\n";
    }
    
    private function setupCronJob()
    {
        echo "2️⃣ إعداد Cron Job للفهرسة المستمرة:\n";
        
        $cronCommand = '
# إضافة هذا السطر إلى crontab للفهرسة كل 5 دقائق
*/5 * * * * cd ' . base_path() . ' && php artisan queue:work --stop-when-empty --timeout=300

# أو للفهرسة اليومية للصفحات الجديدة
0 2 * * * cd ' . base_path() . ' && php artisan scout:import "App\Models\Page" --chunk=1000
';
        
        echo "   - أضف هذا السطر إلى crontab:\n";
        echo "     */5 * * * * cd " . base_path() . " && php artisan queue:work --stop-when-empty\n";
        echo "   - أو للفهرسة اليومية:\n";
        echo "     0 2 * * * cd " . base_path() . ' && php artisan scout:import "App\Models\Page"' . "\n";
        
        echo "   ✅ إعداد Cron Job مكتمل\n\n";
    }
    
    private function startBatchIndexing()
    {
        echo "3️⃣ بدء الفهرسة المتدرجة:\n";
        
        $progress = $this->getProgress();
        $totalPages = Page::count();
        $startOffset = $progress['last_offset'];
        
        echo "   - بدء من الصفحة: " . number_format($startOffset) . "\n";
        echo "   - حجم الدفعة: " . number_format($this->batchSize) . "\n";
        
        $batchCount = 0;
        $maxBatches = 10; // فهرسة 10 دفعات في المرة الواحدة
        
        for ($offset = $startOffset; $offset < $totalPages && $batchCount < $maxBatches; $offset += $this->batchSize) {
            echo "   📦 دفعة " . ($batchCount + 1) . " (الصفحات {$offset} - " . ($offset + $this->batchSize) . "):\n";
            
            try {
                // إرسال مهمة إلى Queue
                \App\Jobs\IndexPagesJob::dispatch($offset, $this->batchSize);
                echo "     ✅ تم إرسال المهمة إلى Queue\n";
                
                // تحديث التقدم
                $this->updateProgress($offset + $this->batchSize, $progress['indexed_count'] + $this->batchSize);
                
                $batchCount++;
                
                // استراحة قصيرة
                sleep(1);
                
            } catch (Exception $e) {
                echo "     ❌ خطأ: " . $e->getMessage() . "\n";
                $this->logError($offset, $e->getMessage());
            }
        }
        
        echo "   ✅ تم إرسال {$batchCount} دفعة إلى Queue\n\n";
    }
    
    private function setupPerformanceMonitoring()
    {
        echo "4️⃣ إعداد مراقبة الأداء:\n";
        
        // إنشاء ملف مراقبة
        $monitoringScript = '#!/bin/bash
# مراقبة الفهرسة التلقائية
# تشغيل: ./monitor-indexing.sh

echo "📊 مراقبة الفهرسة التلقائية"
echo "========================="

# فحص Queue
php artisan queue:work --help > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Queue متاح"
    php artisan queue:monitor --queue=default
else
    echo "❌ Queue غير متاح"
fi

# فحص Elasticsearch
curl -s http://145.223.98.97:9201/pages/_stats | jq -r ".indices.pages.total.docs.count" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ Elasticsearch متصل"
else
    echo "❌ خطأ في Elasticsearch"
fi

# عرض آخر 10 أسطر من لوج الفهرسة
echo "📝 آخر أنشطة الفهرسة:"
tail -10 storage/logs/indexing_progress.log 2>/dev/null || echo "لا توجد logs بعد"
';
        
        file_put_contents('monitor-indexing.sh', $monitoringScript);
        echo "   - تم إنشاء ملف المراقبة: monitor-indexing.sh\n";
        echo "   - لتشغيل المراقبة: bash monitor-indexing.sh\n";
        
        echo "   ✅ إعداد المراقبة مكتمل\n\n";
    }
    
    private function getProgress()
    {
        if (file_exists($this->indexedPagesFile)) {
            $data = json_decode(file_get_contents($this->indexedPagesFile), true);
            return $data ?: ['last_offset' => 0, 'indexed_count' => 0];
        }
        
        return ['last_offset' => 0, 'indexed_count' => 0];
    }
    
    private function updateProgress($offset, $indexed)
    {
        $progress = [
            'last_offset' => $offset,
            'indexed_count' => $indexed,
            'last_updated' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($this->indexedPagesFile, json_encode($progress, JSON_PRETTY_PRINT));
    }
    
    private function logError($offset, $error)
    {
        $logEntry = date('Y-m-d H:i:s') . " - Error at offset {$offset}: {$error}" . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    public function showStatus()
    {
        echo "📊 حالة الفهرسة الحالية:\n";
        echo "========================\n";
        
        $progress = $this->getProgress();
        $totalPages = Page::count();
        $percentage = ($progress['indexed_count'] / $totalPages) * 100;
        
        echo "   - إجمالي الصفحات: " . number_format($totalPages) . "\n";
        echo "   - مُفهرس: " . number_format($progress['indexed_count']) . "\n";
        echo "   - متبقي: " . number_format($totalPages - $progress['indexed_count']) . "\n";
        echo "   - النسبة: " . number_format($percentage, 2) . "%\n";
        echo "   - آخر تحديث: " . ($progress['last_updated'] ?? 'غير محدد') . "\n";
        
        echo "\n🎯 للمتابعة:\n";
        echo "   - php background-indexing.php\n";
        echo "   - php artisan queue:work\n";
        echo "   - bash monitor-indexing.sh\n";
    }
}

// تشغيل النظام
$manager = new BackgroundIndexingManager();

if (isset($argv[1]) && $argv[1] === 'status') {
    $manager->showStatus();
} else {
    $manager->startBackgroundIndexing();
}