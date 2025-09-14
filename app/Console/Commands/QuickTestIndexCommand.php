<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;
use Elasticsearch\ClientBuilder;

/**
 * Quick test command for optimized search - Limited indexing
 */
class QuickTestIndexCommand extends Command
{
    protected $signature = 'search:quick-test {--limit=1000 : Number of pages to index for testing}';
    protected $description = 'Quick test index with limited pages for ultra-fast search testing';

    protected $elasticsearch;

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $this->info("🚀 Starting Quick Search Test with {$limit} pages...");
        
        $this->initializeElasticsearch();
        
        try {
            // Step 1: Create test index
            $this->createTestIndex();
            
            // Step 2: Index limited pages
            $this->indexLimitedPages($limit);
            
            // Step 3: Test search
            $this->testSearch();
            
            $this->info('✅ Quick test completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
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

    protected function createTestIndex()
    {
        $indexName = 'pages_test';
        
        $this->info("📊 Creating test index: {$indexName}");
        
        // Delete existing test index
        try {
            $this->elasticsearch->indices()->delete(['index' => $indexName]);
            $this->info("🗑️ Deleted existing test index");
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Create test index with optimized settings
        $params = [
            'index' => $indexName,
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'refresh_interval' => '1s',
                    'analysis' => [
                        'analyzer' => [
                            'arabic_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'arabic_normalization']
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'arabic_analyzer'
                        ],
                        'page_number' => ['type' => 'integer'],
                        'book_id' => ['type' => 'integer'],
                        'book_title' => ['type' => 'text', 'analyzer' => 'arabic_analyzer'],
                        'author_names' => ['type' => 'text', 'analyzer' => 'arabic_analyzer']
                    ]
                ]
            ]
        ];
        
        $this->elasticsearch->indices()->create($params);
        $this->info("✅ Test index created successfully");
    }

    protected function indexLimitedPages($limit)
    {
        $this->info("📝 Indexing {$limit} pages for testing...");
        
        $pages = Page::with(['book', 'book.authors'])
            ->take($limit)
            ->get();
        
        $this->info("Found {$pages->count()} pages");
        
        $bulkParams = ['body' => []];
        
        foreach ($pages as $page) {
            $bulkParams['body'][] = [
                'index' => [
                    '_index' => 'pages_test',
                    '_id' => $page->id
                ]
            ];
            
            $bulkParams['body'][] = [
                'id' => $page->id,
                'content' => $this->prepareContent($page->content ?? ''),
                'page_number' => $page->page_number ?? 1,
                'book_id' => $page->book_id,
                'book_title' => $page->book->title ?? 'غير محدد',
                'author_names' => $page->book?->authors?->pluck('name')->implode(', ') ?? 'غير محدد'
            ];
        }
        
        if (!empty($bulkParams['body'])) {
            $response = $this->elasticsearch->bulk($bulkParams);
            
            if ($response['errors']) {
                $this->warn("Some indexing errors occurred");
            } else {
                $this->info("✅ All pages indexed successfully");
            }
        }
        
        // Refresh index
        $this->elasticsearch->indices()->refresh(['index' => 'pages_test']);
    }

    protected function prepareContent(string $content): string
    {
        $content = strip_tags($content);
        $content = preg_replace('/\s+/', ' ', $content);
        return trim($content);
    }

    protected function testSearch()
    {
        $this->info("🔍 Testing search functionality...");
        
        $testQueries = ['الله', 'الإسلام', 'النبي', 'القرآن'];
        
        foreach ($testQueries as $query) {
            try {
                $params = [
                    'index' => 'pages_test',
                    'body' => [
                        'query' => [
                            'multi_match' => [
                                'query' => $query,
                                'fields' => ['content', 'book_title', 'author_names']
                            ]
                        ],
                        'size' => 5
                    ]
                ];
                
                $startTime = microtime(true);
                $response = $this->elasticsearch->search($params);
                $searchTime = round((microtime(true) - $startTime) * 1000, 2);
                
                $total = $response['hits']['total']['value'] ?? 0;
                
                $this->info("Query: '{$query}' - Results: {$total} - Time: {$searchTime}ms");
                
            } catch (\Exception $e) {
                $this->error("Search failed for '{$query}': " . $e->getMessage());
            }
        }
    }
}