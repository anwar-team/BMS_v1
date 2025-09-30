<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BookSection Model for Ultra-Fast Search
 */
class BookSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * العلاقة مع الكتب
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}