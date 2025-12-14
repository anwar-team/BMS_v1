<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Display a listing of authors.
     */
    public function index()
    {
        $authors = Author::withCount(['books' => function($query) {
            $query->where('status', 'published')
                  ->where('visibility', 'public');
        }])
        ->orderByDesc('books_count')
        ->orderBy('full_name')
        ->paginate(15);
        
        return view('authors.index', compact('authors'));
    }

    /**
     * Display the specified author.
     */
    public function show($id)
    {
        $author = Author::with(['books' => function($query) {
            $query->where('status', 'published')
                  ->where('visibility', 'public')
                  ->with(['bookSection', 'publisher'])
                  ->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        
        return view('authors.show', compact('author'));
    }

    /**
     * Display detailed information about the author.
     */
    public function details($id)
    {
        $author = Author::with(['books' => function($query) {
            $query->where('status', 'published')
                  ->where('visibility', 'public')
                  ->with(['bookSection', 'publisher'])
                  ->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        
        // Get paginated books for the table
        $books = Book::whereHas('authors', function($query) use ($id) {
            $query->where('authors.id', $id);
        })
        ->with(['authors', 'bookSection', 'publisher'])
        ->where('status', 'published')
        ->where('visibility', 'public')
        ->orderBy('created_at', 'desc')
        ->paginate(10);
        
        return view('authors.details', compact('author', 'books'));
    }
}