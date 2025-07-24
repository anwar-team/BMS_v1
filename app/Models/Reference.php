<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reference extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'title',
        'author',
        'publisher',
        'publication_year',
        'page_reference',
        'reference_type',
        'isbn',
        'url',
        'notes',
        'edition',
        'volume_info',
        'citation_count',
        'is_verified'
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'citation_count' => 'integer',
        'is_verified' => 'boolean'
    ];

    /**
     * العلاقة مع الكتاب
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * العلاقة مع استشهادات الصفحات
     */
    public function pageReferences(): HasMany
    {
        return $this->hasMany(PageReference::class);
    }

    /**
     * البحث في المراجع
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('title', 'like', "%{$term}%")
                    ->orWhere('author', 'like', "%{$term}%")
                    ->orWhere('publisher', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%");
    }

    /**
     * تصفية حسب نوع المرجع
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('reference_type', $type);
    }

    /**
     * المراجع المحققة فقط
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * ترتيب حسب عدد الاستشهادات
     */
    public function scopeOrderedByCitations($query)
    {
        return $query->orderBy('citation_count', 'desc');
    }

    /**
     * المراجع حسب السنة
     */
    public function scopeByYear($query, $year)
    {
        return $query->where('publication_year', $year);
    }

    /**
     * المراجع في فترة زمنية
     */
    public function scopeBetweenYears($query, $startYear, $endYear)
    {
        return $query->whereBetween('publication_year', [$startYear, $endYear]);
    }

    /**
     * زيادة عداد الاستشهادات
     */
    public function incrementCitationCount()
    {
        $this->increment('citation_count');
    }

    /**
     * الحصول على العنوان المختصر
     */
    public function getShortTitleAttribute()
    {
        return str_limit($this->title, 80);
    }

    /**
     * الحصول على المعلومات الكاملة للمرجع
     */
    public function getFullCitationAttribute()
    {
        $citation = $this->title;
        
        if ($this->author) {
            $citation = $this->author . '. ' . $citation;
        }
        
        if ($this->publisher && $this->publication_year) {
            $citation .= '. ' . $this->publisher . ', ' . $this->publication_year;
        } elseif ($this->publication_year) {
            $citation .= '. ' . $this->publication_year;
        }
        
        if ($this->edition) {
            $citation .= '. ' . $this->edition;
        }
        
        if ($this->page_reference) {
            $citation .= '. ' . $this->page_reference;
        }
        
        return $citation;
    }

    /**
     * تحديد ما إذا كان المرجع مهماً
     */
    public function getIsImportantAttribute()
    {
        return $this->citation_count >= 5 || $this->is_verified;
    }

    /**
     * الحصول على نوع المرجع باللغة العربية
     */
    public function getReferenceTypeArabicAttribute()
    {
        $types = [
            'book' => 'كتاب',
            'article' => 'مقال',
            'website' => 'موقع إلكتروني',
            'manuscript' => 'مخطوط',
            'hadith_collection' => 'مجموعة أحاديث',
            'tafsir' => 'تفسير',
            'fatwa' => 'فتوى'
        ];
        
        return $types[$this->reference_type] ?? $this->reference_type;
    }
}