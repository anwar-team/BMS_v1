<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publisher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'email',
        'phone',
        'description',
        'website_url',
        'is_active',
    ];

    /**
     * العلاقة مع الكتب
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
