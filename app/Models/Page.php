<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'volume_id',
        'chapter_id',
        'page_number',
        'content',
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
     * العلاقة مع الحواشي
     */
    public function footnotes(): HasMany
    {
        return $this->hasMany(Footnote::class);
    }

    /**
     * العلاقة مع الفهارس
     */
    public function bookIndexes(): HasMany
    {
        return $this->hasMany(BookIndex::class);
    }

    /**
     * العلاقة مع مراجع الصفحة
     */
    public function pageReferences(): HasMany
    {
        return $this->hasMany(PageReference::class);
    }

    /**
     * العلاقة مع التعليقات
     */
    public function annotations(): HasMany
    {
        return $this->hasMany(Annotation::class);
    }

    /**
     * الحصول على الحواشي مرتبة حسب الموقع
     */
    public function orderedFootnotes()
    {
        return $this->footnotes()->orderedByPosition();
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
     * الحصول على التعليقات العامة
     */
    public function publicAnnotations()
    {
        return $this->annotations()->public();
    }

    /**
     * التحقق من وجود حواشي
     */
    public function hasFootnotes(): bool
    {
        return $this->footnotes()->exists();
    }

    /**
     * التحقق من وجود تعليقات
     */
    public function hasAnnotations(): bool
    {
        return $this->annotations()->exists();
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
}
