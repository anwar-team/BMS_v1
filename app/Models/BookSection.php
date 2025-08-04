<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
        'slug',
        'logo_path', // إضافة logo_path
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع القسم الأب
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(BookSection::class, 'parent_id');
    }

    /**
     * العلاقة مع الأقسام الفرعية
     */
    public function children(): HasMany
    {
        return $this->hasMany(BookSection::class, 'parent_id');
    }

    /**
     * العلاقة مع الكتب
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'book_section_id');
    }

    /**
     * scope للأقسام النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * scope للأقسام الرئيسية
     */
    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get sections with book count for homepage
     */
    public static function getForHomepage($limit = 6)
    {
        return self::where('is_active', true)
            ->whereNull('parent_id')
            ->withCount('books')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all active sections with book count for categories page
     */
    public static function getAllWithBookCount()
    {
        return self::where('is_active', true)
            ->withCount('books')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get section by slug
     */
    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->where('is_active', true)->first();
    }
}