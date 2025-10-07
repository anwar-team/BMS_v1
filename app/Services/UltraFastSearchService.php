<?php

namespace App\Services;

use App\Models\Page;
use Elasticsearch\ClientBuilder;

/**
 * Ultra-Fast Search Service
 * Optimized for Elasticsearch 7.17.3
 */
class UltraFastSearchService
{
	// Search type constants
	const SEARCH_TYPE_EXACT = 'exact_match';
	const SEARCH_TYPE_FLEXIBLE = 'flexible_match';
	const SEARCH_TYPE_MORPHOLOGICAL = 'morphological';

	protected $elasticsearch;

	public function __construct()
	{
		$this->elasticsearch = ClientBuilder::create()
			->setHosts([config('services.elasticsearch.host', 'http://145.223.98.97:9201')])
			->setConnectionPool('\\Elasticsearch\\ConnectionPool\\StaticNoPingConnectionPool')
			->setSelector('\\Elasticsearch\\ConnectionPool\\Selectors\\RoundRobinSelector')
			->setRetries(1)
			->setSSLVerification(false)
			->build();
	}

	/**
	 * Ultra-fast search with direct Elasticsearch queries
	 * Context7 Enhanced: Added aggregations for filter counts
	 */
	public function search(string $query, array $filters = [], int $page = 1, int $perPage = 15): array
	{
		try {
			// Use new search index first, then fallback to old ones
			$indices = ['pages_new_search', 'pages', 'pages_test', 'pages_optimized'];
			$indexToUse = null;
            
			foreach ($indices as $index) {
				try {
					if ($this->elasticsearch->indices()->exists(['index' => $index])) {
						$indexToUse = $index;
						break;
					}
				} catch (\Exception $e) {
					continue;
				}
			}
            
			if (!$indexToUse) {
				return $this->scoutFallback($query, $filters, $page, $perPage);
			}

			$params = [
				'index' => $indexToUse,
				'body' => [
					'query' => $this->buildOptimizedQuery($query, $filters),
					'highlight' => $this->buildHighlight(),
					'aggs' => $this->buildAggregations(), // Context7: Add aggregations for filter counts
					'_source' => [
						'id', 'content', 'page_number', 'book_id', 
						'book_title', 'author_names', 'author_ids',
						'book_section_id'
					],
					'from' => ($page - 1) * $perPage,
					'size' => $perPage,
					'sort' => ['_score'],
					'track_total_hits' => true, // إصلاح مشكلة الـ 10,000
				],
				'timeout' => '5s',
				'preference' => '_local',
			];

			$response = $this->elasticsearch->search($params);
            
			return $this->transformResults($response, $query, $page, $perPage, $filters);

		} catch (\Exception $e) {
			// Fallback to Scout if direct fails
			return $this->scoutFallback($query, $filters, $page, $perPage);
		}
	}

	/**
	 * Build exact match query - literal exact matching with word order
	 * FIXED: arabic_exact analyzer doesn't work properly, use content.keyword or flexible with strict matching
	 */
	protected function buildExactMatchQuery(string $searchTerm, string $wordOrder = 'consecutive'): array
	{
		// For exact + any_order: use match with operator=and on flexible field
		// (arabic_exact analyzer is broken - creates single token)
		if ($wordOrder === 'any_order') {
			return [
				'match' => [
					'content.flexible' => [
						'query' => $searchTerm,
						'operator' => 'and'
					]
				]
			];
		}
		
		// For exact + consecutive/same_paragraph: use match_phrase with slop
		// Using flexible field because exact analyzer is broken
		$slop = ($wordOrder === 'consecutive') ? 0 : 50;
		
		return [
			'match_phrase' => [
				'content.flexible' => [
					'query' => $searchTerm,
					'slop' => $slop
				]
			]
		];
	}

