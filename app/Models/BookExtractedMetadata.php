<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookExtractedMetadata extends Model
{
    use HasFactory;

    protected $table = 'book_extracted_metadata';

    protected $fillable = [
        'book_id',
        // معلومات القسم (الأولوية الأولى)
        'extracted_section_name',
        'matched_section_id',
        'section_match_confidence',
        // معلومات المؤلف
        'extracted_author_name',
        'extracted_author_death_year',
        'extracted_author_madhhab',
        'matched_author_id',
        'author_match_confidence',
        // معلومات الناشر
        'extracted_publisher_name',
        'extracted_publisher_city',
        'matched_publisher_id',
        'publisher_match_confidence',
        // معلومات الطبعة
        'extracted_edition',
        'extracted_edition_number',
        'extracted_year_hijri',
        'extracted_year_miladi',
        // معلومات التحقيق
        'extracted_tahqeeq_name',
        'matched_tahqeeq_author_id',
        // معلومات إضافية
        'extracted_pages_count',
        'extracted_volumes_count',
        // حالة المعالجة
        'is_processed',
        'is_applied',
        'needs_review',
        'processing_status',
        'error_message',
        'extracted_at',
        'applied_at',
    ];

    protected $casts = [
        'is_processed' => 'boolean',
        'is_applied' => 'boolean',
        'needs_review' => 'boolean',
        'section_match_confidence' => 'decimal:2',
        'author_match_confidence' => 'decimal:2',
        'publisher_match_confidence' => 'decimal:2',
        'extracted_pages_count' => 'integer',
        'extracted_volumes_count' => 'integer',
        'extracted_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    /**
     * العلاقة مع الكتاب
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * العلاقة مع القسم المطابق
     */
    public function matchedSection(): BelongsTo
    {
        return $this->belongsTo(BookSection::class, 'matched_section_id');
    }

    /**
     * العلاقة مع المؤلف المطابق
     */
    public function matchedAuthor(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'matched_author_id');
    }

    /**
     * العلاقة مع الناشر المطابق
     */
    public function matchedPublisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class, 'matched_publisher_id');
    }

    /**
     * العلاقة مع المحقق المطابق
     */
    public function matchedTahqeeqAuthor(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'matched_tahqeeq_author_id');
    }

    /**
     * Scope للبيانات التي تحتاج مراجعة
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('needs_review', true);
    }

    /**
     * Scope للبيانات المطبقة
     */
    public function scopeApplied($query)
    {
        return $query->where('is_applied', true);
    }

    /**
     * Scope للبيانات غير المطبقة
     */
    public function scopeNotApplied($query)
    {
        return $query->where('is_applied', false);
    }

    /**
     * Scope حسب حالة المعالجة
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('processing_status', $status);
    }

    /**
     * Scope للبيانات عالية الثقة (يمكن تطبيقها تلقائياً)
     */
    public function scopeHighConfidence($query)
    {
        return $query->where(function($q) {
            $q->where('section_match_confidence', '>=', 0.80)
              ->orWhere('author_match_confidence', '>=', 0.80)
              ->orWhere('publisher_match_confidence', '>=', 0.80);
        });
    }

    /**
     * التحقق من إمكانية التطبيق التلقائي
     */
    public function canAutoApply(): bool
    {
        return ($this->section_match_confidence >= 0.80 
                || $this->author_match_confidence >= 0.80 
                || $this->publisher_match_confidence >= 0.80)
            && !$this->is_applied
            && $this->processing_status === 'matched';
    }

    /**
     * الحصول على نسبة الثقة الإجمالية
     */
    public function getOverallConfidenceAttribute(): float
    {
        $scores = array_filter([
            $this->section_match_confidence,
            $this->author_match_confidence,
            $this->publisher_match_confidence,
        ]);

        return count($scores) > 0 ? array_sum($scores) / count($scores) : 0.00;
    }
}
