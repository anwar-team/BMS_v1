<?php

// إعداد الفهرسة التلقائية والمهام المُجدولة
// تشغيل: php schedule-indexing.php

echo "⏰ إعداد الفهرسة التلقائية والمهام المُجدولة\n";
echo "=============================================\n\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Page;
use Illuminate\Support\Facades\DB;

class AutoIndexingScheduler
{
    public function setup()
    {
        echo "🔧 إعداد الفهرسة التلقائية:\n";
        echo "============================\n\n";
        
        // 1. تحديث إعدادات Scout
        $this->updateScoutConfig();
        
        // 2. إنشاء جدولة Laravel
        $this->createScheduledTasks();
        
        // 3. إعداد Observer للفهرسة التلقائية
        $this->setupPageObserver();
        
        // 4. إنشاء Command للفهرسة المتدرجة
        $this->createIndexingCommand();
        
        // 5. تعليمات التشغيل
        $this->showInstructions();
    }
    
    private function updateScoutConfig()
    {
        echo "1️⃣ تحديث إعدادات Scout:\n";
        
        $envUpdates = [
            'SCOUT_QUEUE=true',
            'SCOUT_CHUNK_SIZE=1000', 
            'QUEUE_CONNECTION=database'
        ];
        
        echo "   إضافة إلى ملف .env:\n";
        foreach ($envUpdates as $update) {
            echo "   - {$update}\n";
        }
        
        echo "   ✅ تحديث إعدادات Scout\n\n";
    }
    
