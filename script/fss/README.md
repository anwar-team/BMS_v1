# مستخرج كتب المكتبة الشاملة - Shamela Books Scraper

سكربت شامل لاستخراج الكتب من المكتبة الشاملة (shamela.ws) وحفظها في قاعدة البيانات.

## المميزات

- ✅ استخراج بيانات الكتاب الكاملة (العنوان، المؤلف، الناشر، إلخ)
- ✅ استخراج الفهرس والأجزاء
- ✅ استخراج محتوى الصفحات (نص و HTML)
- ✅ حفظ البيانات في ملفات JSON
- ✅ حفظ البيانات في قاعدة بيانات MySQL
- ✅ معالجة دفعية للكتب المتعددة
- ✅ إعادة المحاولة للكتب الفاشلة
- ✅ تتبع التقدم وحفظ الحالة
- ✅ معالجة الأخطاء المتقدمة
- ✅ دعم نطاقات الصفحات المحددة

## التثبيت

### 1. تثبيت Python
تأكد من تثبيت Python 3.8 أو أحدث.

### 2. تثبيت المتطلبات
```bash
pip install -r requirements.txt
```

### 3. إعداد قاعدة البيانات (اختياري)
إذا كنت تريد حفظ البيانات في قاعدة بيانات MySQL، تأكد من:
- تثبيت MySQL Server
- إنشاء قاعدة بيانات (مثل `bms`)
- إنشاء الجداول المطلوبة (انظر قسم هيكل قاعدة البيانات)

## الاستخدام

### استخراج كتاب واحد

```bash
# استخراج كتاب بمعرف 12345
python shamela_runner.py single 12345

# استخراج كتاب وحفظه في قاعدة البيانات
python shamela_runner.py single 12345 --save-db --db-password mypassword

# استخراج نطاق صفحات محدد (من صفحة 1 إلى 50)
python shamela_runner.py single 12345 --page-range "1-50"

# استخراج صفحة واحدة فقط
python shamela_runner.py single 12345 --page-range "25"
```

### المعالجة الدفعية

```bash
# استخراج عدة كتب من ملف
python shamela_runner.py batch --book-list books.txt

# استخراج كتب محددة
python shamela_runner.py batch --book-ids 12345 67890 11111

# معالجة دفعية مع حفظ في قاعدة البيانات
python shamela_runner.py batch --book-list books.txt --save-db --db-password mypassword
```

### إعادة محاولة الكتب الفاشلة

```bash
# إعادة محاولة جميع الكتب التي فشلت في المعالجة السابقة
python shamela_runner.py retry

# إعادة محاولة مع حفظ في قاعدة البيانات
python shamela_runner.py retry --save-db --db-password mypassword
```

## تنسيق ملف قائمة الكتب

يمكن أن يحتوي ملف `books.txt` على:

```
# هذا تعليق - سيتم تجاهله
12345
67890
https://shamela.ws/book/11111
https://shamela.ws/book/22222/1
33333
```

## خيارات سطر الأوامر

### خيارات عامة
- `--output-dir`: مجلد الإخراج (افتراضي: shamela_books)
- `--no-html`: عدم استخراج HTML للصفحات (نص فقط)
- `--save-db`: حفظ البيانات في قاعدة البيانات

### خيارات قاعدة البيانات
- `--db-host`: عنوان الخادم (افتراضي: localhost)
- `--db-port`: منفذ الاتصال (افتراضي: 3306)
- `--db-user`: اسم المستخدم (افتراضي: root)
- `--db-password`: كلمة المرور
- `--db-name`: اسم قاعدة البيانات (افتراضي: bms)

### خيارات المعالجة الدفعية
- `--continue-on-error`: المتابعة عند حدوث خطأ في كتاب معين

## هيكل الملفات المُخرجة

```
shamela_books/
├── book_12345_20231201_143022.json    # بيانات الكتاب
├── book_67890_20231201_143055.json
├── progress.json                        # ملف تتبع التقدم
├── batch_report_20231201_143100.json   # تقرير المعالجة الدفعية
└── shamela_runner.log                   # ملف السجلات
```

## هيكل ملف JSON للكتاب

```json
{
  "title": "عنوان الكتاب",
  "shamela_id": "12345",
  "authors": [
    {
      "name": "اسم المؤلف",
      "biography": "ترجمة المؤلف",
      "birth_date": "تاريخ الولادة",
      "death_date": "تاريخ الوفاة"
    }
  ],
  "publisher": "دار النشر",
  "publication_year": "سنة النشر",
  "page_count": 500,
  "volume_count": 2,
  "categories": ["الفقه", "الحديث"],
  "index": [
    {
      "title": "الفصل الأول",
      "page_number": 10,
      "level": 0,
      "children": []
    }
  ],
  "volumes": [
    {
      "number": 1,
      "title": "الجزء الأول",
      "page_start": 1,
      "page_end": 250
    }
  ],
  "pages": [
    {
      "page_number": 1,
      "content": "محتوى الصفحة النصي",
      "html_content": "<div>محتوى HTML</div>",
      "word_count": 150,
      "volume_number": 1
    }
  ]
}
```

