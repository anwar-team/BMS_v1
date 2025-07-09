<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'volume_id',
        'book_id',
        'chapter_number',
        'title',
        'parent_id',
        'order',
        'page_start',
        'page_end',
        'chapter_type',
    ];

    protected $casts = [
        'order' => 'integer',
        'page_start' => 'integer',
        'page_end' => 'integer',
    ];

    /**
     * العلاقة مع المجلد
     */
    public function volume(): BelongsTo
    {
        return $this->belongsTo(Volume::class);
    }

    /**
     * العلاقة مع الكتاب
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * العلاقة مع الفصل الأب
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'parent_id');
    }

    /**
     * العلاقة مع الفصول الفرعية
     */
    public function children(): HasMany
    {
        return $this->hasMany(Chapter::class, 'parent_id');
    }

    /**
     * العلاقة مع الصفحات
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * scope للفصول الرئيسية
     */
    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * scope للفصول الفرعية
     */
    public function scopeSub($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * الحصول على عدد الصفحات في الفصل
     */
    public function getPagesCountAttribute(): int
    {
        if ($this->page_start && $this->page_end) {
            return $this->page_end - $this->page_start + 1;
        }
        return $this->pages()->count();
    }

    /**
     * الحصول على العنوان الكامل للفصل
     */
    public function getFullTitleAttribute(): string
    {
        $title = '';
        if ($this->chapter_number) {
            $title .= "الفصل {$this->chapter_number}: ";
        }
        $title .= $this->title;
        return $title;
    }
}
