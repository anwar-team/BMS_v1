# 🔌 Feedback System API Documentation

## Base URL
```
https://yourdomain.com
```

---

## 📝 Endpoints

### 1. Store Feedback/Complaint (Public)

**Endpoint:** `POST /feedback`

**Description:** Submit a new feedback or complaint message (anonymous, no authentication required)

**Headers:**
```http
Content-Type: application/x-www-form-urlencoded
X-CSRF-TOKEN: {csrf_token}
Accept: application/json
```

**Request Body:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `type` | string | Yes | Message type: `feedback` or `complaint` |
| `subject` | string | Yes | Subject (min: 3 chars, max: 255 chars) |
| `message` | text | Yes | Message content (min: 10 chars) |

**Example Request:**
```javascript
fetch('/feedback', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
        type: 'feedback',
        subject: 'تحسين واجهة البحث',
        message: 'أقترح إضافة خاصية البحث الصوتي لتسهيل عملية البحث في الكتب'
    })
});
```

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "تم إرسال رسالتك بنجاح! شكراً لك على ملاحظاتك.",
    "data": {
        "id": 123,
        "type": "feedback",
        "subject": "تحسين واجهة البحث",
        "message": "أقترح إضافة خاصية البحث الصوتي...",
        "status": "pending",
        "priority": "medium",
        "ip_address": "192.168.1.1",
        "created_at": "2025-10-09T12:00:00.000000Z",
        "updated_at": "2025-10-09T12:00:00.000000Z"
    }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "errors": {
        "type": ["يرجى اختيار نوع الرسالة"],
        "subject": ["يرجى إدخال الموضوع"],
        "message": ["الرسالة يجب أن تكون 10 أحرف على الأقل"]
    }
}
```

---

### 2. List Feedbacks (Admin Only)

**Endpoint:** `GET /admin/feedback`

**Description:** Get list of all feedbacks with filtering and pagination

**Authentication:** Required (Admin)

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `type` | string | No | Filter by type: `feedback` or `complaint` |
| `status` | string | No | Filter by status: `pending`, `in_progress`, `resolved` |
| `priority` | string | No | Filter by priority: `low`, `medium`, `high` |
| `search` | string | No | Search in subject and message |
| `page` | integer | No | Page number (default: 1) |

**Example Request:**
```
GET /admin/feedback?status=pending&type=complaint&search=خطأ&page=1
```

**Response:** HTML page with feedbacks table and statistics

---

### 3. Show Feedback Details (Admin Only)

**Endpoint:** `GET /admin/feedback/{id}`

**Description:** Get detailed information about a specific feedback

**Authentication:** Required (Admin)

**Response:** HTML page with full feedback details

---

### 4. Update Feedback (Admin Only)

**Endpoint:** `PUT /admin/feedback/{id}`

**Description:** Update feedback status, priority, and admin notes

**Authentication:** Required (Admin)

**Headers:**
```http
Content-Type: application/x-www-form-urlencoded
X-CSRF-TOKEN: {csrf_token}
Accept: application/json
```

**Request Body:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | Yes | Status: `pending`, `in_progress`, `resolved` |
| `priority` | string | Yes | Priority: `low`, `medium`, `high` |
| `admin_notes` | text | No | Admin notes/comments |

**Example Request:**
```javascript
fetch('/admin/feedback/123', {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        status: 'resolved',
        priority: 'high',
        admin_notes: 'تم إصلاح المشكلة في آخر تحديث'
    })
});
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "تم تحديث البيانات بنجاح",
    "data": {
        "id": 123,
        "status": "resolved",
        "priority": "high",
        "admin_notes": "تم إصلاح المشكلة في آخر تحديث",
        "updated_at": "2025-10-09T12:30:00.000000Z"
    }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "errors": {
        "status": ["الحالة المحددة غير صحيحة"],
        "priority": ["الأولوية المحددة غير صحيحة"]
    }
}
```

---

### 5. Delete Feedback (Admin Only)

**Endpoint:** `DELETE /admin/feedback/{id}`

**Description:** Permanently delete a feedback

**Authentication:** Required (Admin)

**Headers:**
```http
X-CSRF-TOKEN: {csrf_token}
```

**Success Response:** Redirect to feedback list with success message

---

## 📊 Data Models

### FeedbackComplaint Model

```php
{
    "id": 123,
    "name": null,                          // Optional
    "email": null,                         // Optional
    "type": "feedback",                    // Enum: feedback, complaint
    "subject": "تحسين واجهة البحث",
    "message": "أقترح إضافة خاصية...",
    "status": "pending",                   // Enum: pending, in_progress, resolved
    "priority": "medium",                  // Enum: low, medium, high
    "admin_notes": null,                   // Optional
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "created_at": "2025-10-09T12:00:00.000000Z",
    "updated_at": "2025-10-09T12:00:00.000000Z",
    
    // Computed Attributes
    "type_icon": "⭐",                      // ⭐ for feedback, ⚠️ for complaint
    "type_arabic": "ملاحظة",               // Arabic translation
    "status_arabic": "معلقة",              // Arabic translation
    "priority_arabic": "متوسطة",           // Arabic translation
    "type_badge": "bg-green-100 text-green-800",
    "status_badge": "bg-yellow-100 text-yellow-800",
    "priority_badge": "bg-yellow-100 text-yellow-800"
}
```

---

## 🔒 Authentication & Authorization

### Public Endpoints:
- `POST /feedback` - No authentication required

### Admin Endpoints:
- `GET /admin/feedback` - Requires authentication
- `GET /admin/feedback/{id}` - Requires authentication
- `PUT /admin/feedback/{id}` - Requires authentication
- `DELETE /admin/feedback/{id}` - Requires authentication

**Middleware:** `auth`

---

## ⚠️ Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created successfully |
| 401 | Unauthorized (not logged in) |
| 403 | Forbidden (insufficient permissions) |
| 404 | Not found |
| 422 | Validation error |
| 500 | Server error |

---

## 📝 Validation Rules

### Store Feedback:
```php
[
    'type' => 'required|in:feedback,complaint',
    'subject' => 'required|string|min:3|max:255',
    'message' => 'required|string|min:10'
]
```

### Update Feedback:
```php
[
    'status' => 'required|in:pending,in_progress,resolved',
    'priority' => 'required|in:low,medium,high',
    'admin_notes' => 'nullable|string'
]
```

---

## 🧪 Testing Examples

### cURL Example:
```bash
# Store feedback
curl -X POST https://yourdomain.com/feedback \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -H "Accept: application/json" \
  -d '{
    "type": "feedback",
    "subject": "اقتراح تحسين",
    "message": "أقترح إضافة خاصية البحث المتقدم"
  }'

