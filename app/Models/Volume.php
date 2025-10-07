<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Volume extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'number',
        'title',
        'page_start',
        'page_end',
    ];

    protected $casts = [
        'number' => 'integer',
        'page_start' => 'integer',
        'page_end' => 'integer',
    ];
  /**
     * العلاقة مع الكتاب
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * العلاقة مع الفصول
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    /**
     * العلاقة مع الفصول الرئيسية فقط (parent_id = null)
     */
    public function topLevelChapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->whereNull('parent_id')->orderBy('order');
    }

    /**
     * العلاقة مع الصفحات
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * الحصول على عدد الصفحات في المجلد
     */
    public function getPagesCountAttribute(): int
    {
        if ($this->page_start && $this->page_end) {
            return $this->page_end - $this->page_start + 1;
        }
        return $this->pages()->count();
    }

    /**
     * الحصول على الاسم الكامل للمجلد
     */
    public function getFullTitleAttribute(): string
    {
        $title = "المجلد {$this->number}";
        if ($this->title) {
            $title .= " - {$this->title}";
        }
        return $title;
    }

    /**
     * Override the chapters attribute to return only top-level chapters when accessed directly
     * This prevents duplicates in the TOC
     */
    public function getChaptersAttribute()
    {
        // If topLevelChapters is already loaded, return it
        if ($this->relationLoaded('topLevelChapters')) {
            return $this->getRelation('topLevelChapters');
        }
        
        // If chapters is loaded with constraints (from eager loading), return it
        if ($this->relationLoaded('chapters')) {
            $chapters = $this->getRelation('chapters');
            // Filter to only top-level chapters if not already filtered
            if ($chapters && $chapters->count() > 0) {
                $topLevel = $chapters->filter(fn($ch) => $ch->parent_id === null);
                if ($topLevel->count() > 0) {
                    return $topLevel;
                }
            }
            return $chapters;
        }
        
        // Default: load and return top-level chapters only
        return $this->topLevelChapters;
    }
}