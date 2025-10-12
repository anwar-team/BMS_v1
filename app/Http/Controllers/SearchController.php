<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Author;
use App\Models\BookSection;
use App\Services\UltraFastSearchService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Context7 Enhanced: Get available filter options with real data
     * 
     * @param UltraFastSearchService $searchService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableFilters(Request $request, UltraFastSearchService $searchService)
    {
        $validated = $request->validate([
            'type' => 'nullable|in:all,books,sections,authors',
            'limit' => 'nullable|integer|min:10|max:200'
        ]);

        $filterType = $validated['type'] ?? 'all';
        $limit = $validated['limit'] ?? 50;

        try {
            $filters = $searchService->getAvailableFilters($filterType, $limit);
            
            if (isset($filters['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $filters['error'],
                    'data' => []
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $filters,
                'metadata' => [
                    'type' => $filterType,
                    'limit' => $limit,
                    'generated_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get available filters', [
                'error' => $e->getMessage(),
                'type' => $filterType
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load filter options',
                'data' => []
            ], 500);
        }
    }

    /**
     * API endpoint for search (returns JSON) - Ultra Fast version
     * Context7 Enhanced: Proper validation and filter metadata in response
     * 
     * @param UltraFastSearchService $searchService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiSearch(Request $request, UltraFastSearchService $searchService)
    {
        $startTime = microtime(true);
        
        // Context7: Validate request parameters
        $validated = $request->validate([
            'q' => 'nullable|string|max:500',
            'author_id' => 'nullable', // Can be int or comma-separated string
            'section_id' => 'nullable', // Can be int or comma-separated string
            'book_id' => 'nullable', // Can be int or comma-separated string
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:50',
            'search_type' => 'nullable|in:exact_match,flexible_match,morphological',
            'word_order' => 'nullable|in:consecutive,same_paragraph,any_order',
            'search_mode' => 'nullable|string', // Backward compatibility
            'proximity' => 'nullable|string', // Backward compatibility
        ]);
        
        $query = trim($validated['q'] ?? '');
        $authorId = $validated['author_id'] ?? null;
        $sectionId = $validated['section_id'] ?? null;
        $bookId = $validated['book_id'] ?? null;
        $page = max(1, (int) ($validated['page'] ?? 1));
        $perPage = (int) ($validated['per_page'] ?? 15);
        
        // تحويل الفلاتر المتعددة إلى arrays إذا لزم الأمر
        if ($authorId && is_string($authorId) && strpos($authorId, ',') !== false) {
            $authorId = array_map('intval', array_filter(explode(',', $authorId)));
        }
        if ($sectionId && is_string($sectionId) && strpos($sectionId, ',') !== false) {
            $sectionId = array_map('intval', array_filter(explode(',', $sectionId)));
        }
        if ($bookId && is_string($bookId) && strpos($bookId, ',') !== false) {
            $bookId = array_map('intval', array_filter(explode(',', $bookId)));
        }
        
		// Context7: Enhanced validation for query and filters
		$hasValidFilters = false;
		if ($authorId) {
			$hasValidFilters = is_array($authorId) ? count($authorId) > 0 : !empty($authorId);
		}
		if ($sectionId && !$hasValidFilters) {
			$hasValidFilters = is_array($sectionId) ? count($sectionId) > 0 : !empty($sectionId);
		}
		if ($bookId && !$hasValidFilters) {
			$hasValidFilters = is_array($bookId) ? count($bookId) > 0 : !empty($bookId);
		}
		
		// Require either a query OR valid filters
		if (empty($query) && !$hasValidFilters) {
			return response()->json([
				'success' => false,
				'message' => 'يرجى توفير كلمة بحث أو اختيار فلتر صحيح',
				'data' => [],
				'pagination' => [
					'current_page' => 1,
					'last_page' => 1,
					'per_page' => $perPage,
					'total' => 0
				],
				'search_time' => 0,
				'error' => 'Missing query or valid filters'
			], 422); // Context7: Use 422 for validation errors
		}        try {
            $filters = array_filter([
                'author_id' => $authorId,
                'section_id' => $sectionId,
                'book_id' => $bookId, // Context7: Added book_id filter
                'search_type' => $validated['search_type'] ?? 'flexible_match',
                'word_order' => $validated['word_order'] ?? 'any_order',
                'search_mode' => $validated['search_mode'] ?? null, // Backward compatibility
                'proximity' => $validated['proximity'] ?? 'any_order', // Backward compatibility
            ]);

            $results = $searchService->search($query, $filters, $page, $perPage);
            
            $searchTime = round((microtime(true) - $startTime) * 1000, 2);

            // Context7: Enhanced response with filter metadata
            return response()->json([
                'success' => true,
                'data' => $results['results'],
                'pagination' => [
                    'current_page' => $results['current_page'] ?? $page,
                    'last_page' => $results['last_page'] ?? 1,
                    'per_page' => $results['per_page'] ?? $perPage,
                    'total' => $results['total'] ?? 0,
                    'from' => (($page - 1) * $perPage) + 1,
                    'to' => min($page * $perPage, $results['total'] ?? 0)
                ],
                'filters' => $results['filters'] ?? [], // Context7: Add filter counts
                'search_time' => $searchTime . 'ms'
            ]);

        } catch (\Exception $e) {
            $searchTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('Optimized search failed', [
                'error' => $e->getMessage(),
                'query' => $query,
                'filters' => $filters ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في البحث: ' . $e->getMessage(),
                'data' => [],
                'search_time' => $searchTime . 'ms'
            ], 500);
        }
    }

    /**
     * Get dynamic filter options
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilterOptions(Request $request)
    {
        $filterType = $request->get('type');
        
        try {
            $options = [];
            
            switch ($filterType) {
                case 'section':
                    $options = BookSection::select('id', 'name')
                        ->orderBy('name')
                        ->get()
                        ->map(function ($section) {
                            return [
                                'id' => $section->id,
                                'name' => $section->name
                            ];
                        })
                        ->toArray();
                    break;
                    
                case 'author':
                    $options = Author::select('id', 'full_name')
                        ->orderBy('full_name')
                        ->get()
                        ->map(function ($author) {
                            return [
                                'id' => $author->id,
                                'name' => $author->full_name
                            ];
                        })
                        ->toArray();
                    break;
                    
                case 'book':
                    $options = \App\Models\Book::select('id', 'title')
                        ->orderBy('title')
                        ->get()
                        ->map(function ($book) {
                            return [
                                'id' => $book->id,
                                'name' => $book->title
                            ];
                        })
                        ->toArray();
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'نوع التصفية غير مدعوم'
                    ], 400);
            }
            
            return response()->json([
                'success' => true,
                'data' => $options
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch filter options', [
                'error' => $e->getMessage(),
                'filter_type' => $filterType
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب خيارات التصفية'
            ], 500);
        }
    }

    /**
     * Show search form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('ultra-fast-search.views.ultra-fast');
    }
}