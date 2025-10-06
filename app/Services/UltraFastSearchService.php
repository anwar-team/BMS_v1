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
					'_source' => [
						'id', 'content', 'page_number', 'book_id', 
						'book_title', 'author_names', 'book_section_id'
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
            
			return $this->transformResults($response, $query, $page, $perPage);

		} catch (\Exception $e) {
			// Fallback to Scout if direct fails
			return $this->scoutFallback($query, $filters, $page, $perPage);
		}
	}

	/**
	 * Build exact match query - literal exact matching
	 */
	protected function buildExactMatchQuery(string $searchTerm): array
	{
		return [
			'match_phrase' => [
				'content.exact' => [
					'query' => $searchTerm,
					'slop' => 0
				]
			]
		];
	}

	/**
	 * Build flexible match query - allows prefixes without stemming
	 */
	protected function buildFlexibleMatchQuery(string $searchTerm): array
	{
		return [
			'match' => [
				'content.flexible' => [
					'query' => $searchTerm,
					'operator' => 'and'
				]
			]
		];
	}

	/**
	 * Build morphological query - root-based search with derivatives
	 */
	protected function buildMorphologicalQuery(string $searchTerm): array
	{
		return [
			'bool' => [
				'should' => [
					[
						'match' => [
							'content.stemmed' => [
								'query' => $searchTerm,
								'boost' => 2.0
							]
						]
					],
					[
						'match' => [
							'content.flexible' => [
								'query' => $searchTerm,
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
            
			switch ($searchType) {
				case self::SEARCH_TYPE_EXACT:
					$boolQuery['bool']['must'][] = $this->buildExactMatchQuery($query);
					break;

				case self::SEARCH_TYPE_MORPHOLOGICAL:
					$boolQuery['bool']['must'][] = $this->buildMorphologicalQuery($query);
					break;

				case self::SEARCH_TYPE_FLEXIBLE:
				default:
					$boolQuery['bool']['must'][] = $this->buildFlexibleMatchQuery($query);
					break;
			}
		}

		// Keep old search_mode for backward compatibility
		$searchMode = $filters['search_mode'] ?? null;
		if ($searchMode && !isset($filters['search_type'])) {
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
		} else {
			$boolQuery['bool']['must'][] = ['match_all' => new \stdClass()];
		}

		// Add existing filters
		if (!empty($filters['author_id'])) {
			$boolQuery['bool']['filter'][] = [
				'term' => ['author_ids' => $filters['author_id']]
			];
		}

		if (!empty($filters['section_id'])) {
			$boolQuery['bool']['filter'][] = [
				'term' => ['book_section_id' => $filters['section_id']]
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
	 * Transform Elasticsearch results
	 */
	protected function transformResults(array $response, string $query, int $page = 1, int $perPage = 15): array
	{
		$hits = $response['hits']['hits'] ?? [];
		$total = $response['hits']['total']['value'] ?? 0;

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

		return [
			'results' => $results,
			'total' => $total,
			'current_page' => $page,
			'per_page' => $perPage,
			'last_page' => max(1, ceil($total / $perPage)),
		];
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