	/**
	 * Build flexible match query - allows prefixes without stemming
	 * Context7 Best Practice: Use match for any_order, match_phrase for consecutive/paragraph
	 */
	protected function buildFlexibleMatchQuery(string $searchTerm, string $wordOrder = 'any_order'): array
	{
		// If any_order, use match with operator AND
		if ($wordOrder === 'any_order') {
			return [
				'match' => [
					'content.flexible' => [
						'query' => $searchTerm,
						'operator' => 'and'
					]
				]
			];
		}
		
		// For consecutive: slop=0 (words must be adjacent)
		// For same_paragraph: slop=50 (words can have up to 50 words between them)
		$slop = ($wordOrder === 'consecutive') ? 0 : 50;
		
		return [
			'match_phrase' => [
				'content.flexible' => [
					'query' => $searchTerm,
					'slop' => $slop
				]
			]
		];
	}

	/**
	 * Get slop value based on word order
	 * Context7 Note: This function should NOT be called for any_order
	 */
	protected function getSlop(string $wordOrder): int
	{
		switch ($wordOrder) {
			case 'consecutive':
				return 0; // No words between (adjacent terms)
			case 'same_paragraph':
				return 50; // Allow up to 50 words between terms
			default:
				// Should never reach here for any_order
				// any_order uses match with operator=and instead
				return 0;
		}
	}

	/**
	 * Build morphological query - root-based search with derivatives
	 * Context7 Best Practice: Apply word_order logic to morphological search too
	 */
	protected function buildMorphologicalQuery(string $searchTerm, string $wordOrder = 'any_order'): array
	{
		// For any_order: use match with operator=and
		if ($wordOrder === 'any_order') {
			return [
				'bool' => [
					'should' => [
						[
							'match' => [
								'content.stemmed' => [
									'query' => $searchTerm,
									'boost' => 2.0,
									'operator' => 'and'
								]
							]
						],
						[
							'match' => [
								'content.flexible' => [
									'query' => $searchTerm,
									'boost' => 1.0,
									'operator' => 'and'
								]
							]
						]
					],
					'minimum_should_match' => 1
				]
			];
		}
		
		// For consecutive/same_paragraph: use match_phrase with appropriate slop
		$slop = ($wordOrder === 'consecutive') ? 0 : 50;
		
		return [
			'bool' => [
				'should' => [
					[
						'match_phrase' => [
							'content.stemmed' => [
								'query' => $searchTerm,
								'slop' => $slop,
								'boost' => 2.0
							]
						]
					],
					[
						'match_phrase' => [
							'content.flexible' => [
								'query' => $searchTerm,
								'slop' => $slop,
								'boost' => 1.0
							]
						]
					]
				],
				'minimum_should_match' => 1
			]
		];
	}

