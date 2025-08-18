# دليل السكربت المحسن للمكتبة الشاملة

## Enhanced Shamela Scraper Guide

### نظرة عامة

هذا السكربت المحسن يحل جميع المشاكل المطروحة في استخراج البيانات من المكتبة الشاملة ويقدم ميزات متقدمة جديدة.

### المشاكل المحلولة

1. **✅ تاريخ الإصدار والتحويل للهجري**
   - استخراج تاريخ الإصدار تلقائيًا
   - تحويل التاريخ الميلادي للهجري
   - دعم التواريخ المختلطة (هجرية/ميلادية)

2. **✅ رقم الطبعة كقيمة عددية**
   - استخراج رقم الطبعة من النصوص العربية
   - تحويل "الطبعة الثانية" إلى 2
   - دعم الأنماط المختلفة (ط1، الطبعة 1، إلخ)

3. **✅ معالجة الناشر المحسنة**
   - جدول منفصل للناشرين
   - تجنب التكرار
   - استخراج معلومات إضافية (الموقع)

4. **✅ ترقيم الصفحات الأصلي**
   - اكتشاف عبارة "ترقيم الكتاب موافق للمطبوع"
   - استخراج الترقيم الأصلي من المحتوى
   - حفظ الترقيم الأصلي مع الترقيم التسلسلي

5. **✅ معالجة أقسام الكتب**
   - جدول منفصل لأقسام الكتب
   - ربط تلقائي مع تجنب التكرار

6. **✅ بطاقة الكتاب الكاملة**
   - استخراج المحتوى الكامل لبطاقة الكتاب
   - إزالة أجزاء المشاركة تلقائيًا
   - حفظ في حقل `description`

7. **✅ الكتب متعددة المجلدات**
   - استخراج روابط المجلدات تلقائيًا
   - جدول منفصل للروابط
   - معالجة النطاقات الصحيحة

8. **✅ ترتيب الفهرس المحسن**
   - ترقيم تسلسلي للفصول
   - دعم التسلسل الهرمي
   - حقل `order` للترتيب الصحيح

### الملفات الجديدة

1. **enhanced_shamela_scraper.py** - السكربت الأساسي المحسن
2. **enhanced_database_manager.py** - مدير قاعدة البيانات المحسن
3. **enhanced_runner.py** - سكربت التشغيل الموحد
4. **enhanced_requirements.txt** - متطلبات محدثة

### التثبيت والإعداد

#### 1. تثبيت المتطلبات

```bash
pip install -r enhanced_requirements.txt
```

#### 2. إعداد قاعدة البيانات

```bash
python enhanced_runner.py create-tables --db-host localhost --db-user root --db-password yourpass --db-name bms
```

### طرق الاستخدام

#### 1. استخراج كتاب واحد فقط

```bash
python enhanced_runner.py extract 12106
```

#### 2. استخراج كتاب مع حد أقصى للصفحات

```bash
python enhanced_runner.py extract 12106 --max-pages 100
```

#### 3. استخراج كتاب وحفظه في قاعدة البيانات

```bash
python enhanced_runner.py extract 12106 --db-host localhost --db-user root --db-password secret --db-name bms
```

#### 4. حفظ ملف JSON موجود في قاعدة البيانات

```bash
python enhanced_runner.py save-db enhanced_book_12106_20250814_123456.json --db-host localhost --db-user root --db-password secret --db-name bms
```

#### 5. عرض إحصائيات كتاب من قاعدة البيانات

```bash
python enhanced_runner.py stats 123 --db-host localhost --db-user root --db-password secret --db-name bms
```

### هيكل قاعدة البيانات المحسن

#### الجداول الجديدة/المحدثة

1. **publishers** - جدول الناشرين

   ```sql
   CREATE TABLE publishers (
       id BIGINT PRIMARY KEY AUTO_INCREMENT,
       name VARCHAR(255) NOT NULL,
       slug VARCHAR(255) UNIQUE,
       location VARCHAR(255),
       description TEXT,
       created_at TIMESTAMP,
       updated_at TIMESTAMP
   );
   ```

2. **book_sections** - جدول أقسام الكتب

   ```sql
   CREATE TABLE book_sections (
       id BIGINT PRIMARY KEY AUTO_INCREMENT,
       name VARCHAR(255) NOT NULL,
       slug VARCHAR(255) UNIQUE,
       parent_id BIGINT,
       description TEXT,
       created_at TIMESTAMP,
       updated_at TIMESTAMP
   );
   ```

3. **books** - جدول الكتب المحسن

   ```sql
   CREATE TABLE books (
       id BIGINT PRIMARY KEY AUTO_INCREMENT,
       title VARCHAR(500) NOT NULL,
       slug VARCHAR(255) UNIQUE,
       shamela_id VARCHAR(50) UNIQUE NOT NULL,
       publisher_id BIGINT REFERENCES publishers(id),
       book_section_id BIGINT REFERENCES book_sections(id),
       edition VARCHAR(255),
       edition_number INT,
       publication_year INT,
       edition_date_hijri VARCHAR(50),
       pages_count INT,
       volumes_count INT,
       description LONGTEXT,
       has_original_pagination BOOLEAN DEFAULT FALSE,
       created_at TIMESTAMP,
       updated_at TIMESTAMP
   );
   ```

