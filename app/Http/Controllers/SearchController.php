<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Author;
use App\Models\BookSection;
use App\Services\OptimizedSearchService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchController extends Controller
{
    /**
     * Perform search across pages with filters
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $authorId = $request->get('author_id');
        $sectionId = $request->get('section_id');
        $perPage = $request->get('per_page', 15);
        
        // Validate per_page parameter
        $perPage = min(max((int) $perPage, 5), 100);
        
        $results = collect();
        $totalResults = 0;
        
        if (!empty($query) || $authorId || $sectionId) {
            try {
                // Start with base search
                $searchBuilder = Page::search($query ?: '*');
                
                // Apply author filter if provided
                if ($authorId) {
                    $searchBuilder->where('author_ids', $authorId);
                }
                
                // Apply book section filter if provided
                if ($sectionId) {
                    $searchBuilder->where('book_section_id', $sectionId);
                }
                
                // Get paginated results
                $results = $searchBuilder->paginate($perPage);
                $totalResults = $results->total();
                
            } catch (\Exception $e) {
                // Fallback to database search if Elasticsearch fails
                $fallbackQuery = Page::with(['book.authors', 'book.bookSection'])
                    ->when($query, function ($q) use ($query) {
                        return $q->where('content', 'LIKE', "%{$query}%")
                                ->orWhereHas('book', function ($bookQuery) use ($query) {
                                    $bookQuery->where('title', 'LIKE', "%{$query}%");
                                })
                                ->orWhereHas('book.authors', function ($authorQuery) use ($query) {
                                    $authorQuery->where('full_name', 'LIKE', "%{$query}%");
                                });
                    })
                    ->when($authorId, function ($q) use ($authorId) {
                        return $q->whereHas('book.authors', function ($authorQuery) use ($authorId) {
                            $authorQuery->where('authors.id', $authorId);
                        });
                    })
                    ->when($sectionId, function ($q) use ($sectionId) {
                        return $q->whereHas('book', function ($bookQuery) use ($sectionId) {
                            $bookQuery->where('book_section_id', $sectionId);
                        });
                    });
                
                $results = $fallbackQuery->paginate($perPage);
                $totalResults = $results->total();
                
                // Log the error for debugging
                \Log::warning('Elasticsearch search failed, using database fallback', [
                    'error' => $e->getMessage(),
                    'query' => $query,
                    'author_id' => $authorId,
                    'section_id' => $sectionId
                ]);
            }
        }
        
        // Get filter options for the search form
        $authors = Author::orderBy('full_name')->get(['id', 'full_name']);
        $bookSections = BookSection::orderBy('name')->get(['id', 'name']);
        
        return view('search.results', compact(
            'results',
            'query',
            'authorId',
            'sectionId',
            'authors',
            'bookSections',
            'totalResults'
        ));
    }
    
    /**
     * Show search form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $authors = Author::orderBy('full_name')->get(['id', 'full_name']);
        $bookSections = BookSection::orderBy('name')->get(['id', 'name']);
        
        return view('search.index', compact('authors', 'bookSections'));
    }
    
    /**
     * API endpoint for search (returns JSON) - Optimized version
     *
     * @param Request $request
     * @param OptimizedSearchService $searchService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiSearch(Request $request, OptimizedSearchService $searchService)
    {
        $startTime = microtime(true);
        
        $query = trim($request->get('q', ''));
        $authorId = $request->get('author_id');
        $sectionId = $request->get('section_id');
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(max((int) $request->get('per_page', 15), 5), 50);
        
        if (empty($query) && !$authorId && !$sectionId) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى توفير كلمة بحث أو مرشح',
                'data' => [],
                'search_time' => 0
            ], 400);
        }

        try {
            $filters = array_filter([
                'author_id' => $authorId,
                'section_id' => $sectionId,
            ]);

            $results = $searchService->search($query, $filters, $page, $perPage);
            
            $searchTime = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'success' => true,
                'data' => $results['results'],
                'pagination' => [
                    'current_page' => $results['current_page'],
                    'last_page' => $results['last_page'],
                    'per_page' => $results['per_page'],
                    'total' => $results['total'],
                    'from' => $results['from'],
                    'to' => $results['to']
                ],
                'search_time' => $searchTime . 'ms'
            ]);

        } catch (\Exception $e) {
            $searchTime = round((microtime(true) - $startTime) * 1000, 2);
            
            \Log::error('Optimized search failed', [
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
}
