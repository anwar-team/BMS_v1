# 🚀 دليل استخدام نظام استخراج البيانات الوصفية

**الإصدار:** 1.0  
**التاريخ:** 5 أكتوبر 2025  
**الحالة:** ✅ جاهز للإنتاج

---

## 📋 المحتويات

1. [نظرة عامة](#نظرة-عامة)
2. [الأوامر المتاحة](#الأوامر-المتاحة)
3. [أمثلة الاستخدام](#أمثلة-الاستخدام)
4. [فهم النتائج](#فهم-النتائج)
5. [المراجعة اليدوية](#المراجعة-اليدوية)
6. [الأسئلة الشائعة](#الأسئلة-شائعة)

---

## 🎯 نظرة عامة

نظام متكامل لاستخراج البيانات الوصفية من حقل `description` في جدول الكتب:

### ما يتم استخراجه:

| البيان | معدل النجاح | الثقة العالية (≥80%) |
|--------|-------------|---------------------|
| **المؤلف** | 90-100% | 50-70% |
| **الناشر** | 60-80% | 50-70% |
| **القسم** | N/A | N/A (غير متوفر في الأوصاف) |

### الخدمات المستخدمة:

1. **`AuthorExtractor`** - استخراج ومطابقة المؤلفين
2. **`PublisherExtractor`** - استخراج ومطابقة الناشرين  
3. **`BookSectionMatcher`** - استخراج ومطابقة الأقسام (جاهز للمستقبل)

---

## 💻 الأوامر المتاحة

### الأمر الرئيسي

```bash
php artisan books:process-metadata [OPTIONS]
```

### الخيارات (Options)

| الخيار | الوصف | القيمة الافتراضية |
|--------|-------|-------------------|
| `--limit=N` | عدد الكتب المراد معالجتها | 100 |
| `--batch-size=N` | عدد الكتب في كل دفعة | 10 |
| `--force` | إعادة معالجة الكتب المعالجة سابقاً | false |
| `--apply` | التطبيق التلقائي للبيانات عالية الثقة (≥80%) | false |
| `--book-id=ID` | معالجة كتاب محدد بـ ID | - |

---

## 📚 أمثلة الاستخدام

### 1. معالجة 10 كتب (اختبار)

```bash
php artisan books:process-metadata --limit=10
```

**النتيجة:**
- يستخرج المؤلف والناشر من الأوصاف
- يحفظ البيانات في `book_extracted_metadata`
- **لا يطبق** التغييرات على جدول `books`

---

### 2. معالجة مع التطبيق التلقائي

```bash
php artisan books:process-metadata --limit=50 --apply
```

**النتيجة:**
- يستخرج البيانات
- **يطبق تلقائياً** البيانات ذات الثقة ≥80%
- يحدث `author_id` و `publisher_id` في جدول `books`

**مثال التطبيق:**
```php
// إذا author_match_confidence >= 0.80
$book->author_id = $metadata->matched_author_id;
$book->save();
```

---

### 3. معالجة كتاب محدد

```bash
php artisan books:process-metadata --book-id=123 --apply
```

**الاستخدام:**
- لاختبار كتاب معين
- لإعادة معالجة كتاب بعد تحديث الوصف

---

### 4. إعادة معالجة كتب معالجة سابقاً

```bash
php artisan books:process-metadata --limit=100 --force
```

**متى تستخدمه:**
- بعد تحسين خوارزميات المطابقة
- بعد تحديث قاعدة بيانات المؤلفين/الناشرين
- لإعادة المعالجة الكاملة

---

### 5. معالجة جميع الكتب (الإنتاج)

```bash
# معالجة 1000 كتاب في المرة
php artisan books:process-metadata --limit=1000 --apply

# أو معالجة جميع الـ 11,957 كتاب
php artisan books:process-metadata --limit=12000 --apply
```

**تحذير:** ⚠️ قد يستغرق وقتاً طويلاً (30-60 دقيقة)

---

## 📊 فهم النتائج

### مثال مخرجات الأمر

```
🚀 بدء معالجة البيانات الوصفية للكتب...

📚 عدد الكتب: 10

 10/10 [============================] 100%

📊 النتائج النهائية:
═══════════════════════════════════════

+--------------------+-------+--------+
| المقياس            | العدد | النسبة |
+--------------------+-------+--------+
| إجمالي الكتب       | 10    | 100%   |
| معالج بنجاح        | 10    | 100%   |
| متجاوز             | 0     | 0%     |
|                    |       |        |
| أقسام مستخرجة      | 0     | 0%     |
| مؤلفين مستخرجين    | 10    | 100%   |
| ناشرين مستخرجين    | 6     | 60%    |
|                    |       |        |
| ثقة عالية (≥80%)   | 7     | 70%    |
| تم التطبيق تلقائياً | 5     | 50%    |
| يحتاج مراجعة       | 3     | 30%    |
| أخطاء              | 0     | 0%     |
+--------------------+-------+--------+

✅ معدل استخراج المؤلفين ممتاز!
✅ معدل الثقة العالية جيد جداً!

⚠️  3 كتاب يحتاج مراجعة يدوية.

✨ تمت المعالجة بنجاح!
```

### شرح المقاييس

| المقياس | المعنى |
|---------|--------|
| **إجمالي الكتب** | عدد الكتب المستهدفة للمعالجة |
| **معالج بنجاح** | عدد الكتب التي تمت معالجتها بدون أخطاء |
| **متجاوز** | كتب تم تجاوزها (معالجة سابقاً بدون `--force`) |
| **أقسام مستخرجة** | عدد الأقسام المطابقة بنجاح |
| **مؤلفين مستخرجين** | عدد المؤلفين المطابقين بنجاح |
| **ناشرين مستخرجين** | عدد الناشرين المطابقين بنجاح |
| **ثقة عالية** | بيانات بثقة ≥80% (قابلة للتطبيق التلقائي) |
| **تم التطبيق تلقائياً** | بيانات تم تطبيقها على جدول `books` |
| **يحتاج مراجعة** | بيانات بثقة < 80% أو بدون مطابقة |
| **أخطاء** | عدد الأخطاء البرمجية أثناء المعالجة |

---

## 🔍 المراجعة اليدوية

### الكتب التي تحتاج مراجعة

الكتب ذات `needs_review = true` تحتاج مراجعة في الحالات التالية:

1. **ثقة منخفضة** (< 80%)
   ```
   author_match_confidence = 0.75
   ```

2. **عدم وجود مطابقة**
   ```
   extracted_author_name = "مؤلف جديد"
   matched_author_id = NULL
   ```

3. **مطابقة غامضة**
   ```
   match_type = "similar_text"
   similarity_score = 72%
   ```

### الاستعلام عن البيانات

```php
// الكتب التي تحتاج مراجعة
$needsReview = BookExtractedMetadata::needsReview()->get();

// الكتب عالية الثقة غير المطبقة
$highConfidence = BookExtractedMetadata::highConfidence()
    ->notApplied()
    ->get();

// البيانات المطبقة
$applied = BookExtractedMetadata::applied()->get();
```

### SQL مباشر

```sql
-- الكتب التي تحتاج مراجعة
SELECT 
    bem.id,
    b.title,
    bem.extracted_author_name,
    a.full_name as matched_author,
    bem.author_match_confidence,
    bem.processing_status
FROM book_extracted_metadata bem
JOIN books b ON b.id = bem.book_id
LEFT JOIN authors a ON a.id = bem.matched_author_id
WHERE bem.needs_review = 1
ORDER BY bem.author_match_confidence DESC;
```

---

## ❓ الأسئلة الشائعة

### 1. هل يمكن التراجع عن التطبيق التلقائي?

نعم، يمكنك:
```sql
UPDATE books 
SET author_id = NULL, publisher_id = NULL 
WHERE id IN (SELECT book_id FROM book_extracted_metadata WHERE is_applied = 1);

UPDATE book_extracted_metadata 
SET is_applied = 0, applied_at = NULL;
```

### 2. كيف أحسّن دقة المطابقة؟

1. **تحديث قاعدة بيانات المؤلفين/الناشرين**
   - أضف أسماء بديلة للمؤلفين
   - حدّث أسماء الناشرين لتكون أقصر

2. **تحسين تنظيف النصوص**
   - أضف المزيد من العبارات الشائعة للإزالة
   - حسّن regex patterns

3. **خفض عتبة الثقة** (غير موصى به)
   ```php
   // في AuthorExtractor.php
   if ($bestScore > 60) { // كان 70
       // ...
   }
   ```

### 3. ماذا أفعل مع البيانات غير المطابقة؟

**الخيار 1: إنشاء سجلات جديدة**
```php
$metadata = BookExtractedMetadata::where('matched_author_id', null)->first();

$newAuthor = Author::create([
    'full_name' => $metadata->extracted_author_name,
    'biography' => 'تم إنشاؤه من الاستخراج التلقائي',
]);

$metadata->matched_author_id = $newAuthor->id;
$metadata->author_match_confidence = 1.00;
$metadata->save();
```

**الخيار 2: مطابقة يدوية**
```php
// في واجهة Filament أو admin panel
$metadata->matched_author_id = 123; // ID المؤلف الصحيح
$metadata->author_match_confidence = 1.00;
$metadata->needs_review = false;
$metadata->save();
```

### 4. كم يستغرق معالجة 11,957 كتاب؟

**التقدير:**
- معدل المعالجة: ~200 كتاب/دقيقة
- الوقت الإجمالي: ~60 دقيقة
- يعتمد على أداء السيرفر وعدد المؤلفين/الناشرين

**نصيحة:** قسّم المعالجة:
```bash
# دفعة 1
php artisan books:process-metadata --limit=3000 --apply

# دفعة 2
php artisan books:process-metadata --limit=3000 --apply

# ... وهكذا
```

### 5. هل يمكن استخدام Queues؟

نعم! يمكن تحسين الأمر ليستخدم Laravel Queues:

```php
// في ProcessBooksMetadata.php
foreach ($books->chunk(100) as $chunk) {
    ProcessBookMetadataJob::dispatch($chunk);
}
```

**مزايا:**
- معالجة متوازية
- لا يتعطل إذا حدث خطأ
- يمكن متابعة التقدم

---

## 📈 الإحصائيات المتوقعة

بناءً على الاختبارات:

| المقياس | التوقع |
|---------|--------|
| استخراج المؤلف | 90-95% |
| استخراج الناشر | 60-80% |
| استخراج القسم | 0% (غير متوفر) |
| مطابقة المؤلف | 70-80% |
| مطابقة الناشر | 50-70% |
| ثقة عالية (≥80%) | 60-70% |
| تطبيق تلقائي ناجح | 50-60% |

**النتيجة النهائية:**
- ~6,000-7,000 كتاب ستُربط بالمؤلف تلقائياً
- ~5,000-6,000 كتاب ستُربط بالناشر تلقائياً
- ~4,000-5,000 كتاب يحتاج مراجعة يدوية

---

## 🛠️ الصيانة والتحسين

### مراقبة الأداء

```sql
-- متوسط الثقة
SELECT 
    AVG(author_match_confidence) as avg_author_confidence,
    AVG(publisher_match_confidence) as avg_publisher_confidence
FROM book_extracted_metadata;

-- توزيع حالة المعالجة
SELECT 
    processing_status,
    COUNT(*) as count
FROM book_extracted_metadata
GROUP BY processing_status;
```

### تنظيف البيانات القديمة

```sql
-- حذف بيانات منخفضة الثقة جداً
DELETE FROM book_extracted_metadata 
WHERE author_match_confidence < 0.50 
AND publisher_match_confidence < 0.50
AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

---

## 📞 الدعم

للمساعدة أو الإبلاغ عن مشاكل:
1. راجع ملف `METADATA_EXTRACTION_FINAL_REPORT.md`
2. تحقق من سجل الأخطاء: `storage/logs/laravel.log`
3. تواصل مع فريق التطوير

---

**آخر تحديث:** 5 أكتوبر 2025  
**الإصدار:** 1.0.0  
**المطور:** نظام BMS v1
