<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'reference_id',
        'chapter_id',
        'citation_text',
        'position_in_page',
        'citation_type',
        'context',
        'is_primary_source'
    ];

    protected $casts = [
        'position_in_page' => 'integer',
        'is_primary_source' => 'boolean'
    ];

    /**
     * العلاقة مع الصفحة
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * العلاقة مع المرجع
     */
    public function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class);
    }

    /**
     * العلاقة مع الفصل
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * زيادة عداد الاستشهادات في المرجع عند الإنشاء
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($pageReference) {
            $pageReference->reference->incrementCitationCount();
        });

        static::deleted(function ($pageReference) {
            $pageReference->reference->decrement('citation_count');
        });
    }

    /**
     * البحث في الاستشهادات
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('citation_text', 'like', "%{$term}%")
                    ->orWhere('context', 'like', "%{$term}%");
    }

    /**
     * تصفية حسب نوع الاستشهاد
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('citation_type', $type);
    }

    /**
     * المصادر الأولية فقط
     */
    public function scopePrimarySources($query)
    {
        return $query->where('is_primary_source', true);
    }

    /**
     * ترتيب حسب الموقع في الصفحة
     */
    public function scopeOrderedByPosition($query)
    {
        return $query->orderBy('position_in_page');
    }

    /**
     * الاستشهادات المباشرة
     */
    public function scopeDirectQuotes($query)
    {
        return $query->where('citation_type', 'direct_quote');
    }

    /**
     * الحصول على النص المختصر للاستشهاد
     */
    public function getShortCitationAttribute()
    {
        return str_limit($this->citation_text, 100);
    }

    /**
     * الحصول على السياق المختصر
     */
    public function getShortContextAttribute()
    {
        return str_limit($this->context, 150);
    }

    /**
     * تحديد ما إذا كان الاستشهاد طويلاً
     */
    public function getIsLongCitationAttribute()
    {
        return strlen($this->citation_text) > 200;
    }

    /**
     * الحصول على نوع الاستشهاد باللغة العربية
     */
    public function getCitationTypeArabicAttribute()
    {
        $types = [
            'direct_quote' => 'اقتباس مباشر',
            'paraphrase' => 'إعادة صياغة',
            'reference' => 'مرجع',
            'see_also' => 'انظر أيضاً'
        ];
        
        return $types[$this->citation_type] ?? $this->citation_type;
    }

    /**
     * الحصول على معلومات الاستشهاد الكاملة
     */
    public function getFullCitationInfoAttribute()
    {
        $info = [
            'reference' => $this->reference->full_citation,
            'page' => $this->page->page_number,
            'type' => $this->citation_type_arabic,
            'is_primary' => $this->is_primary_source
        ];
        
        if ($this->citation_text) {
            $info['citation'] = $this->citation_text;
        }
        
        return $info;
    }
}