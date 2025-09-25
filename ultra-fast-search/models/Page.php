<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

/**
 * Page Model for Ultra-Fast Search
 * 
 * This is a simplified version that includes only the essential methods
 * needed for the search functionality to work independently.
 */
class Page extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'book_id',
        'page_number',
        'content',
        'word_count',
    ];

    protected $casts = [
        'page_number' => 'integer',
    ];

    /**
     * العلاقة مع الكتاب
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the indexable data array for the model.
     * This method is required by Laravel Scout
     */
    public function toSearchableArray(): array
    {
        $searchableArray = [
            'id' => $this->id,
            'content' => $this->content,
            'page_number' => $this->page_number,
            'book_id' => $this->book_id,
        ];

        // Add book information if available
        if ($this->relationLoaded('book') && $this->book) {
            $searchableArray['book_title'] = $this->book->title ?? '';
            $searchableArray['book_section_id'] = $this->book->book_section_id ?? null;
            
            // Add author information if available
            if ($this->book->relationLoaded('authors') && $this->book->authors->isNotEmpty()) {
                $searchableArray['author_names'] = $this->book->authors->pluck('full_name')->implode(' ');
                $searchableArray['author_ids'] = $this->book->authors->pluck('id')->toArray();
            }
        }

        return $searchableArray;
    }

    /**
     * Modify the query used to retrieve models when making all searchable.
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['book', 'book.authors']);
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return config('scout.prefix') . 'pages';
    }

    /**
     * Get Scout metadata for enhanced search.
     */
    public function scoutMetadata(): array
    {
        return [
            'book_title' => $this->book->title ?? '',
            'author_names' => $this->book && $this->book->authors 
                ? $this->book->authors->pluck('full_name')->implode(' ') 
                : '',
            'book_section_id' => $this->book->book_section_id ?? null,
        ];
    }
}