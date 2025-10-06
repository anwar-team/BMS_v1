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
     * API endpoint for search (returns JSON) - Ultra Fast version
     * 
     * @param UltraFastSearchService $searchService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiSearch(Request $request, UltraFastSearchService $searchService)
    {
        $startTime = microtime(true);
        
        $query = trim($request->get('q', ''));
        $authorId = $request->get('author_id');
        $sectionId = $request->get('section_id');
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(max((int) $request->get('per_page', 15), 5), 50);
        
        // تحويل الفلاتر المتعددة إلى arrays إذا لزم الأمر
        if ($authorId && is_string($authorId) && strpos($authorId, ',') !== false) {
            $authorId = array_filter(explode(',', $authorId));
        }
        if ($sectionId && is_string($sectionId) && strpos($sectionId, ',') !== false) {
            $sectionId = array_filter(explode(',', $sectionId));
        }
        
        // التحقق من وجود استعلام أو فلاتر صالحة
        $hasValidFilters = false;
        if ($authorId) {
            $hasValidFilters = is_array($authorId) ? count($authorId) > 0 : !empty($authorId);
        }
        if ($sectionId && !$hasValidFilters) {
            $hasValidFilters = is_array($sectionId) ? count($sectionId) > 0 : !empty($sectionId);
        }
        
        if (empty($query) && !$hasValidFilters) {
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
                'search_type' => $request->get('search_type', 'flexible_match'), // New system
                'search_mode' => $request->get('search_mode'), // Backward compatibility
                'proximity' => $request->get('proximity', 'any_order'), // Backward compatibility
            ]);

            $results = $searchService->search($query, $filters, $page, $perPage);
            
            $searchTime = round((microtime(true) - $startTime) * 1000, 2);

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