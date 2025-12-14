<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class WarmCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up the cache with frequently accessed data';

    /**
     * The cache service instance.
     *
     * @var CacheService
     */
    protected $cacheService;

    /**
     * Create a new command instance.
     */
    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔥 Warming up the cache...');
        $this->newLine();

        $startTime = microtime(true);

        // Warm the cache
        $warmed = $this->cacheService->warmCache();

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        // Show results
        $this->info('✅ Cache warmed successfully!');
        $this->newLine();
        
        $this->table(
            ['Item', 'Status'],
            collect($warmed)->map(fn($item) => [$item, '✓ Cached'])->toArray()
        );

        $this->info("⏱️  Completed in {$duration} seconds");
        $this->newLine();

        // Show cache stats
        $stats = $this->cacheService->getCacheStats();
        
        $this->line("📊 Cache Statistics:");
        $this->line("   Total Keys: {$stats['total_keys']}");
        $this->line("   Cached: {$stats['cached_keys']}");
        $this->line("   Missing: {$stats['missing_keys']}");

        return Command::SUCCESS;
    }
}
