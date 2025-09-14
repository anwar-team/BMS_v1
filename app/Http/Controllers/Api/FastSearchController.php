<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FastElasticsearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FastSearchController extends Controller
{
    protected $elasticsearchService;

    public function __construct(FastElasticsearchService $elasticsearchService)
    {
        $this->elasticsearchService = $elasticsearchService;
    }

    /**
     * Fast search API endpoint
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        $query = trim($request->get('q', ''));
        $authorId = $request->get('author_id');
        $sectionId = $request->get('section_id');
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(max((int) $request->get('per_page', 15), 5), 50);

        // Validate input
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

            $results = $this->elasticsearchService->search($query, $filters, $page, $perPage);
            
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
                'search_time' => $searchTime
            ]);

        } catch (\Exception $e) {
            $searchTime = round((microtime(true) - $startTime) * 1000, 2);
            
            \Log::error('Fast search failed', [
                'error' => $e->getMessage(),
                'query' => $query,
                'filters' => $filters ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في البحث: ' . $e->getMessage(),
                'data' => [],
                'search_time' => $searchTime
            ], 500);
        }
    }

    /**
     * Get search suggestions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'suggestions' => []
            ]);
        }

        try {
            $suggestions = $this->elasticsearchService->getSuggestions($query);
            
            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'suggestions' => []
            ]);
        }
    }

    /**
     * Health check for search service
     *
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            // Test simple search
            $this->elasticsearchService->search('test', [], 1, 1);
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return response()->json([
                'success' => true,
                'status' => 'healthy',
                'response_time' => $responseTime
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}