	/**
	 * Build optimized query for Arabic text with advanced search options
	 */
	protected function buildOptimizedQuery(string $query, array $filters): array
	{
		$boolQuery = [
			'bool' => [
				'must' => [],
				'filter' => [],
			]
		];

		if (!empty($query)) {
			// Get search type from filters (new system)
			$searchType = $filters['search_type'] ?? self::SEARCH_TYPE_FLEXIBLE;
			$wordOrder = $filters['word_order'] ?? 'any_order';
            
			switch ($searchType) {
				case self::SEARCH_TYPE_EXACT:
					$boolQuery['bool']['must'][] = $this->buildExactMatchQuery($query, $wordOrder);
					break;

				case self::SEARCH_TYPE_MORPHOLOGICAL:
					$boolQuery['bool']['must'][] = $this->buildMorphologicalQuery($query, $wordOrder);
					break;

				case self::SEARCH_TYPE_FLEXIBLE:
				default:
					$boolQuery['bool']['must'][] = $this->buildFlexibleMatchQuery($query, $wordOrder);
					break;
			}
		}

		// Keep old search_mode for backward compatibility
		// But ONLY if search_type is not set AND searchMode is provided
		$searchMode = $filters['search_mode'] ?? null;
		if ($searchMode && !isset($filters['search_type']) && !empty($query)) {
			$proximity = $filters['proximity'] ?? 'any_order'; // تعريف المتغير
			
			switch ($searchMode) {
				case 'exact_phrase':
					// مطابقة العبارة تماماً
					$boolQuery['bool']['must'][] = [
						'match_phrase' => [
							'content' => [
								'query' => $query,
								'slop' => 0
							]
						]
					];
					break;
                    
				case 'phrase_proximity':
					// مطابقة العبارة مع تباعد مسموح
					$slop = ($proximity === 'same_paragraph') ? 50 : 
						   (($proximity === 'consecutive') ? 2 : 10);
                    
					$boolQuery['bool']['must'][] = [
						'match_phrase' => [
							'content' => [
								'query' => $query,
								'slop' => $slop
							]
						]
					];
					break;
                    
				case 'all_words':
					// جميع الكلمات يجب أن تكون موجودة
					$boolQuery['bool']['must'][] = [
						'match' => [
							'content' => [
								'query' => $query,
								'operator' => 'and',
								'fuzziness' => 'AUTO'
							]
						]
					];
					break;
                    
				case 'any_word':
					// أي كلمة من الكلمات
					$boolQuery['bool']['must'][] = [
						'match' => [
							'content' => [
								'query' => $query,
								'operator' => 'or',
								'fuzziness' => 'AUTO'
							]
						]
					];
					break;
                    
				default: // flexible
					// البحث المرن (الافتراضي)
					$boolQuery['bool']['must'][] = [
						'multi_match' => [
							'query' => $query,
							'fields' => [
								'content^3',
								'book_title^2',
								'author_names^1.5',
							],
							'type' => 'best_fields',
							'fuzziness' => 'AUTO',
							'operator' => 'or',
							'minimum_should_match' => '70%',
						]
					];
					break;
			}
		}
		
		// If no query at all, return all documents (for filter-only searches)
		if (empty($query) && empty($boolQuery['bool']['must'])) {
			$boolQuery['bool']['must'][] = ['match_all' => new \stdClass()];
		}

		// Add filters - Context7 Best Practice: Use correct field types
		
		// Author filter - NOTE: author_ids field does NOT exist in indexed data
		// We need to filter by book_id and then join with books table to get author
		// For now, author filter is disabled until re-indexing
		if (!empty($filters['author_id'])) {
			\Illuminate\Support\Facades\Log::warning('Author filter requested but author_ids field does not exist in Elasticsearch index. Skipping author filter.');
			// TODO: Re-index with author_ids field OR use post-filter with database join
		}

		// Section filter - use keyword type (not integer!)
		if (!empty($filters['section_id'])) {
			$sectionIds = is_array($filters['section_id']) 
				? $filters['section_id'] 
				: [$filters['section_id']];
			
			// Convert to strings because book_section_id is keyword type
			$boolQuery['bool']['filter'][] = [
				'terms' => ['book_section_id' => array_map('strval', $sectionIds)]
			];
		}

		// Book filter - use integer type
		if (!empty($filters['book_id'])) {
			$bookIds = is_array($filters['book_id']) 
				? $filters['book_id'] 
				: [$filters['book_id']];
			
			// book_id is integer type
			$boolQuery['bool']['filter'][] = [
				'terms' => ['book_id' => array_map('intval', $bookIds)]
			];
		}

		return $boolQuery;
	}

	/**
	 * Build highlighting for Arabic text
	 */
	protected function buildHighlight(): array
	{
		return [
			'fields' => [
				'content' => [
					'fragment_size' => 120,
					'number_of_fragments' => 1,
					'pre_tags' => ['<mark class="highlight">'],
					'post_tags' => ['</mark>'],
				],
			],
			'encoder' => 'html',
		];
	}