4. **volume_links** - روابط المجلدات

   ```sql
   CREATE TABLE volume_links (
       id BIGINT PRIMARY KEY AUTO_INCREMENT,
       book_id BIGINT REFERENCES books(id),
       volume_number INT,
       title VARCHAR(255),
       url VARCHAR(500),
       page_start INT,
       page_end INT,
       created_at TIMESTAMP,
       updated_at TIMESTAMP
   );
   ```

5. **chapters** - الفصول المحسنة

   ```sql
   CREATE TABLE chapters (
       id BIGINT PRIMARY KEY AUTO_INCREMENT,
       volume_id BIGINT REFERENCES volumes(id),
       book_id BIGINT REFERENCES books(id),
       title VARCHAR(255) NOT NULL,
       parent_id BIGINT REFERENCES chapters(id),
       order_number INT DEFAULT 0,
       page_start INT,
       page_end INT,
       level INT DEFAULT 0,
       chapter_type ENUM('main', 'sub') DEFAULT 'main',
       created_at TIMESTAMP,
       updated_at TIMESTAMP
   );
   ```

6. **pages** - الصفحات المحسنة

   ```sql
   CREATE TABLE pages (
       id BIGINT PRIMARY KEY AUTO_INCREMENT,
       book_id BIGINT REFERENCES books(id),
       volume_id BIGINT REFERENCES volumes(id),
       chapter_id BIGINT REFERENCES chapters(id),
       page_number INT NOT NULL,
       original_page_number INT,
       content LONGTEXT,
       html_content LONGTEXT,
       word_count INT,
       created_at TIMESTAMP,
       updated_at TIMESTAMP
   );
   ```

### الميزات الجديدة

#### 1. التحويل التلقائي للتواريخ

- يحول السنوات الميلادية للهجرية تلقائيًا
- يدعم التواريخ المختلطة في النص
- يحفظ كلا التاريخين

#### 2. معالجة الطبعات الذكية

- يستخرج رقم الطبعة من النصوص العربية
- يحول "الطبعة الثانية" إلى 2
- يدعم الأنماط المختلفة

#### 3. الترقيم الأصلي

- يكتشف تلقائيًا إذا كان الكتاب يستخدم ترقيمًا أصليًا
- يستخرج أرقام الصفحات الأصلية من المحتوى
- يحتفظ بالترقيم التسلسلي والأصلي

#### 4. الروابط متعددة المجلدات

- يستخرج روابط المجلدات تلقائيًا
- يحدد نطاقات الصفحات لكل مجلد
- يحفظها في جدول منفصل

#### 5. الترتيب المحسن للفهارس

- ترقيم تسلسلي هرمي للفصول
- دعم المستويات المتعددة
- ترتيب صحيح في قاعدة البيانات

### أمثلة النتائج

#### مثال على كتاب بترقيم أصلي

```json
{
  "title": "موقف الإمام والمأموم",
  "shamela_id": "12106",
  "publisher": {
    "name": "المراقبة الثقافية، إدارة المساجد",
    "location": "الكويت"
  },
  "edition": "الطبعة الثانية",
  "edition_number": 2,
  "publication_year": 1425,
  "edition_date_hijri": "1425",
  "has_original_pagination": true,
  "pages": [
    {
      "page_number": 1,
      "original_page_number": 7,
      "content": "...",
      "word_count": 245
    }
  ]
}
```

#### مثال على فهرس مرتب

```json
{
  "index": [
    {
      "title": "الباب الأول",
      "order": 1000,
      "page_number": 15,
      "level": 0,
      "chapter_type": "main",
      "children": [
        {
          "title": "الفصل الأول",
          "order": 1001,
          "page_number": 16,
          "level": 1,
          "chapter_type": "sub"
        }
      ]
    }
  ]
}
```

### استكشاف الأخطاء

#### 1. خطأ في الاتصال بقاعدة البيانات

```bash
❌ خطأ في الاتصال بقاعدة البيانات: Access denied
```

**الحل:** تحقق من اسم المستخدم وكلمة المرور

#### 2. خطأ في معرف الكتاب

```bash
❌ خطأ: الصفحة غير موجودة
```

**الحل:** تحقق من معرف الكتاب في المكتبة الشاملة

#### 3. خطأ في إنشاء الجداول

```bash
❌ خطأ في إنشاء الجدول: Table already exists
```

**الحل:** الجداول موجودة بالفعل، يمكن المتابعة

### السجلات والتتبع

يتم حفظ جميع العمليات في ملفات السجل:

- `enhanced_shamela_runner.log` - سجل التشغيل العام
- `enhanced_shamela_scraper.log` - سجل عمليات الاستخراج

### الأداء والتحسينات

- **معدل الطلبات:** 0.5 ثانية بين كل طلب
- **إعادة المحاولة:** 3 محاولات لكل طلب فاشل
- **ضغط البيانات:** دعم gzip للاستجابات
- **التخزين المؤقت:** تجنب إعادة الاستخراج للصفحات الموجودة

### التطوير والمساهمة

الكود مقسم إلى وحدات منفصلة:

- `enhanced_shamela_scraper.py` - منطق الاستخراج
- `enhanced_database_manager.py` - إدارة قاعدة البيانات
- `enhanced_runner.py` - واجهة المستخدم

### الدعم

للإبلاغ عن مشاكل أو طلب ميزات جديدة، استخدم نظام المشاكل في المشروع.

---
**ملاحظة:** هذا السكربت المحسن يحل جميع المشاكل المطروحة ويقدم ميزات إضافية لتحسين جودة البيانات المستخرجة.
