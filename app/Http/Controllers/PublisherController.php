<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use App\Models\Book;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    /**
     * Display a listing of publishers.
     */
    public function index()
    {
        $publishers = Publisher::withCount(['books' => function($query) {
            $query->where('status', 'published')
                  ->where('visibility', 'public');
        }])
        ->where('is_active', true)
        ->orderByDesc('books_count')
        ->orderBy('name')
        ->paginate(15);
        
        return view('publishers.index', compact('publishers'));
    }

    /**
     * Display the specified publisher.
     */
    public function show($id)
    {
        $publisher = Publisher::with(['books' => function($query) {
            $query->where('status', 'published')
                  ->where('visibility', 'public')
                  ->with(['authors', 'bookSection'])
                  ->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        
        return view('publishers.show', compact('publisher'));
    }

    /**
     * Display detailed information about the publisher.
     */
    public function details($id)
    {
        $publisher = Publisher::with(['books' => function($query) {
            $query->where('status', 'published')
                  ->where('visibility', 'public')
                  ->with(['authors', 'bookSection'])
                  ->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        
        // Get paginated books for the table
        $books = Book::where('publisher_id', $id)
        ->with(['authors', 'bookSection'])
        ->where('status', 'published')
        ->where('visibility', 'public')
        ->orderBy('created_at', 'desc')
        ->paginate(10);
        
        return view('publishers.details', compact('publisher', 'books'));
    }
}