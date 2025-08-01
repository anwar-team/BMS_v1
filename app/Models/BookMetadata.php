<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookMetadata extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'metadata_key',
        'metadata_value',
        'metadata_type',
        'data_type',
        'description',
        'is_searchable',
        'is_public',
        'display_order'
    ];

    protected $casts = [
        'is_searchable' => 'boolean',
        'is_public' => 'boolean',
        'display_order' => 'integer'
    ];

    /**
     * العلاقة مع الكتاب
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * تحويل القيمة حسب نوع البيانات
     */
    public function getTypedValueAttribute()
    {
        switch ($this->data_type) {
            case 'number':
                return is_numeric($this->metadata_value) ? (float) $this->metadata_value : 0;
            case 'boolean':
                return filter_var($this->metadata_value, FILTER_VALIDATE_BOOLEAN);
            case 'date':
                return $this->metadata_value ? \Carbon\Carbon::parse($this->metadata_value) : null;
            case 'json':
                return json_decode($this->metadata_value, true);
            default:
                return $this->metadata_value;
        }
    }

    /**
     * تعيين القيمة مع التحويل المناسب
     */
    public function setTypedValueAttribute($value)
    {
        switch ($this->data_type) {
            case 'json':
                $this->metadata_value = is_array($value) ? json_encode($value) : $value;
                break;
            case 'date':
                $this->metadata_value = $value instanceof \Carbon\Carbon ? $value->toDateString() : $value;
                break;
            default:
                $this->metadata_value = (string) $value;
        }
    }

    /**
     * البحث في البيانات الوصفية
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('is_searchable', true)
                    ->where(function ($q) use ($term) {
                        $q->where('metadata_key', 'like', "%{$term}%")
                          ->orWhere('metadata_value', 'like', "%{$term}%")
                          ->orWhere('description', 'like', "%{$term}%");
                    });
    }

    /**
     * تصفية حسب نوع البيانات الوصفية
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('metadata_type', $type);
    }

    /**
     * البيانات العامة فقط
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * البيانات القابلة للبحث
     */
    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * ترتيب حسب ترتيب العرض
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')
                    ->orderBy('metadata_key');
    }

    /**
     * البحث بالمفتاح
     */
    public function scopeByKey($query, $key)
    {
        return $query->where('metadata_key', $key);
    }

    /**
     * البحث بالقيمة
     */
    public function scopeByValue($query, $value)
    {
        return $query->where('metadata_value', $value);
    }

    /**
     * الحصول على البيانات الوصفية لدبلن كور
     */
    public function scopeDublinCore($query)
    {
        return $query->where('metadata_type', 'dublin_core');
    }

    /**
     * البيانات الوصفية الإسلامية
     */
    public function scopeIslamicMetadata($query)
    {
        return $query->where('metadata_type', 'islamic_metadata');
    }

    /**
     * البيانات الوصفية الخاصة بالشاملة
     */
    public function scopeShamelaSpecific($query)
    {
        return $query->where('metadata_type', 'shamela_specific');
    }

    /**
     * الحصول على القيمة المنسقة للعرض
     */
    public function getFormattedValueAttribute()
    {
        switch ($this->data_type) {
            case 'date':
                $date = $this->typed_value;
                return $date ? $date->format('Y-m-d') : '';
            case 'boolean':
                return $this->typed_value ? 'نعم' : 'لا';
            case 'json':
                $value = $this->typed_value;
                return is_array($value) ? implode(', ', $value) : $this->metadata_value;
            default:
                return $this->metadata_value;
        }
    }

    /**
     * الحصول على اسم المفتاح باللغة العربية
     */
    public function getKeyArabicAttribute()
    {
        $arabicKeys = [
            'dc.title' => 'العنوان',
            'dc.creator' => 'المؤلف',
            'dc.subject' => 'الموضوع',
            'dc.description' => 'الوصف',
            'dc.publisher' => 'الناشر',
            'dc.date' => 'التاريخ',
            'dc.language' => 'اللغة',
            'dc.format' => 'التنسيق',
            'dc.identifier' => 'المعرف',
            'dc.source' => 'المصدر',
            'hijri_date' => 'التاريخ الهجري',
            'madhhab' => 'المذهب',
            'manuscript_location' => 'مكان المخطوط',
            'verification_status' => 'حالة التحقيق'
        ];
        
        return $arabicKeys[$this->metadata_key] ?? $this->metadata_key;
    }

    /**
     * تحديد ما إذا كانت البيانات مهمة
     */
    public function getIsImportantAttribute()
    {
        $importantKeys = [
            'dc.title', 'dc.creator', 'dc.subject', 'dc.date',
            'hijri_date', 'madhhab', 'verification_status'
        ];
        
        return in_array($this->metadata_key, $importantKeys);
    }
}