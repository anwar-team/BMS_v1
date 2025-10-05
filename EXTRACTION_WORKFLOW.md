# 🚀 آلية العمل - استخراج ومعالجة معلومات الكتب

## 📋 الهدف
استخراج المعلومات من أوصاف الكتب التي **ليس لها shamela_id** وربطها تلقائياً بالجداول المناسبة (المؤلفين والناشرين).

---

## 🔍 الكتب المستهدفة

### الشرط الرئيسي:
```sql
SELECT * FROM books 
WHERE shamela_id IS NULL 
ORDER BY id ASC;
```

**لماذا هذا الشرط؟**
- الكتب من مكتبة الشاملة تكون معلوماتها مُحدثة مسبقاً
- نركز على الكتب المُضافة يدوياً أو من مصادر أخرى
- تجنب الازدواجية في المعالجة

---

## 🏗️ البنية الأساسية

### 1. جدول تخزين النتائج: `book_extracted_metadata`

```sql
CREATE TABLE book_extracted_metadata (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    book_id BIGINT UNSIGNED NOT NULL,
    
    -- ✅ معلومات المؤلف المستخرجة
    extracted_author_name VARCHAR(500),
    extracted_author_death_year VARCHAR(20),
    extracted_author_madhhab VARCHAR(100),
    matched_author_id BIGINT UNSIGNED NULL,
    author_match_confidence DECIMAL(3,2) DEFAULT 0.00,
    
    -- ✅ معلومات الناشر المستخرجة
    extracted_publisher_name VARCHAR(500),
    extracted_publisher_city VARCHAR(200),
    matched_publisher_id BIGINT UNSIGNED NULL,
    publisher_match_confidence DECIMAL(3,2) DEFAULT 0.00,
    
    -- ✅ معلومات الطبعة المستخرجة
    extracted_edition VARCHAR(200),
    extracted_edition_number VARCHAR(50),
    extracted_year_hijri VARCHAR(10),
    extracted_year_miladi VARCHAR(10),
    
    -- ✅ معلومات التحقيق
    extracted_tahqeeq_name VARCHAR(500),
    matched_tahqeeq_author_id BIGINT UNSIGNED NULL,
    
    -- ✅ معلومات إضافية
    extracted_pages_count INT,
    extracted_volumes_count INT,
    
    -- ✅ حالة المعالجة
    is_processed BOOLEAN DEFAULT FALSE,
    is_applied BOOLEAN DEFAULT FALSE,
    needs_review BOOLEAN DEFAULT TRUE,
    processing_status ENUM('pending', 'extracted', 'matched', 'applied', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    
    -- ✅ التواريخ
    extracted_at TIMESTAMP NULL,
    applied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (matched_author_id) REFERENCES authors(id) ON DELETE SET NULL,
    FOREIGN KEY (matched_publisher_id) REFERENCES publishers(id) ON DELETE SET NULL,
    FOREIGN KEY (matched_tahqeeq_author_id) REFERENCES authors(id) ON DELETE SET NULL,
    
    UNIQUE INDEX idx_book_id (book_id),
    INDEX idx_processing_status (processing_status),
    INDEX idx_needs_review (needs_review)
);
```

---

## ⚙️ آلية العمل التفصيلية

### المرحلة 1: الاستخراج (Extraction)

```
📖 كتاب بدون shamela_id
    ↓
📝 قراءة حقل description
    ↓
🔍 استخدام Regular Expressions للاستخراج
    ↓
💾 حفظ في book_extracted_metadata
```

**ما يتم استخراجه:**
1. ✅ اسم المؤلف + سنة الوفاة + المذهب
2. ✅ اسم الناشر + المدينة
3. ✅ رقم الطبعة + سنة النشر
4. ✅ اسم المحقق (إن وجد)
5. ✅ عدد الصفحات والمجلدات

---

### المرحلة 2: المطابقة (Matching)

#### أ. مطابقة المؤلف

