<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorBook extends Model
{
    use HasFactory;

    protected $table = 'author_book';

    protected $fillable = [
        'book_id',
        'author_id',
        'role',
        'is_main',
        'display_order',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * العلاقة مع المؤلف
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * العلاقة مع الكتاب
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * الحصول على الدور باللغة العربية
     */
    public function getRoleArabicAttribute(): string
    {
        return match($this->role) {
            'author' => 'مؤلف',
            'co_author' => 'مؤلف مشارك',
            'editor' => 'محرر',
            'translator' => 'مترجم',
            'reviewer' => 'مراجع',
            'commentator' => 'معلق',
            default => $this->role
        };
    }
}