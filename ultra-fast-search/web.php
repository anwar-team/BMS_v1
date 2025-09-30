<?php
// Ultra-fast search related routes and API endpoints
// Copy these into your routes/web.php or include as a route file

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Ultra-fast search page
Route::get('/search/ultra-fast', function() {
    return view('ultra-fast-search.views.ultra-fast');
})->name('search.ultra-fast');

// Ultra-fast search API endpoint - uses the SearchController in this module
Route::get('/api/ultra-search', [\App\Http\Controllers\SearchController::class, 'apiSearch'])->name('api.ultra-search');

// Get full page content
Route::get('/api/page/{pageId}/full-content', function($pageId) {
    $page = \App\Models\Page::with(['book', 'book.authors'])->find($pageId);
    if (!$page) {
        return response()->json(['error' => 'الصفحة غير موجودة'], 404);
    }
    return response()->json([
        'success' => true,
        'page' => [
            'id' => $page->id,
            'full_content' => $page->content,
            'page_number' => $page->page_number,
            'book_id' => $page->book_id,
            'book_title' => $page->book->title ?? '',
        ]
    ]);
});

// Get related pages from the same book
Route::get('/api/book/{bookId}/pages', function($bookId) {
    $pages = \App\Models\Page::where('book_id', $bookId)
        ->select(['id', 'page_number', 'content'])
        ->limit(10)
        ->get()
        ->map(function($page) {
            return [
                'id' => $page->id,
                'page_number' => $page->page_number,
                'content_preview' => mb_substr($page->content, 0, 100) . '...'
            ];
        });
        
    return response()->json([
        'success' => true,
        'pages' => $pages,
        'pagination' => [
            'total' => $pages->count()
        ]
    ]);
});
