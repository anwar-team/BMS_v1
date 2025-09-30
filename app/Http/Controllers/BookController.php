<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index()
    {
        $books = Book::with(['authors', 'mainAuthors', 'publisher'])
                    ->where('status', 'published')
                    ->where('visibility', 'public')
                    ->paginate(15);
        return view('books.index', compact('books'));
    }

    /**
     * Display detailed information about the book.
     */
    public function details($id)
    {
        $book = Book::with(['authors', 'mainAuthors', 'publisher'])->findOrFail($id);
        
        return view('books.details', compact('book'));
    }

    /**
     * Display book for reading with pages.
     */
    public function read($id)
    {
        $book = Book::with('pages')->findOrFail($id);
        
        // Increment views count
        $book->increment('views_count');
        
        return view('books.read', compact('book'));
    }

    /**
     * Download book file.
     */
    public function download($id)
    {
        $book = Book::findOrFail($id);
        
        if (!$book->file_path) {
            abort(404, 'ملف الكتاب غير متوفر');
        }
        
        $filePath = storage_path('app/public/' . $book->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'ملف الكتاب غير موجود');
        }
        
        // Increment downloads count
        $book->increment('downloads_count');
        
        return response()->download($filePath, $book->title . '.pdf');
    }
}