<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackComplaint extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feedback_complaints';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'type',
        'subject',
        'message',
        'status',
        'priority',
        'admin_notes',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to only include pending items.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include in progress items.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include resolved items.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope a query to only include feedback.
     */
    public function scopeFeedback($query)
    {
        return $query->where('type', 'feedback');
    }

    /**
     * Scope a query to only include complaints.
     */
    public function scopeComplaint($query)
    {
        return $query->where('type', 'complaint');
    }

    /**
     * Scope a query to filter by priority.
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get the badge color for status.
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'resolved' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the badge color for priority.
     */
    public function getPriorityBadgeAttribute()
    {
        return match($this->priority) {
            'low' => 'bg-gray-100 text-gray-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'high' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the badge color for type.
     */
    public function getTypeBadgeAttribute()
    {
        return match($this->type) {
            'feedback' => 'bg-green-100 text-green-800',
            'complaint' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the icon for type.
     */
    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'feedback' => '⭐',
            'complaint' => '⚠️',
            default => '📝',
        };
    }

    /**
     * Get status in Arabic.
     */
    public function getStatusArabicAttribute()
    {
        return match($this->status) {
            'pending' => 'معلقة',
            'in_progress' => 'قيد المعالجة',
            'resolved' => 'محلولة',
            default => 'غير معروف',
        };
    }

    /**
     * Get priority in Arabic.
     */
    public function getPriorityArabicAttribute()
    {
        return match($this->priority) {
            'low' => 'منخفضة',
            'medium' => 'متوسطة',
            'high' => 'عالية',
            default => 'غير معروف',
        };
    }

    /**
     * Get type in Arabic.
     */
    public function getTypeArabicAttribute()
    {
        return match($this->type) {
            'feedback' => 'ملاحظة',
            'complaint' => 'شكوى',
            default => 'غير معروف',
        };
    }
}
