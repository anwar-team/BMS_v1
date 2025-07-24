<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Footnote extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'page_id',
        'chapter_id',
        'volume_id',
        'footnote_number',
        'content',
        'position_in_page',
        'reference_text',
        'type',
        'order_in_page',
        'is_original'
    ];

    protected $casts = [
        'position_in_page' => 'integer',
        'footnote_number' => 'integer',
        'order_in_page' => 'integer',
        'is_original' => 'boolean'
    ];

    /**
     * العلاقة مع الكتاب
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * العلاقة مع الصفحة
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * العلاقة مع الفصل
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * العلاقة مع المجلد
     */
    public function volume(): BelongsTo
    {
        return $this->belongsTo(Volume::class);
    }

    /**
     * البحث في الحواشي
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('content', 'like', "%{$term}%")
                    ->orWhere('reference_text', 'like', "%{$term}%");
    }

    /**
     * تصفية حسب نوع الحاشية
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * الحواشي الأصلية فقط
     */
    public function scopeOriginal($query)
    {
        return $query->where('is_original', true);
    }

    /**
     * ترتيب حسب الموقع في الصفحة
     */
    public function scopeOrderedByPosition($query)
    {
        return $query->orderBy('position_in_page')
                    ->orderBy('order_in_page');
    }

    /**
     * الحصول على النص المختصر للحاشية
     */
    public function getShortContentAttribute()
    {
        return str_limit($this->content, 100);
    }

    /**
     * تحديد ما إذا كانت الحاشية طويلة
     */
    public function getIsLongAttribute()
    {
        return strlen($this->content) > 200;
    }
}