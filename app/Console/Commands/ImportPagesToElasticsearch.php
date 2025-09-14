<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPagesToElasticsearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:import-pages {--chunk=500 : Number of pages to process in each chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all existing pages to Elasticsearch index';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Elasticsearch pages import...');
        
        $chunkSize = (int) $this->option('chunk');
        $totalPages = Page::count();
        
        if ($totalPages === 0) {
            $this->warn('No pages found to import.');
            return 0;
        }
        
        $this->info("Found {$totalPages} pages to import.");
        $this->info("Processing in chunks of {$chunkSize}...");
        
        $bar = $this->output->createProgressBar($totalPages);
        $bar->start();
        
        $processedCount = 0;
        $errorCount = 0;
        
        try {
            // Process pages in chunks to avoid memory issues
            Page::with(['book.authors', 'book.bookSection'])
                ->chunk($chunkSize, function ($pages) use ($bar, &$processedCount, &$errorCount) {
                    try {
                        // Make pages searchable in bulk
                        $pages->searchable();
                        $processedCount += $pages->count();
                        $bar->advance($pages->count());
                    } catch (\Exception $e) {
                        $errorCount += $pages->count();
                        $this->error("Error processing chunk: " . $e->getMessage());
                        $bar->advance($pages->count());
                    }
                });
                
            $bar->finish();
            $this->newLine(2);
            
            if ($errorCount > 0) {
                $this->warn("Import completed with errors. Processed: {$processedCount}, Errors: {$errorCount}");
                return 1;
            } else {
                $this->info("Successfully imported {$processedCount} pages to Elasticsearch!");
                return 0;
            }
            
        } catch (\Exception $e) {
            $bar->finish();
            $this->newLine();
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }
}
