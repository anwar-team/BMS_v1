<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        //'nickname',
        'biography',
        'madhhab',
        'birth_date',
        'death_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'death_date' => 'date',
    ];

    /**
     * العلاقة مع الكتب (many-to-many)
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'author_book')
            ->withPivot(['role', 'is_main', 'display_order'])
            ->withTimestamps();
    }



    /**
     * الحصول على الكتب الرئيسية
     */
    public function mainBooks(): BelongsToMany
    {
        return $this->books()->wherePivot('is_main', true);
    }
}