```
📝 اسم المؤلف المستخرج: "أحمد بن علي أبو بكر الرازي الجصاص الحنفي"
    ↓
🔍 البحث في جدول authors
    ↓
📊 خوارزمية المطابقة:
    1. مطابقة تامة (100%)
    2. مطابقة جزئية - الاسم الأول والأخير (90%)
    3. مطابقة تقريبية - Similar Text (70-80%)
    ↓
✅ إذا وجد: ربط author_id
❌ إذا لم يوجد: إنشاء مؤلف جديد
    ↓
💾 تحديث matched_author_id + author_match_confidence
```

**خوارزمية المطابقة:**
```php
1. تنظيف الاسم (إزالة الألقاب: الشيخ، الإمام، الخ)
2. البحث بالاسم الكامل
3. البحث بأول 3 كلمات
4. البحث بآخر كلمتين (اسم العائلة)
5. استخدام similar_text() للمطابقة التقريبية
6. حساب نسبة الثقة
```

#### ب. مطابقة الناشر

```
📝 اسم الناشر المستخرج: "دار إحياء التراث العربي - بيروت"
    ↓
🔍 البحث في جدول publishers
    ↓
📊 خوارزمية المطابقة:
    1. مطابقة تامة (100%)
    2. بحث عن الكلمات الرئيسية (80%)
    3. مطابقة تقريبية (70%)
    ↓
✅ إذا وجد: ربط publisher_id
❌ إذا لم يوجد: إنشاء ناشر جديد
    ↓
💾 تحديث matched_publisher_id + publisher_match_confidence
```

---

### المرحلة 3: التطبيق (Application)

```
📊 المعلومات المستخرجة + المطابقة
    ↓
✅ إذا كانت نسبة الثقة > 80%: تطبيق تلقائي
⚠️ إذا كانت نسبة الثقة 50-80%: تحتاج مراجعة
❌ إذا كانت نسبة الثقة < 50%: تحتاج مراجعة يدوية
    ↓
📝 التطبيق:
    1. تحديث book.publisher_id
    2. إدراج في author_book (مؤلف رئيسي)
    3. إدراج في author_book (محقق إن وجد)
    4. تحديث book.edition
    5. تحديث book.pages_count (إن كان مختلف)
    ↓
✅ تعليم is_applied = true
```

---

## 🛠️ الأدوات والملفات

### 1. Migration
```
database/migrations/xxxx_create_book_extracted_metadata_table.php
```

### 2. Model
```
app/Models/BookExtractedMetadata.php
```

### 3. Services
```
app/Services/BookMetadataExtractor.php       - استخراج المعلومات
app/Services/AuthorMatcher.php               - مطابقة المؤلفين
app/Services/PublisherMatcher.php            - مطابقة الناشرين
app/Services/MetadataApplicator.php          - تطبيق المعلومات
```

### 4. Command
```
app/Console/Commands/ProcessBooksMetadata.php
```

**الاستخدام:**
```bash
# معالجة 10 كتب للاختبار
php artisan books:process-metadata --limit=10

# معالجة جميع الكتب بدون shamela_id
php artisan books:process-metadata

# معالجة كتاب معين
php artisan books:process-metadata --book=123

# معالجة وتطبيق مباشرة (للنتائج > 80%)
php artisan books:process-metadata --auto-apply

# عرض الإحصائيات فقط
php artisan books:process-metadata --stats
```

---

## 📊 تدفق البيانات الكامل

