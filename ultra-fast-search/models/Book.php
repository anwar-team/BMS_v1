<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Book Model for Ultra-Fast Search
 * 
 * Simplified version that includes only essential relationships
 * needed for the search functionality.
 */
class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'book_section_id',
    ];

    /**
     * العلاقة مع الصفحات
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * العلاقة مع المؤلفين (Many-to-Many)
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_book');
    }

    /**
     * العلاقة مع قسم الكتاب
     */
    public function bookSection(): BelongsTo
    {
        return $this->belongsTo(BookSection::class);
    }
}