# Update feedback (Admin)
curl -X PUT https://yourdomain.com/admin/feedback/123 \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -H "Cookie: laravel_session=your-session" \
  -d '{
    "status": "resolved",
    "priority": "medium",
    "admin_notes": "تم التنفيذ"
  }'
```

### JavaScript/Fetch Example:
```javascript
// Store feedback
async function submitFeedback(data) {
    const response = await fetch('/feedback', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    });
    
    return await response.json();
}

// Usage
submitFeedback({
    type: 'feedback',
    subject: 'اقتراح',
    message: 'رسالة الاقتراح هنا...'
}).then(result => {
    if (result.success) {
        console.log('Success:', result.message);
    } else {
        console.error('Errors:', result.errors);
    }
});
```

---

## 📊 Statistics Endpoint (Future Enhancement)

**Proposed:** `GET /api/feedback/stats`

**Response:**
```json
{
    "total": 150,
    "pending": 25,
    "in_progress": 10,
    "resolved": 115,
    "feedbacks": 100,
    "complaints": 50,
    "by_priority": {
        "low": 50,
        "medium": 80,
        "high": 20
    }
}
```

---

## 🔐 Rate Limiting

To prevent spam, consider implementing rate limiting:

```php
// In routes/web.php
Route::post('/feedback', [FeedbackComplaintController::class, 'store'])
    ->middleware('throttle:5,1'); // 5 requests per minute
```

---

## 📚 Additional Resources

- **User Guide**: `FEEDBACK_SYSTEM_USER_GUIDE.md`
- **Completion Report**: `FEEDBACK_SYSTEM_COMPLETION_REPORT.md`
- **Project Plan**: `FEEDBACK_SYSTEM_PLAN.md`

---

**Version**: 1.0.0  
**Last Updated**: October 9, 2025  
**Status**: Production Ready ✅
