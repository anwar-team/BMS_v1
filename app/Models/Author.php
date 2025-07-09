<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'fname',
        'mname',
        'lname',
        'biography',
        'nationality',
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
     * الحصول على الاسم الكامل
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->fname, $this->mname, $this->lname]);
        return implode(' ', $parts);
    }

    /**
     * الحصول على الكتب الرئيسية
     */
    public function mainBooks(): BelongsToMany
    {
        return $this->books()->wherePivot('is_main', true);
    }
}