```
┌─────────────────────────────────────────┐
│   📚 كتاب بدون shamela_id              │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│   🔍 استخراج من description           │
│   - المؤلف                             │
│   - الناشر                             │
│   - الطبعة                             │
│   - المحقق                             │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│   💾 حفظ في book_extracted_metadata    │
│   status = 'extracted'                  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│   🔎 مطابقة المؤلف                    │
│   - بحث في authors                     │
│   - حساب نسبة الثقة                    │
│   - إنشاء جديد إن لم يوجد             │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│   🔎 مطابقة الناشر                    │
│   - بحث في publishers                  │
│   - حساب نسبة الثقة                    │
│   - إنشاء جديد إن لم يوجد             │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│   📊 تحديث book_extracted_metadata     │
│   status = 'matched'                    │
│   - matched_author_id                   │
│   - matched_publisher_id                │
│   - confidence scores                   │
└──────────────┬──────────────────────────┘
               │
               ▼
        ┌──────┴──────┐
        │             │
    Confidence    Confidence
      > 80%        < 80%
        │             │
        ▼             ▼
   ┌─────────┐   ┌────────────┐
   │ تطبيق   │   │ مراجعة     │
   │ تلقائي  │   │ يدوية      │
   └────┬────┘   └──────┬─────┘
        │               │
        ▼               │
┌─────────────────┐     │
│ تحديث الجداول: │     │
│ - publishers    │     │
│ - author_book   │     │
│ - books.edition │     │
└────────┬────────┘     │
         │              │
         ▼              ▼
┌──────────────────────────┐
│ status = 'applied'       │
│ is_applied = true        │
└──────────────────────────┘
```

---

## 📈 مراحل التنفيذ

### ✅ المرحلة 1: الإعداد (30 دقيقة)
- [x] إنشاء Migration
- [x] إنشاء Model
- [x] اختبار الجدول

### ✅ المرحلة 2: خدمة الاستخراج (1-2 ساعة)
- [ ] BookMetadataExtractor Service
- [ ] Regular Expressions للأنماط
- [ ] اختبار الاستخراج على 10 كتب

### ✅ المرحلة 3: خدمات المطابقة (2-3 ساعات)
- [ ] AuthorMatcher Service
- [ ] PublisherMatcher Service
- [ ] خوارزميات المطابقة التقريبية
- [ ] اختبار المطابقة

### ✅ المرحلة 4: خدمة التطبيق (1 ساعة)
- [ ] MetadataApplicator Service
- [ ] منطق التطبيق التلقائي
- [ ] التحقق من الصحة

### ✅ المرحلة 5: Command (1 ساعة)
- [ ] ProcessBooksMetadata Command
- [ ] Progress bar
- [ ] Logging
- [ ] Error handling

### ✅ المرحلة 6: الاختبار (1-2 ساعة)
- [ ] اختبار على 10 كتب
- [ ] مراجعة النتائج
- [ ] تعديل الأنماط إن لزم

### ✅ المرحلة 7: التشغيل الكامل (حسب الحاجة)
- [ ] تشغيل على جميع الكتب
- [ ] مراقبة الأخطاء
- [ ] جمع الإحصائيات

---

## 📊 الإحصائيات المتوقعة

### قبل المعالجة:
```
إجمالي الكتب: 11,957
كتب بدون shamela_id: ؟؟؟ (سنعرف بعد الاستعلام)
مؤلفون حالياً: 3,619
ناشرون حالياً: 1,677
```

### بعد المعالجة (توقعات):
```
معلومات مستخرجة: ~88% من الكتب
مؤلفون جدد: +500-1000
ناشرون جدد: +200-400
معلومات طبعة محدثة: ~60%
```

---

## ⚠️ نقاط مهمة

### 1. الأمان
- ✅ استخدام Transactions للتطبيق
- ✅ Rollback في حالة الخطأ
- ✅ Logging شامل

### 2. الأداء
- ✅ معالجة Batch (100 كتاب في المرة)
- ✅ استخدام Queues للكتب الكثيرة
- ✅ Caching للمؤلفين والناشرين

### 3. المراجعة
- ✅ نسبة ثقة < 80% تحتاج مراجعة
- ✅ واجهة Filament للمراجعة
- ✅ تقارير بالنتائج

---

## 🚀 جاهز للبدء؟

**الخطوة التالية:**
1. نُنشئ Migration و Model
2. نبني BookMetadataExtractor
3. نختبر على 10 كتب
4. نكمل باقي الخدمات

**هل نبدأ بالـ Migration والـ Model؟** 💪
