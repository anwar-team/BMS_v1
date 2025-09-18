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

// Ultra-fast search routes
Route::get('/search', function() {
    return view('ultra-fast-search.views.ultra-fast');
})->name('search.ultra-fast');

Route::get('/api/ultra-search', [\App\Http\Controllers\SearchController::class, 'apiSearch'])->name('api.ultra-search');

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

// Author routes
Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/authors/{id}', [AuthorController::class, 'show'])->name('authors.show')->where('id', '[0-9]+');
Route::get('/authors/{id}/details', [AuthorController::class, 'details'])->name('authors.details')->where('id', '[0-9]+');

// Publisher routes
Route::get('/publishers', [PublisherController::class, 'index'])->name('publishers.index');
Route::get('/publishers/{id}', [PublisherController::class, 'show'])->name('publishers.show')->where('id', '[0-9]+');
Route::get('/publishers/{id}/details', [PublisherController::class, 'details'])->name('publishers.details')->where('id', '[0-9]+');

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