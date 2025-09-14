<?php

use App\Livewire\SuperDuper\BlogList;
use App\Livewire\SuperDuper\BlogDetails;
use App\Livewire\SuperDuper\Pages\ContactUs;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ShowAllController;
use App\Http\Controllers\BookReadController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SearchController;
use App\Livewire\Reader\BookReader;
use Illuminate\Support\Facades\Route;
use Lab404\Impersonate\Services\ImpersonateManager;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Language switching route
Route::get('/language/{language}', [LanguageController::class, 'switch'])->name('language.switch');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/blog', BlogList::class)->name('blog');

Route::get('/blog/{slug}', BlogDetails::class)->name('blog.show');

Route::get('/contact-us', ContactUs::class)->name('contact-us');

Route::get('/categories', [CategoriesController::class, 'index'])->name('categories');

// روابط عرض جميع الكتب والمؤلفين مع إمكانية التصفية
Route::get('/show-all', [ShowAllController::class, 'index'])->name('show-all');

// Book routes
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{id}/details', [BookController::class, 'details'])->name('books.details')->where('id', '[0-9]+');
Route::get('/books/{id}/read', [BookController::class, 'read'])->name('books.read')->where('id', '[0-9]+');
Route::get('/books/{id}/download', [BookController::class, 'download'])->name('books.download')->where('id', '[0-9]+');

// Author routes
Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/authors/{id}', [AuthorController::class, 'show'])->name('authors.show')->where('id', '[0-9]+');
Route::get('/authors/{id}/details', [AuthorController::class, 'details'])->name('authors.details')->where('id', '[0-9]+');

// Publisher routes
Route::get('/publishers', [PublisherController::class, 'index'])->name('publishers.index');
Route::get('/publishers/{id}', [PublisherController::class, 'show'])->name('publishers.show')->where('id', '[0-9]+');
Route::get('/publishers/{id}/details', [PublisherController::class, 'details'])->name('publishers.details')->where('id', '[0-9]+');

// Simple test route
Route::get('/simple-test', function() {
    return response()->json(['message' => 'Laravel is working', 'time' => now()]);
});

// Test ultra search service
Route::get('/test-ultra-search', function() {
    try {
        $service = app(\App\Services\UltraFastSearchService::class);
        $result = $service->search('الله', [], 1, 5);
        return response()->json([
            'status' => 'success',
            'results_count' => count($result['results'] ?? []),
            'total' => $result['total'] ?? 0,
            'sample_result' => $result['results'][0] ?? null
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile())
        ]);
    }
});

// Direct search test with custom parameters
Route::get('/direct-search-test', function(Request $request) {
    try {
        $query = $request->get('q', 'الله');
        $perPage = (int) $request->get('per_page', 3);
        
        $service = app(\App\Services\UltraFastSearchService::class);
        $startTime = microtime(true);
        $result = $service->search($query, [], 1, $perPage);
        $searchTime = round((microtime(true) - $startTime) * 1000, 2);
        
        return response()->json([
            'query' => $query,
            'requested_per_page' => $perPage,
            'actual_results_count' => count($result['results'] ?? []),
            'total_found' => $result['total'] ?? 0,
            'search_time_ms' => $searchTime,
            'pagination' => [
                'current_page' => $result['current_page'] ?? 1,
                'per_page' => $result['per_page'] ?? $perPage,
                'last_page' => $result['last_page'] ?? 1,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile())
        ]);
    }
});

// Test search route
Route::get('/test-search', [App\Http\Controllers\TestSearchController::class, 'test']);

// Search routes
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/search/ultra-fast', function() {
    return view('search.ultra-fast');
})->name('search.ultra-fast');
Route::get('/search/results', [SearchController::class, 'search'])->name('search.results');
Route::get('/api/search', [SearchController::class, 'apiSearch'])->name('api.search');

// Redirect old search to ultra-fast (optional)
Route::get('/ultra-search', function() {
    return redirect()->route('search.ultra-fast');
})->name('ultra-search');

// Ultra-fast search API routes (optimized with Context7 MCP best practices)
Route::get('/api/ultra-search', [SearchController::class, 'apiSearch'])->name('api.ultra-search');

// Fast search API routes
Route::get('/api/fast-search', [App\Http\Controllers\Api\FastSearchController::class, 'search'])->name('api.fast-search');
Route::get('/api/search/suggestions', [App\Http\Controllers\Api\FastSearchController::class, 'suggestions'])->name('api.search.suggestions');
Route::get('/api/search/health', [App\Http\Controllers\Api\FastSearchController::class, 'health'])->name('api.search.health');

Route::get('/privacy-policy', function () {
    return view('components.superduper.pages.coming-soon', ['page_type' => 'privacy']);
})->name('privacy-policy');

Route::get('/terms-conditions', function () {
    return view('components.superduper.pages.coming-soon', ['page_type' => 'privacy']);
})->name('terms-conditions');

Route::get('/coming-soon', function () {
    return view('components.superduper.pages.coming-soon', ['page_type' => 'generic']);
})->name('coming-soon');

// طرق قراءة الكتب - Livewire Component
Route::get('/book/{bookId}/{pageNumber?}', BookReader::class)
    ->name('book.read')
    ->where(['bookId' => '[0-9]+', 'pageNumber' => '[0-9]+']);

// Legacy routes for backward compatibility (can be removed later)
// Route::get('/book/{bookId}/search', [BookReadController::class, 'search'])
//     ->name('book.search')
//     ->where('bookId', '[0-9]+');
// 
// Route::post('/book/{bookId}/goto/{pageNumber}', [BookReadController::class, 'goToPage'])
//     ->name('book.goto')
//     ->where(['bookId' => '[0-9]+', 'pageNumber' => '[0-9]+']);

Route::post('/contact', [App\Http\Controllers\ContactController::class, 'submit'])
    ->name('contact.submit');

// Shamela Import Route
Route::get('/admin/shamela-import', App\Livewire\ShamelaScraper::class)
    ->name('shamela.import')
    ->middleware('auth');

// TODO: Create actual blog preview component
Route::post('/blog-preview', function () {
    // Implementation pending
})->name('blog.preview');

Route::get('impersonate/leave', function () {
    if (!app(ImpersonateManager::class)->isImpersonating()) {
        return redirect('/');
    }

    app(ImpersonateManager::class)->leave();

    return redirect(
        session()->pull('impersonate.back_to')
    );
})->name('impersonate.leave')->middleware('web');