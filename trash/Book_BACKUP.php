<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    protected $fillable = [
        'title',
        'description',
        'slug',
        'cover_image',
        'publisher_id',
        'pages_count',
        'volumes_count',
        'status',
        'visibility',
        'cover_image_url',
        'source_url',
        'book_section_id',
        'edition',
        'edition_DATA',
    ];

    protected $casts = [
        'pages_count' => 'integer',
        'volumes_count' => 'integer',
        'edition' => 'integer',
        'edition_DATA' => 'integer',
    ];

    /**
     * العلاقة مع قسم الكتاب
     */
    public function bookSection(): BelongsTo
    {
        return $this->belongsTo(BookSection::class);
    }

    /**
     * العلاقة مع المجلدات
     */
    public function volumes(): HasMany
    {
        return $this->hasMany(Volume::class);
    }

    /**
     * العلاقة مع الكتب المستوردة من BOK
     */
    public function bokImports(): HasMany
    {
        return $this->hasMany(BokImport::class);
    }

    /**
     * الحصول على آخر استيراد BOK
     */
    public function latestBokImport(): BelongsTo
    {
        return $this->belongsTo(BokImport::class, 'id', 'book_id')
            ->latest();
    }

    /**
     * العلاقة مع المؤلفين عبر الجدول الوسيط
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_book')
            ->withPivot('role', 'is_main', 'display_order')
            ->withTimestamps();
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
     * العلاقة مع فهارس الكتاب
     */
    public function bookIndexes(): HasMany
    {
        return $this->hasMany(BookIndex::class);
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

    public function importantIndexes()
    {
        return $this->bookIndexes()->highRelevance();
    }

    /**
     * الحصول على رابط صورة الغلاف
     */
    public function getCoverImageAttribute($value)
    {
        return $value ?? $this->cover_image_url;
    }
}
