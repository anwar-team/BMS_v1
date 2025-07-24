<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Annotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_id',
        'page_id',
        'chapter_id',
        'user_id',
        'annotation_text',
        'highlighted_text',
        'position_start',
        'position_end',
        'annotation_type',
        'visibility',
        'color',
        'likes_count',
        'is_verified'
    ];

    protected $casts = [
        'position_start' => 'integer',
        'position_end' => 'integer',
        'likes_count' => 'integer',
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
     * العلاقة مع الصفحة
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * العلاقة مع الفصل
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * العلاقة مع المستخدم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * البحث في التعليقات
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('annotation_text', 'like', "%{$term}%")
                    ->orWhere('highlighted_text', 'like', "%{$term}%");
    }

    /**
     * تصفية حسب نوع التعليق
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('annotation_type', $type);
    }

    /**
     * التعليقات العامة فقط
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * التعليقات الخاصة للمستخدم
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('visibility', 'public')
              ->orWhere('user_id', $userId);
        });
    }

    /**
     * التعليقات المحققة فقط
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * ترتيب حسب الإعجابات
     */
    public function scopeOrderedByLikes($query)
    {
        return $query->orderBy('likes_count', 'desc');
    }

    /**
     * ترتيب حسب الموقع في الصفحة
     */
    public function scopeOrderedByPosition($query)
    {
        return $query->orderBy('position_start');
    }

    /**
     * التعليقات الحديثة
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * زيادة عداد الإعجابات
     */
    public function incrementLikes()
    {
        $this->increment('likes_count');
    }

    /**
     * تقليل عداد الإعجابات
     */
    public function decrementLikes()
    {
        $this->decrement('likes_count');
    }

    /**
     * الحصول على النص المختصر للتعليق
     */
    public function getShortAnnotationAttribute()
    {
        return str_limit($this->annotation_text, 150);
    }

    /**
     * الحصول على النص المحدد المختصر
     */
    public function getShortHighlightAttribute()
    {
        return str_limit($this->highlighted_text, 100);
    }

    /**
     * تحديد ما إذا كان التعليق طويلاً
     */
    public function getIsLongAttribute()
    {
        return strlen($this->annotation_text) > 300;
    }

    /**
     * تحديد ما إذا كان التعليق شائعاً
     */
    public function getIsPopularAttribute()
    {
        return $this->likes_count >= 10;
    }

    /**
     * الحصول على نوع التعليق باللغة العربية
     */
    public function getAnnotationTypeArabicAttribute()
    {
        $types = [
            'highlight' => 'تمييز',
            'note' => 'ملاحظة',
            'bookmark' => 'إشارة مرجعية',
            'correction' => 'تصحيح',
            'question' => 'سؤال',
            'explanation' => 'شرح'
        ];
        
        return $types[$this->annotation_type] ?? $this->annotation_type;
    }

    /**
     * الحصول على مستوى الرؤية باللغة العربية
     */
    public function getVisibilityArabicAttribute()
    {
        $visibility = [
            'private' => 'خاص',
            'public' => 'عام',
            'shared' => 'مشارك'
        ];
        
        return $visibility[$this->visibility] ?? $this->visibility;
    }

    /**
     * تحديد ما إذا كان بإمكان المستخدم رؤية التعليق
     */
    public function canBeViewedBy($userId)
    {
        if ($this->visibility === 'public') {
            return true;
        }
        
        return $this->user_id === $userId;
    }

    /**
     * تحديد ما إذا كان بإمكان المستخدم تعديل التعليق
     */
    public function canBeEditedBy($userId)
    {
        return $this->user_id === $userId;
    }
}