<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'cover_image',
        'published_year',
        'publisher',
        'publisher_id',
        'pages_count',
        'volumes_count',
        'status',
        'visibility',
        'cover_image_url',
        'source_url',
        'book_section_id',
    ];

    protected $casts = [
        'published_year' => 'integer',
        'pages_count' => 'integer',
        'volumes_count' => 'integer',
    ];

    protected static function booted()
{
    static::saving(function ($book) {
        $book->pages_count = $book->pages()->count();
        $book->volumes_count = $book->volumes()->count();
    });
}

    /**
     * العلاقة مع قسم الكتاب
     */
    public function bookSection(): BelongsTo
    {
        return $this->belongsTo(BookSection::class, 'book_section_id');
    }

    /**
     * العلاقة مع المؤلفين (many-to-many)
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_book')
            ->withPivot(['role', 'is_main', 'display_order'])
            ->withTimestamps();
    }

    /**
     * العلاقة مع المجلدات
     */
    public function volumes(): HasMany
    {
        return $this->hasMany(Volume::class);
    }

    /**
     * العلاقة مع الفصول
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    /**
     * العلاقة مع الصفحات
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * العلاقة مع جدول المؤلفين والكتب (pivot table)
     */
    public function authorBooks(): HasMany
    {
        return $this->hasMany(AuthorBook::class, 'book_id');
    }

    /**
     * الحصول على المؤلفين الرئيسيين
     */
    public function mainAuthors(): BelongsToMany
    {
        return $this->authors()->wherePivot('is_main', true);
    }

    /**
     * scope للكتب المنشورة
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * scope للكتب العامة
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * العلاقة مع استيراد BOK
     */
    public function bokImports(): HasMany
    {
        return $this->hasMany(BokImport::class);
    }

    /**
     * الحصول على آخر استيراد BOK
     */
    public function latestBokImport()
    {
        return $this->hasOne(BokImport::class)->latestOfMany();
    }

    /**
     * التحقق من كون الكتاب مستورد من BOK
     */
    public function isImportedFromBok(): bool
    {
        return $this->bokImports()->exists();
    }

    /**
     * الحصول على معلومات استيراد BOK
     */
    public function getBokImportInfo(): ?BokImport
    {
        return $this->latestBokImport;
    }

    /**
     * الحصول على رابط صورة الغلاف
     */
    public function getCoverImageAttribute($value)
    {
        return $value ?? $this->cover_image_url;
    }
}
