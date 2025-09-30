<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Page;
use Exception;

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
        $pages = Page::with(['book', 'book.authors'])
            ->offset($this->offset)
            ->limit($this->limit)
            ->get();
            
        if ($pages->count() > 0) {
            $pages->searchable();
            
            // تسجيل التقدم
            $logMessage = date('Y-m-d H:i:s') . " - Indexed " . $pages->count() . " pages (offset: {$this->offset})" . PHP_EOL;
            
            if (!file_exists(storage_path('logs'))) {
                mkdir(storage_path('logs'), 0755, true);
            }
            
            file_put_contents(
                storage_path('logs/indexing_progress.log'),
                $logMessage,
                FILE_APPEND
            );
        }
    }
    
    public function failed(Exception $exception)
    {
        if (!file_exists(storage_path('logs'))) {
            mkdir(storage_path('logs'), 0755, true);
        }
        
        $errorMessage = date('Y-m-d H:i:s') . " - Error at offset {$this->offset}: " . $exception->getMessage() . PHP_EOL;
        
        file_put_contents(
            storage_path('logs/indexing_errors.log'),
            $errorMessage,
            FILE_APPEND
        );
    }
}