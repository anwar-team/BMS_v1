<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class TestSearchController extends Controller
{
    public function test(Request $request)
    {
        try {
            $query = $request->get('q', 'الله');
            
            // Very simple test without Scout
            $results = Page::take(3)->get();
            
            return response()->json([
                'success' => true,
                'query' => $query,
                'count' => $results->count(),
                'message' => 'Test working without Scout',
                'data' => $results->map(function($page) {
                    return [
                        'id' => $page->id,
                        'content' => mb_substr(strip_tags($page->content), 0, 50)
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}