	/**
	 * Build aggregations for filter counts
	 * Context7 Best Practice: Use terms aggregation for faceted search
	 */
	protected function buildAggregations(): array
	{
		return [
			// Author aggregation - get top authors with document counts
			'authors' => [
				'terms' => [
					'field' => 'author_ids',
					'size' => 100, // Top 100 authors
					'order' => ['_count' => 'desc']
				]
			],
			// Section aggregation - get all sections with document counts
			'sections' => [
				'terms' => [
					'field' => 'book_section_id',
					'size' => 50, // Top 50 sections
					'order' => ['_count' => 'desc']
				]
			],
			// Book aggregation - get top books with document counts
			'books' => [
				'terms' => [
					'field' => 'book_id',
					'size' => 100, // Top 100 books
					'order' => ['_count' => 'desc']
				]
			]
		];
	}

	/**
	 * Transform Elasticsearch results
	 * Context7 Enhanced: Added aggregations data for filter counts
	 */
	protected function transformResults(array $response, string $query, int $page = 1, int $perPage = 15, array $filters = []): array
	{
		$hits = $response['hits']['hits'] ?? [];
		$total = $response['hits']['total']['value'] ?? 0;
		$aggregations = $response['aggregations'] ?? [];

		$results = collect($hits)->map(function ($hit) use ($query) {
			$source = $hit['_source'] ?? [];
			$highlight = $hit['highlight']['content'][0] ?? null;

			return [
				'id' => $source['id'] ?? null,
				'page_number' => $source['page_number'] ?? null,
				'content' => $highlight ?: $this->formatContent($source['content'] ?? '', $query),
				'book_title' => $source['book_title'] ?? 'غير محدد',
				'author_name' => $source['author_names'] ?? 'غير محدد',
				'author_id' => !empty($source['author_ids']) ? $source['author_ids'][0] : null,
				'book_id' => $source['book_id'] ?? null,
				'book_section_id' => $source['book_section_id'] ?? null,
				'score' => $hit['_score'] ?? 0,
			];
		});

		// Process aggregations for filter metadata
		$filterMetadata = $this->processAggregations($aggregations);

		return [
			'results' => $results,
			'total' => $total,
			'current_page' => $page,
			'per_page' => $perPage,
			'last_page' => max(1, ceil($total / $perPage)),
			'filters' => $filterMetadata, // Context7: Add filter counts
		];
	}

	/**
	 * Process aggregations to extract filter metadata
	 * Context7 Best Practice: Transform aggregations for frontend consumption
	 */
	protected function processAggregations(array $aggregations): array
	{
		$metadata = [
			'authors' => [],
			'sections' => [],
			'books' => []
		];

		// Process author aggregation
		if (isset($aggregations['authors']['buckets'])) {
			foreach ($aggregations['authors']['buckets'] as $bucket) {
				$metadata['authors'][] = [
					'id' => $bucket['key'],
					'count' => $bucket['doc_count']
				];
			}
		}

		// Process section aggregation
		if (isset($aggregations['sections']['buckets'])) {
			foreach ($aggregations['sections']['buckets'] as $bucket) {
				$metadata['sections'][] = [
					'id' => $bucket['key'],
					'count' => $bucket['doc_count']
				];
			}
		}

		// Process book aggregation
		if (isset($aggregations['books']['buckets'])) {
			foreach ($aggregations['books']['buckets'] as $bucket) {
				$metadata['books'][] = [
					'id' => $bucket['key'],
					'count' => $bucket['doc_count']
				];
			}
		}

		return $metadata;
	}

	/**
	 * Format content with simple highlighting
	 */
	protected function formatContent(string $content, string $query): string
	{
		if (empty($query) || empty($content)) {
			return mb_substr($content, 0, 150) . '...';
		}

		// Find query position
		$position = mb_stripos($content, $query);
		if ($position !== false) {
			$start = max(0, $position - 60);
			$excerpt = mb_substr($content, $start, 120);
            
			// Highlight
			$excerpt = str_ireplace(
				$query,
				'<mark class="highlight">' . $query . '</mark>',
				$excerpt
			);
            
			return $excerpt . '...';
		}

		return mb_substr($content, 0, 120) . '...';
	}