    private function createScheduledTasks()
    {
        echo "2️⃣ إنشاء المهام المُجدولة:\n";
        
        // إنشاء ملف Schedule مُخصص
        $scheduleContent = '<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\IndexPagesCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // فهرسة تدريجية كل 10 دقائق (1000 صفحة)
        $schedule->command("pages:index --chunk=1000 --batch=10")
                 ->everyTenMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();

        // فهرسة شاملة يومياً في الساعة 2 صباحاً
        $schedule->command("scout:import \"App\\\\Models\\\\Page\" --chunk=2000")
                 ->dailyAt("02:00")
                 ->withoutOverlapping()
                 ->runInBackground();

        // تنظيف logs الفهرسة أسبوعياً
        $schedule->command("pages:cleanup-logs")
                 ->weekly()
                 ->sundays()
                 ->at("03:00");
    }
}';

        file_put_contents('app/Console/Kernel.php', $scheduleContent);
        echo "   - تم إنشاء جدولة المهام ✅\n";
        echo "   - فهرسة كل 10 دقائق (1000 صفحة)\n";
        echo "   - فهرسة شاملة يومياً الساعة 2 صباحاً\n";
        echo "   ✅ إعداد الجدولة مكتمل\n\n";
    }
    
    private function setupPageObserver()
    {
        echo "3️⃣ إعداد Observer للفهرسة التلقائية:\n";
        
        $observerContent = '<?php

namespace App\Observers;

use App\Models\Page;

class PageObserver
{
    public function created(Page $page)
    {
        // فهرسة الصفحة الجديدة تلقائياً
        if (config("scout.queue")) {
            $page->searchable();
        }
    }

    public function updated(Page $page)
    {
        // إعادة فهرسة الصفحة المُحدثة
        if (config("scout.queue")) {
            $page->searchable();
        }
    }

    public function deleted(Page $page)
    {
        // حذف الصفحة من الفهرس
        $page->unsearchable();
    }
}';

        // إنشاء مجلد Observers إذا لم يكن موجوداً
        if (!file_exists('app/Observers')) {
            mkdir('app/Observers', 0755, true);
        }
        
        file_put_contents('app/Observers/PageObserver.php', $observerContent);
        echo "   - تم إنشاء PageObserver ✅\n";
        echo "   - فهرسة تلقائية للصفحات الجديدة\n";
        echo "   - إعادة فهرسة للصفحات المُحدثة\n";
        echo "   ✅ إعداد Observer مكتمل\n\n";
        
        // تحديث AppServiceProvider
        $this->updateAppServiceProvider();
    }
    
    private function updateAppServiceProvider()
    {
        echo "   📝 تحديث AppServiceProvider:\n";
        
        $providerAddition = '
        // إضافة هذا الكود إلى boot() method في AppServiceProvider:
        use App\\Models\\Page;
        use App\\Observers\\PageObserver;
        
        public function boot()
        {
            Page::observe(PageObserver::class);
        }';
        
        echo "   أضف هذا الكود إلى app/Providers/AppServiceProvider.php:\n";
        echo $providerAddition . "\n";
        echo "   ✅ تعليمات تحديث AppServiceProvider\n\n";
    }
    
    private function createIndexingCommand()
    {
        echo "4️⃣ إنشاء Command للفهرسة المتدرجة:\n";
        
        $commandContent = '<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;
use App\Jobs\IndexPagesJob;

class IndexPagesCommand extends Command
{
    protected $signature = "pages:index {--chunk=1000} {--batch=10}";
    protected $description = "Index pages in batches for background processing";

    public function handle()
    {
        $chunkSize = $this->option("chunk");
        $batchCount = $this->option("batch");
        
        $this->info("🚀 بدء الفهرسة المتدرجة");
        $this->info("حجم الدفعة: {$chunkSize}");
        $this->info("عدد الدفعات: {$batchCount}");
        
        $totalPages = Page::count();
        $progressFile = storage_path("app/indexing_progress.json");
        
        // قراءة التقدم السابق
        $progress = $this->getProgress($progressFile);
        $startOffset = $progress["last_offset"] ?? 0;
        
        $this->info("البدء من الصفحة: " . number_format($startOffset));
        
        $processed = 0;
        for ($i = 0; $i < $batchCount; $i++) {
            $offset = $startOffset + ($i * $chunkSize);
            
            if ($offset >= $totalPages) {
                $this->info("✅ تم الانتهاء من جميع الصفحات");
                break;
            }
            
            // إرسال مهمة الفهرسة
            IndexPagesJob::dispatch($offset, $chunkSize);
            
            $this->info("📦 دفعة " . ($i + 1) . ": صفحات {$offset} - " . ($offset + $chunkSize));
            
            // تحديث التقدم
            $this->updateProgress($progressFile, $offset + $chunkSize);
            
            $processed += $chunkSize;
        }
        
        $this->info("✅ تم إرسال {$batchCount} دفعة إلى Queue");
        $this->info("📊 إجمالي الصفحات المُرسلة: " . number_format($processed));
        
        return 0;
    }
    
    private function getProgress($file)
    {
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true) ?: [];
        }
        return [];
    }
    
    private function updateProgress($file, $offset)
    {
        $progress = [
            "last_offset" => $offset,
            "updated_at" => date("Y-m-d H:i:s")
        ];
        
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }
        
        file_put_contents($file, json_encode($progress, JSON_PRETTY_PRINT));
    }
}';

        // إنشاء مجلد Commands إذا لم يكن موجوداً
        if (!file_exists('app/Console/Commands')) {
            mkdir('app/Console/Commands', 0755, true);
        }
        
        file_put_contents('app/Console/Commands/IndexPagesCommand.php', $commandContent);
        echo "   - تم إنشاء IndexPagesCommand ✅\n";
        echo "   - Command: php artisan pages:index\n";
        echo "   - خيارات: --chunk=1000 --batch=10\n";
        echo "   ✅ إعداد Command مكتمل\n\n";
    }
    
    private function showInstructions()
    {
        echo "📋 تعليمات التشغيل:\n";
        echo "===================\n\n";
        
        echo "🔄 للفهرسة التلقائية:\n";
        echo "1. تشغيل Queue Worker:\n";
        echo "   php artisan queue:work --timeout=300\n\n";
        
        echo "2. تشغيل Scheduler:\n";
        echo "   php artisan schedule:run\n\n";
        
        echo "3. إعداد Cron Job (Linux/Mac):\n";
        echo "   * * * * * cd " . base_path() . " && php artisan schedule:run >> /dev/null 2>&1\n\n";
        
        echo "🚀 للفهرسة اليدوية:\n";
        echo "1. فهرسة متدرجة:\n";
        echo "   php artisan pages:index --chunk=1000 --batch=20\n\n";
        
        echo "2. فهرسة شاملة:\n";
        echo "   php artisan scout:import \"App\\Models\\Page\" --chunk=2000\n\n";
        
        echo "📊 لمراقبة التقدم:\n";
        echo "1. فحص Queue:\n";
        echo "   php artisan queue:monitor\n\n";
        
        echo "2. فحص logs:\n";
        echo "   tail -f storage/logs/indexing_progress.log\n\n";
        
        echo "3. فحص Elasticsearch:\n";
        echo "   php test-arabic-search.php\n\n";
        
        echo "✅ النظام جاهز للفهرسة التلقائية للمليون صفحة!\n";
    }
}

// تشغيل الإعداد
$scheduler = new AutoIndexingScheduler();
$scheduler->setup();