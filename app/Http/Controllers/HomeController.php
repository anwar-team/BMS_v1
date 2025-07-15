<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\BookSection;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get book counts by category
        $bookCounts = [
            'aqeedah' => Book::whereHas('bookSection', function($query) {
                $query->where('name', 'like', '%عقيدة%');
            })->count(),
            'fiqh' => Book::whereHas('bookSection', function($query) {
                $query->where('name', 'like', '%فقه%');
            })->count(),
            'quran' => Book::whereHas('bookSection', function($query) {
                $query->where('name', 'like', '%قرآن%');
            })->count(),
            'islamic' => Book::whereHas('bookSection', function($query) {
                $query->where('name', 'like', '%إسلامية%');
            })->count(),
            'adhkar' => Book::whereHas('bookSection', function($query) {
                $query->where('name', 'like', '%أذكار%');
            })->count(),
            'research' => Book::whereHas('bookSection', function($query) {
                $query->where('name', 'like', '%بحوث%');
            })->count(),
        ];

        // Get recent books with pagination
        $books = Book::with(['authors', 'bookSection'])
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        // Get authors with their books
        $authors = Author::with(['books'])
            ->whereHas('books')
            ->orderBy('name')
            ->take(6)
            ->get();

        return view('components.superduper.pages.home', compact('bookCounts', 'books', 'authors'));
    }
}