<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;
use Elasticsearch\ClientBuilder;

/**
 * Command to optimize and re-index all pages for search
 */
class OptimizeSearchIndexCommand extends Command
{
    protected $signature = 'search:optimize {--force : Force reindex even if index exists}';
    protected $description = 'Optimize and re-index all pages for ultra-fast search';

    protected $elasticsearch;

    public function handle()
    {
        $this->info('🚀 Starting Search Index Optimization...');
        
        $this->initializeElasticsearch();
        
        try {
            // Step 1: Create optimized index
            $this->createOptimizedIndex();
            
            // Step 2: Index all pages
            $this->indexAllPages();
            
            // Step 3: Optimize index
            $this->optimizeIndex();
            
            $this->info('✅ Search optimization completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Optimization failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    protected function initializeElasticsearch()
    {
        $this->elasticsearch = ClientBuilder::create()
            ->setHosts([config('services.elasticsearch.host', 'http://145.223.98.97:9201')])
            ->setConnectionPool('\Elasticsearch\ConnectionPool\StaticNoPingConnectionPool')
            ->setSelector('\Elasticsearch\ConnectionPool\Selectors\RoundRobinSelector')
            ->setRetries(1)
            ->setSSLVerification(false)
            ->build();
    }

    protected function createOptimizedIndex()
    {
        $indexName = 'pages_optimized';
        
        $this->info("📊 Creating optimized index: {$indexName}");
        
        // Delete existing index if force option is used
        if ($this->option('force')) {
            try {
                $this->elasticsearch->indices()->delete(['index' => $indexName]);
                $this->info("🗑️ Deleted existing index");
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
        }
        
        // Check if index exists
        if ($this->elasticsearch->indices()->exists(['index' => $indexName])) {
            $this->info("ℹ️ Index already exists. Use --force to recreate.");
            return;
        }
        
        // Create index with optimized settings
        $params = [
            'index' => $indexName,
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'refresh_interval' => '30s',
                    'max_result_window' => 50000,
                    'analysis' => [
                        'analyzer' => [
                            'arabic_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'arabic_normalization',
                                    'arabic_stemmer'
                                ]
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'arabic_analyzer',
                            'search_analyzer' => 'arabic_analyzer',
                            'term_vector' => 'with_positions_offsets'
                        ],
                        'page_number' => ['type' => 'integer'],
                        'book_id' => ['type' => 'integer'],
                        'book_title' => [
                            'type' => 'text',
                            'analyzer' => 'arabic_analyzer'
                        ],
                        'author_names' => [
                            'type' => 'text',
                            'analyzer' => 'arabic_analyzer'
                        ],
                        'author_ids' => ['type' => 'integer'],
                        'book_section_id' => ['type' => 'integer'],
                        'created_at' => ['type' => 'date'],
                        'updated_at' => ['type' => 'date']
                    ]
                ]
            ]
        ];
        
        $this->elasticsearch->indices()->create($params);
        $this->info("✅ Optimized index created successfully");
    }

    protected function indexAllPages()
    {
        $this->info("📝 Starting bulk indexing of pages...");
        
        $totalPages = Page::count();
        $this->info("Total pages to index: {$totalPages}");
        
        $batchSize = 500;
        $currentBatch = 0;
        
        $progressBar = $this->output->createProgressBar($totalPages);
        $progressBar->start();
        
        Page::with(['book', 'book.authors'])
            ->chunk($batchSize, function ($pages) use (&$currentBatch, $progressBar) {
                $currentBatch++;
                
                $bulkParams = ['body' => []];
                
                foreach ($pages as $page) {
                    // Index action
                    $bulkParams['body'][] = [
                        'index' => [
                            '_index' => 'pages_optimized',
                            '_id' => $page->id
                        ]
                    ];
                    
                    // Document data
                    $bulkParams['body'][] = [
                        'id' => $page->id,
                        'content' => $this->prepareContent($page->content ?? ''),
                        'page_number' => $page->page_number ?? 1,
                        'book_id' => $page->book_id,
                        'book_title' => $page->book->title ?? 'غير محدد',
                        'author_names' => $page->book?->authors?->pluck('name')->implode(', ') ?? 'غير محدد',
                        'author_ids' => $page->book?->authors?->pluck('id')->toArray() ?? [],
                        'book_section_id' => $page->book_section_id,
                        'created_at' => $page->created_at?->toISOString(),
                        'updated_at' => $page->updated_at?->toISOString(),
                    ];
                }
                
                if (!empty($bulkParams['body'])) {
                    $response = $this->elasticsearch->bulk($bulkParams);
                    
                    if ($response['errors']) {
                        $this->warn("Some errors occurred in batch {$currentBatch}");
                    }
                }
                
                $progressBar->advance(count($pages));
            });
        
        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Bulk indexing completed");
    }

    protected function prepareContent(string $content): string
    {
        // Clean HTML tags
        $content = strip_tags($content);
        
        // Normalize Arabic text
        $content = preg_replace('/[^\p{Arabic}\p{N}\p{P}\s]/u', ' ', $content);
        
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        return trim($content);
    }

    protected function optimizeIndex()
    {
        $this->info("⚡ Optimizing index for performance...");
        
        try {
            // Force merge to optimize segments
            $this->elasticsearch->indices()->forcemerge([
                'index' => 'pages_optimized',
                'max_num_segments' => 1
            ]);
            
            // Refresh index
            $this->elasticsearch->indices()->refresh([
                'index' => 'pages_optimized'
            ]);
            
            $this->info("✅ Index optimization completed");
            
        } catch (\Exception $e) {
            $this->warn("⚠️ Optimization warning: " . $e->getMessage());
        }
    }
}