## هيكل قاعدة البيانات

### جدول الكتب (books)
```sql
CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500),
    shamela_id VARCHAR(50) UNIQUE NOT NULL,
    publisher VARCHAR(200),
    edition VARCHAR(100),
    publication_year VARCHAR(20),
    pages_count INT,
    volumes_count INT,
    categories JSON,
    description TEXT,
    language VARCHAR(10) DEFAULT 'ar',
    source_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### جدول المؤلفين (authors)
```sql
CREATE TABLE authors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200),
    biography TEXT,
    madhhab VARCHAR(100),
    birth_date VARCHAR(50),
    death_date VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### جدول ربط المؤلفين بالكتب (author_book)
```sql
CREATE TABLE author_book (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    author_id INT NOT NULL,
    role VARCHAR(50) DEFAULT 'author',
    is_main BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
);
```

### جدول الأجزاء (volumes)
```sql
CREATE TABLE volumes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    number INT NOT NULL,
    title VARCHAR(200),
    page_start INT,
    page_end INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);
```

### جدول الفصول (chapters)
```sql
CREATE TABLE chapters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    volume_id INT,
    title VARCHAR(500) NOT NULL,
    page_number INT,
    page_end INT,
    parent_id INT,
    level INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (volume_id) REFERENCES volumes(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES chapters(id) ON DELETE CASCADE
);
```

### جدول الصفحات (pages)
```sql
CREATE TABLE pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    volume_id INT,
    chapter_id INT,
    page_number INT NOT NULL,
    content LONGTEXT,
    html_content LONGTEXT,
    word_count INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (volume_id) REFERENCES volumes(id) ON DELETE SET NULL,
    FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE SET NULL,
    UNIQUE KEY unique_book_page (book_id, page_number)
);
```

## استخدام مدير قاعدة البيانات منفصلاً

```python
from shamela_database_manager import ShamelaDatabaseManager, load_book_from_json

# إعداد قاعدة البيانات
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': 'mypassword',
    'database': 'bms'
}

# حفظ كتاب من ملف JSON
with ShamelaDatabaseManager(db_config) as db:
    book = load_book_from_json('book_12345.json')
    result = db.save_complete_book(book)
    print(f"تم حفظ الكتاب بمعرف: {result['book_id']}")

# الحصول على إحصائيات كتاب
with ShamelaDatabaseManager(db_config) as db:
    stats = db.get_book_stats(book_id=1)
    print(f"عدد الصفحات: {stats['pages_count']}")
```

## استكشاف الأخطاء

### مشاكل شائعة وحلولها

1. **خطأ في الاتصال بقاعدة البيانات**
   - تأكد من تشغيل MySQL Server
   - تحقق من صحة بيانات الاتصال
   - تأكد من وجود قاعدة البيانات

2. **فشل في استخراج صفحات الكتاب**
   - قد يكون الكتاب محذوف أو غير متاح
   - تحقق من صحة معرف الكتاب
   - جرب مرة أخرى لاحقاً (قد تكون مشكلة مؤقتة)

3. **بطء في الاستخراج**
   - السكربت يحترم معدل الطلبات لتجنب الحظر
   - يمكن تقليل التأخير في الكود إذا لزم الأمر

4. **نفاد الذاكرة مع الكتب الكبيرة**
   - استخدم نطاقات صفحات أصغر
   - قم بمعالجة الكتاب على دفعات

### ملفات السجلات

يتم حفظ جميع العمليات في ملف `shamela_runner.log` لمساعدتك في تتبع المشاكل.

## المساهمة

نرحب بالمساهمات! يرجى:
1. عمل Fork للمشروع
2. إنشاء فرع للميزة الجديدة
3. إجراء التغييرات
4. إرسال Pull Request

## الترخيص

هذا المشروع مخصص للاستخدام التعليمي والبحثي. يرجى احترام حقوق الطبع والنشر للمحتوى المستخرج.

## إخلاء المسؤولية

- هذا السكربت مخصص للاستخدام التعليمي والبحثي فقط
- يرجى احترام شروط استخدام موقع المكتبة الشاملة
- المطورون غير مسؤولين عن أي استخدام غير قانوني للسكربت
- تأكد من امتلاكك الحق في استخراج ونسخ المحتوى قبل الاستخدام