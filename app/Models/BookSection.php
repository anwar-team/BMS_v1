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
}
