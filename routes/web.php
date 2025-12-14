<?php

use App\Livewire\SuperDuper\Pages\ContactUs;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ShowAllController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\SearchAllController;
use App\Http\Controllers\FeedbackComplaintController;
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


// ===================================================================
// MAIN ROUTES
// ===================================================================

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/contact-us', ContactUs::class)->name('contact-us');

// ===================================================================  
// DISABLED ROUTES (Currently not needed)
// ===================================================================

// Feedback & Complaints Routes (Public)
Route::post('/feedback', [FeedbackComplaintController::class, 'store'])->name('feedback.store');
//Route::get('/test-feedback', fn() => view('test-feedback'))->name('test.feedback');
//Route::get('/test-beta-notices', fn() => view('test-beta-notices'))->name('test.beta.notices');
//Route::get('/test-floating-button', fn() => view('test-floating-button'))->name('test.floating.button');

// Blog Routes (Disabled)
//Route::get('/blog', BlogList::class)->name('blog');
//Route::get('/blog/{slug}', BlogDetails::class)->name('blog.show');

// ===================================================================
// CONTENT ROUTES
// ===================================================================

Route::get('/categories', [CategoriesController::class, 'index'])->name('categories');
Route::get('/show-all', [ShowAllController::class, 'index'])->name('show-all');

// Book routes
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{id}/details', [BookController::class, 'details'])->name('books.details')->where('id', '[0-9]+');
Route::get('/books/{id}/read', [BookController::class, 'read'])->name('books.read.legacy')->where('id', '[0-9]+');
Route::get('/books/{id}/download', [BookController::class, 'download'])->name('books.download')->where('id', '[0-9]+');

// ===================================================================
// SEARCH ROUTES
// ===================================================================
Route::get('/search', function() {
    return view('ultra-fast-search.views.ultra-fast');
})->name('search.ultra-fast');

// API Routes - Grouped for better organization
Route::prefix('api')->name('api.')->group(function () {
    // Ultra-fast search APIs
    Route::get('/ultra-search', [\App\Http\Controllers\SearchController::class, 'apiSearch'])->name('ultra-search');
    Route::get('/filter-options', [\App\Http\Controllers\SearchController::class, 'getFilterOptions'])->name('filter-options');
    Route::get('/available-filters', [\App\Http\Controllers\SearchController::class, 'getAvailableFilters'])->name('available-filters');
    
    // Advanced Search APIs
    Route::prefix('search-all')->name('search.')->group(function () {
        Route::get('/authors', [SearchAllController::class, 'searchAuthors'])->name('authors');
        Route::get('/books', [SearchAllController::class, 'searchBookTitles'])->name('books');
        Route::get('/sections', [SearchAllController::class, 'getBookSections'])->name('sections');
        Route::get('/unified', [SearchAllController::class, 'searchUnified'])->name('unified');
        Route::get('/suggestions', [SearchAllController::class, 'searchSuggestions'])->name('suggestions');
        Route::get('/stats', [SearchAllController::class, 'getSearchStats'])->name('stats');
    });
    
    // Page and Book Content APIs
    Route::get('/page/{pageId}/full-content', function($pageId) {
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
    })->name('page.full-content');

    Route::get('/book/{bookId}/pages', function($bookId) {
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
    })->name('book.pages');

    Route::get('/book/{bookId}/page/{pageNumber}', function($bookId, $pageNumber) {
        $page = \App\Models\Page::with(['book', 'book.authors'])
            ->where('book_id', $bookId)
            ->where('page_number', $pageNumber)
            ->first();
            
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
    })->name('book.page');
});

// ===================================================================
// AUTHORS & PUBLISHERS ROUTES  
// ===================================================================
Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/authors/{id}', [AuthorController::class, 'show'])->name('authors.show')->where('id', '[0-9]+');
Route::get('/authors/{id}/details', [AuthorController::class, 'details'])->name('authors.details')->where('id', '[0-9]+');

// Publisher routes
//Route::get('/publishers', [PublisherController::class, 'index'])->name('publishers.index');
//Route::get('/publishers/{id}', [PublisherController::class, 'show'])->name('publishers.show')->where('id', '[0-9]+');
//Route::get('/publishers/{id}/details', [PublisherController::class, 'details'])->name('publishers.details')->where('id', '[0-9]+');

// ===================================================================
// STATIC PAGES
// ===================================================================

Route::get('/privacy-policy', function () {
    return view('components.superduper.pages.coming-soon', ['page_type' => 'privacy']);
})->name('privacy-policy');

Route::get('/terms-conditions', function () {
    return view('components.superduper.pages.coming-soon', ['page_type' => 'privacy']);
})->name('terms-conditions');

Route::get('/coming-soon', function () {
    return view('components.superduper.pages.coming-soon', ['page_type' => 'generic']);
})->name('coming-soon');

// ===================================================================
// BOOK READER (Livewire Component)
// ===================================================================
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

//Route::post('/contact', [App\Http\Controllers\ContactController::class, 'submit'])
//    ->name('contact.submit');

// Shamela Import Route
//Route::get('/admin/shamela-import', App\Livewire\ShamelaScraper::class)
//    ->name('shamela.import')
//    ->middleware('auth');

// ===================================================================
// ADMIN ROUTES
// ===================================================================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('feedback', FeedbackComplaintController::class)->except(['create', 'store']);
});

// ===================================================================
// SPECIAL FUNCTIONALITY
// ===================================================================

//// TODO: Create actual blog preview component
//Route::post('/blog-preview', function () {
//    // Implementation pending
//})->name('blog.preview');

// Impersonation functionality
Route::get('impersonate/leave', function () {
    if (!app(ImpersonateManager::class)->isImpersonating()) {
        return redirect('/');
    }

    app(ImpersonateManager::class)->leave();

    return redirect(
        session()->pull('impersonate.back_to')
    );
})->name('impersonate.leave')->middleware('web');
