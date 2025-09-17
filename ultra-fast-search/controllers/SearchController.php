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
                'search_mode' => $request->get('search_mode', 'flexible'),
                'proximity' => $request->get('proximity', 'any_order'),
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
     * Show search form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('ultra-fast-search.views.ultra-fast');
    }
}