	/**
	 * Scout fallback if direct Elasticsearch fails
	 */
	protected function scoutFallback(string $query, array $filters, int $page, int $perPage): array
	{
		try {
			// Try Laravel Scout first
			$builder = Page::search($query);
			
			if (!empty($filters['author_id'])) {
				$builder->where('author_ids', $filters['author_id']);
			}
			
			if (!empty($filters['section_id'])) {
				$builder->where('book_section_id', $filters['section_id']);
			}
			
			$results = $builder->paginate($perPage, 'page', $page);
			
			$items = collect($results->items())->map(function ($page) use ($query) {
				return [
					'id' => $page->id,
					'page_number' => $page->page_number,
					'content' => $this->formatContent($page->content ?? '', $query),
					'book_title' => $page->book->title ?? 'كتاب',
					'author_name' => $page->book && $page->book->authors ? $page->book->authors->pluck('full_name')->implode(', ') : 'مؤلف',
					'author_id' => $page->book && $page->book->authors && $page->book->authors->isNotEmpty() ? $page->book->authors->first()->id : null,
					'book_id' => $page->book_id,
					'book_section_id' => $page->book->book_section_id ?? null,
				];
			});
			
			return [
				'results' => $items,
				'total' => $results->total(),
				'current_page' => $results->currentPage(),
				'per_page' => $results->perPage(),
				'last_page' => $results->lastPage(),
			];
			
		} catch (\Exception $e) {
			// Final fallback to database query
			return $this->databaseFallback($query, $filters, $page, $perPage);
		}
	}

	/**
	 * Database fallback if both Elasticsearch and Scout fail
	 */
	protected function databaseFallback(string $query, array $filters, int $page, int $perPage): array
	{
		try {
			$queryBuilder = Page::with(['book', 'book.authors']);
			
			if (!empty($query)) {
				$queryBuilder->where('content', 'LIKE', "%{$query}%");
			}
			
			if (!empty($filters['author_id'])) {
				$queryBuilder->whereHas('book.authors', function ($q) use ($filters) {
					$q->where('authors.id', $filters['author_id']);
				});
			}
			
			if (!empty($filters['section_id'])) {
				$queryBuilder->whereHas('book', function ($q) use ($filters) {
					$q->where('book_section_id', $filters['section_id']);
				});
			}
			
			$results = $queryBuilder->paginate($perPage, ['*'], 'page', $page);
			
			$items = collect($results->items())->map(function ($page) use ($query) {
				return [
					'id' => $page->id,
					'page_number' => $page->page_number,
					'content' => $this->formatContent($page->content ?? '', $query),
					'book_title' => $page->book->title ?? 'كتاب',
					'author_name' => $page->book && $page->book->authors ? $page->book->authors->pluck('full_name')->implode(', ') : 'مؤلف',
					'book_id' => $page->book_id,
					'book_section_id' => $page->book->book_section_id ?? null,
				];
			});
			
			return [
				'results' => $items,
				'total' => $results->total(),
				'current_page' => $results->currentPage(),
				'per_page' => $results->perPage(),
				'last_page' => $results->lastPage(),
			];
			
		} catch (\Exception $e) {
			return [
				'results' => collect(),
				'total' => 0,
				'current_page' => 1,
				'per_page' => $perPage,
				'last_page' => 1,
				'error' => 'Search service temporarily unavailable: ' . $e->getMessage()
			];
		}
	}	/**
	 * Health check for search service
	 */
	public function healthCheck(): array
	{
		try {
			$response = $this->elasticsearch->ping();
			return [
				'status' => 'healthy',
				'elasticsearch' => $response ? 'connected' : 'disconnected',
				'timestamp' => now()->toISOString(),
			];
		} catch (\Exception $e) {
			return [
				'status' => 'unhealthy',
				'elasticsearch' => 'error',
				'error' => $e->getMessage(),
				'timestamp' => now()->toISOString(),
			];
		}
	}
}