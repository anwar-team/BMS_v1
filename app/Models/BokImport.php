<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BokImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_filename',
        'file_path',
        'file_size',
        'file_hash',
        'title',
        'author',
        'description',
        'language',
        'volumes_count',
        'chapters_count',
        'pages_count',
        'estimated_words',
        'status',
        'conversion_options',
        'analysis_result',
        'conversion_log',
        'error_message',
        'book_id',
        'is_featured',
        'allow_download',
        'allow_search',
        'is_public',
        'started_at',
        'completed_at',
        'processing_time',
        'backup_path',
        'backup_created',
        'user_id',
        'import_source',
    ];

    protected $casts = [
        'conversion_options' => 'array',
        'analysis_result' => 'array',
        'conversion_log' => 'array',
        'is_featured' => 'boolean',
        'allow_download' => 'boolean',
        'allow_search' => 'boolean',
        'is_public' => 'boolean',
        'backup_created' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'file_size' => 'integer',
        'volumes_count' => 'integer',
        'chapters_count' => 'integer',
        'pages_count' => 'integer',
        'estimated_words' => 'integer',
        'processing_time' => 'integer',
    ];

    // حالات التحويل المتاحة
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    // مصادر الاستيراد
    public const SOURCE_WEB = 'web';
    public const SOURCE_CLI = 'cli';
    public const SOURCE_API = 'api';

    /**
     * العلاقة مع نموذج الكتاب
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * العلاقة مع نموذج المستخدم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * نطاق للحصول على العمليات المكتملة
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * نطاق للحصول على العمليات الفاشلة
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * نطاق للحصول على العمليات قيد المعالجة
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * نطاق للحصول على العمليات في الانتظار
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * نطاق للحصول على العمليات حسب اللغة
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * نطاق للحصول على العمليات حسب المستخدم
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * خاصية محسوبة لحجم الملف بصيغة قابلة للقراءة
     */
    protected function fileSizeFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatBytes($this->file_size)
        );
    }

    /**
     * خاصية محسوبة لوقت المعالجة بصيغة قابلة للقراءة
     */
    protected function processingTimeFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->processing_time ? $this->formatDuration($this->processing_time) : null
        );
    }

    /**
     * خاصية محسوبة لحالة التحويل بالعربية
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->status) {
                self::STATUS_PENDING => 'في الانتظار',
                self::STATUS_PROCESSING => 'قيد المعالجة',
                self::STATUS_COMPLETED => 'مكتمل',
                self::STATUS_FAILED => 'فشل',
                self::STATUS_CANCELLED => 'ملغي',
                default => 'غير معروف'
            }
        );
    }

    /**
     * خاصية محسوبة لنسبة التقدم
     */
    protected function progressPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->status) {
                    self::STATUS_PENDING => 0,
                    self::STATUS_PROCESSING => 50,
                    self::STATUS_COMPLETED => 100,
                    self::STATUS_FAILED => 0,
                    self::STATUS_CANCELLED => 0,
                    default => 0
                };
            }
        );
    }

    /**
     * خاصية محسوبة لرابط تحميل الملف
     */
    protected function downloadUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file_path ? Storage::url($this->file_path) : null
        );
    }

    /**
     * التحقق من إمكانية إعادة المحاولة
     */
    public function canRetry(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_CANCELLED]);
    }

    /**
     * التحقق من إمكانية الإلغاء
     */
    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * التحقق من اكتمال التحويل
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * التحقق من فشل التحويل
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * التحقق من كون التحويل قيد المعالجة
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * بدء عملية التحويل
     */
    public function startProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now(),
        ]);
    }

    /**
     * إنهاء عملية التحويل بنجاح
     */
    public function markAsCompleted(int $bookId = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'processing_time' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
            'book_id' => $bookId,
        ]);
    }

    /**
     * إنهاء عملية التحويل بفشل
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'processing_time' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * إلغاء عملية التحويل
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now(),
            'processing_time' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
        ]);
    }

    /**
     * إضافة سجل إلى سجل التحويل
     */
    public function addLog(string $message, string $level = 'info'): void
    {
        $logs = $this->conversion_log ?? [];
        $logs[] = [
            'timestamp' => now()->toISOString(),
            'level' => $level,
            'message' => $message,
        ];
        
        $this->update(['conversion_log' => $logs]);
    }

    /**
     * تحديث نتيجة التحليل
     */
    public function updateAnalysisResult(array $result): void
    {
        $this->update(['analysis_result' => $result]);
    }

    /**
     * تحديث خيارات التحويل
     */
    public function updateConversionOptions(array $options): void
    {
        $this->update(['conversion_options' => $options]);
    }

    /**
     * تنسيق حجم الملف
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * تنسيق مدة الوقت
     */
    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' ثانية';
        } elseif ($seconds < 3600) {
            return round($seconds / 60, 1) . ' دقيقة';
        } else {
            return round($seconds / 3600, 1) . ' ساعة';
        }
    }

    /**
     * الحصول على آخر رسالة سجل
     */
    public function getLastLogMessage(): ?string
    {
        $logs = $this->conversion_log ?? [];
        return !empty($logs) ? end($logs)['message'] : null;
    }

    /**
     * الحصول على عدد رسائل الخطأ في السجل
     */
    public function getErrorCount(): int
    {
        $logs = $this->conversion_log ?? [];
        return count(array_filter($logs, fn($log) => $log['level'] === 'error'));
    }

    /**
     * الحصول على عدد رسائل التحذير في السجل
     */
    public function getWarningCount(): int
    {
        $logs = $this->conversion_log ?? [];
        return count(array_filter($logs, fn($log) => $log['level'] === 'warning'));
    }
}