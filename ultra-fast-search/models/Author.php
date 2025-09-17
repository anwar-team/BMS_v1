<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Author Model for Ultra-Fast Search
 */
class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
    ];

    /**
     * العلاقة مع الكتب (Many-to-Many)
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'author_book');
    }
}