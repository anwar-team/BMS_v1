<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Page extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'book_id',
        'volume_id',
        'chapter_id',
        'page_number',
        'internal_index',
        'part',
        'content',
        'html_content',
        'original_page_number',
        'word_count',
        'printed_missing',
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
     * العلاقة مع المجلد
     */
    public function volume(): BelongsTo
    {
        return $this->belongsTo(Volume::class);
    }

    /**
     * العلاقة مع الفصل
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * الحصول على الصفحة التالية
     */
    public function getNextPageAttribute(): ?Page
    {
        return self::where('book_id', $this->book_id)
            ->where('page_number', '>', $this->page_number)
            ->orderBy('page_number')
            ->first();
    }

    /**
     * الحصول على الصفحة السابقة
     */
    public function getPreviousPageAttribute(): ?Page
    {
        return self::where('book_id', $this->book_id)
            ->where('page_number', '<', $this->page_number)
            ->orderBy('page_number', 'desc')
            ->first();
    }

    /**
     * scope لصفحات كتاب معين
     */
    public function scopeOfBook($query, $bookId)
    {
        return $query->where('book_id', $bookId);
    }

    /**
     * scope لصفحات مجلد معين
     */
    public function scopeOfVolume($query, $volumeId)
    {
        return $query->where('volume_id', $volumeId);
    }

    /**
     * scope لصفحات فصل معين
     */
    public function scopeOfChapter($query, $chapterId)
    {
        return $query->where('chapter_id', $chapterId);
    }

    /**
     * الحصول على الفهارس عالية الأهمية
     */
    public function importantIndexes()
    {
        return $this->bookIndexes()->highRelevance();
    }

    /**
     * الحصول على المراجع المباشرة
     */
    public function directReferences()
    {
        return $this->pageReferences()->directQuotes();
    }

    /**
     * الحصول على عدد الكلمات في الصفحة
     */
    public function getWordCountAttribute(): int
    {
        return str_word_count(strip_tags($this->content));
    }

    /**
     * الحصول على عدد الأحرف في الصفحة
     */
    public function getCharacterCountAttribute(): int
    {
        return mb_strlen(strip_tags($this->content));
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