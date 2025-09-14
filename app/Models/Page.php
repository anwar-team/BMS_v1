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
     * Get the indexable data array for the model - OPTIMIZED.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        // Load relationships efficiently
        $this->loadMissing(['book.authors', 'book.bookSection']);

        // Get author names optimized
        $authorNames = $this->book->authors->pluck('full_name')->implode(' ');
        $authorIds = $this->book->authors->pluck('id')->toArray();

        return [
            'id' => $this->id,
            'content' => $this->prepareContentForSearch($this->content),
            'page_number' => $this->page_number,
            'book_id' => $this->book_id,
            'book_title' => $this->book->title ?? '',
            'author_names' => $authorNames,
            'author_ids' => $authorIds,
            'book_section_id' => $this->book->book_section_id ?? null,
            'published_year' => $this->book->published_year ?? null,
            'volume_id' => $this->volume_id,
            'chapter_id' => $this->chapter_id,
            'word_count' => $this->getWordCountAttribute(),
            'created_at' => $this->created_at?->timestamp,
            'updated_at' => $this->updated_at?->timestamp,
        ];
    }

    /**
     * Prepare content for search indexing.
     */
    protected function prepareContentForSearch(?string $content): string
    {
        if (!$content) {
            return '';
        }

        // إزالة HTML tags
        $content = strip_tags($content);
        
        // تنظيف النص العربي
        $content = preg_replace('/\s+/', ' ', $content); // إزالة المسافات الزائدة
        $content = trim($content);
        
        return $content;
    }

    /**
     * Get the index name for the model - OPTIMIZED.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return config('scout.prefix') . 'pages_optimized';
    }

    /**
     * Get Scout metadata for enhanced search.
     */
    public function scoutMetadata(): array
    {
        return [
            'indexed_at' => now()->timestamp,
            'model_type' => 'page',
        ];
    }

    /**
     * Should this model be searchable?
     */
    public function shouldBeSearchable(): bool
    {
        // فقط الصفحات التي تحتوي على محتوى
        return !empty($this->content) && !empty(trim(strip_tags($this->content)));
    }
}