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
}
