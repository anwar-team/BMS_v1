<?php

use App\Livewire\SuperDuper\BlogList;
use App\Livewire\SuperDuper\BlogDetails;
use App\Livewire\SuperDuper\Pages\ContactUs;
use App\Livewire\SuperDuper\Tables\BooksTable;
use App\Livewire\SuperDuper\Tables\AuthorsTable;
use App\Http\Controllers\CategoriesController;
use App\Livewire\HomePage;
use App\Livewire\ShowAllPage;
use App\Http\Controllers\BookReadController;
use App\Http\Controllers\BookController;
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

Route::get('/', HomePage::class)->name('home');

Route::get('/blog', BlogList::class)->name('blog');

Route::get('/blog/{slug}', BlogDetails::class)->name('blog.show');

Route::get('/contact-us', ContactUs::class)->name('contact-us');

Route::get('/categories', [CategoriesController::class, 'index'])->name('categories');

// روابط عرض جميع الكتب والمؤلفين مع إمكانية التصفية
Route::get('/show-all', ShowAllPage::class)->name('show-all');

// Search route
Route::get('/search', ShowAllPage::class)->name('search');

// Book routes
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show')->where('id', '[0-9]+');
Route::get('/books/{id}/details', [BookController::class, 'details'])->name('books.details')->where('id', '[0-9]+');
Route::get('/books/{id}/read', [BookController::class, 'read'])->name('books.read')->where('id', '[0-9]+');
Route::get('/books/{id}/download', [BookController::class, 'download'])->name('books.download')->where('id', '[0-9]+');

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

// Books Table Route
Route::get('/admin/books-table', BooksTable::class)
    ->name('books.table')
    ->middleware('auth');

// Authors Table Route
Route::get('/admin/authors-table', AuthorsTable::class)
    ->name('authors.table')
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

// Authentication routes for welcome page compatibility
Route::redirect('/login', '/admin/login')->name('login');
Route::redirect('/register', '/admin/register')->name('register');