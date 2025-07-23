# أداة تحويل ملفات BOK - المكتبة الشاملة

## نظرة عامة

أداة قوية ومتكاملة لتحويل ملفات `.bok` من المكتبة الشاملة إلى نظام إدارة الكتب (BMS). تدعم الأداة التحويل التلقائي مع الحفاظ على هيكل الكتاب والنصوص العربية.

## المميزات الرئيسية

### 🚀 التحويل التلقائي
- تحليل تلقائي لهيكل ملفات `.bok`
- استخراج معلومات الكتاب والمؤلف
- تحويل الفصول والأجزاء والصفحات
- دعم النصوص العربية بالكامل

### 🎯 واجهة مستخدم متقدمة
- واجهة Filament سهلة الاستخدام
- معاينة البيانات قبل التحويل
- تتبع تقدم العملية
- إدارة شاملة للكتب المحولة

### ⚡ أداء عالي
- معالجة متوازية للملفات
- تحسين استهلاك الذاكرة
- دعم الملفات الكبيرة
- نظام تخزين مؤقت ذكي

### 🔧 مرونة في التكوين
- إعدادات قابلة للتخصيص
- دعم أوامر سطر الأوامر
- نظام سجلات مفصل
- إعدادات أمان متقدمة

## متطلبات النظام

### البرمجيات المطلوبة
- PHP 8.1 أو أحدث
- Laravel 10.x
- Filament 3.x
- mdb-tools (لقراءة ملفات Access)

### متطلبات النظام
```bash
# Ubuntu/Debian
sudo apt-get install mdb-tools

# CentOS/RHEL
sudo yum install mdb-tools

# macOS
brew install mdb-tools

# Windows (WSL)
sudo apt-get install mdb-tools
```

## التثبيت والإعداد

### 1. تثبيت الحزم المطلوبة

```bash
# إضافة مكتبة mdb-parser إلى composer.json
composer require mdb-tools/mdb-parser
```

### 2. تسجيل مزود الخدمة

أضف إلى `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\BokConverterServiceProvider::class,
],
```

### 3. نشر ملفات التكوين

```bash
php artisan vendor:publish --tag=bok-converter-config
```

### 4. تكوين متغيرات البيئة

أضف إلى `.env`:

```env
# مسار أدوات MDB
MDB_TOOLS_PATH=mdb-tools

# مجلد التحويل المؤقت
BOK_TEMP_DIR=storage/app/temp/bok_conversion

# الحد الأقصى لحجم الملف (بالبايت)
BOK_MAX_FILE_SIZE=104857600

# تفعيل السجلات المفصلة
BOK_DETAILED_LOGGING=false

# إنشاء نسخ احتياطية
BOK_CREATE_BACKUP=false
```

### 5. إنشاء المجلدات المطلوبة

```bash
php artisan storage:link
mkdir -p storage/app/temp/bok_conversion
mkdir -p storage/app/bok-imports
mkdir -p storage/app/backups/bok
```

## طرق الاستخدام

### 1. واجهة الويب (Filament)

#### الوصول للأداة
1. انتقل إلى لوحة تحكم Filament
2. اختر "استيراد ملفات BOK" من القائمة
3. انقر على "استيراد ملف BOK جديد"

#### خطوات التحويل
1. **رفع الملف**: اختر ملف `.bok` من جهازك
2. **معاينة البيانات**: راجع المعلومات المستخرجة
3. **خيارات التحويل**: اختر الإعدادات المناسبة
4. **التحويل**: ابدأ عملية التحويل

### 2. سطر الأوامر (CLI)

#### تحويل ملف واحد
```bash
php artisan bok:convert /path/to/book.bok
```

#### تحويل متعدد
```bash
php artisan bok:convert /path/to/directory --batch
```

#### خيارات متقدمة
```bash
# تحويل مع تنظيف النصوص
php artisan bok:convert book.bok --clean

# تحويل بحالة مسودة
php artisan bok:convert book.bok --status=draft

# عرض تفاصيل إضافية
php artisan bok:convert book.bok --verbose

# تعطيل الكشف التلقائي عن الهيكل
php artisan bok:convert book.bok --no-structure
```

### 3. الاستخدام البرمجي

```php
use App\Services\BokConverterService;

$converter = new BokConverterService();
$result = $converter->convertBokFile('/path/to/book.bok');

if ($result['success']) {
    echo "تم تحويل الكتاب: " . $result['title'];
    echo "معرف الكتاب: " . $result['book_id'];
} else {
    echo "خطأ: " . $result['error'];
}
```

## هيكل البيانات المحولة

### نموذج الكتاب (Book)
```php
[
    'title' => 'عنوان الكتاب',
    'description' => 'وصف الكتاب',
    'language' => 'ar',
    'status' => 'published'
]
```

### نموذج الجزء (Volume)
```php
[
    'book_id' => 1,
    'number' => 1,
    'title' => 'الجزء الأول',
    'page_start' => 1,
    'page_end' => 100
]
```

### نموذج الفصل (Chapter)
```php
[
    'book_id' => 1,
    'volume_id' => 1,
    'chapter_number' => 1,
    'title' => 'الفصل الأول',
    'page_start' => 1,
    'page_end' => 20
]
```

### نموذج الصفحة (Page)
```php
[
    'book_id' => 1,
    'volume_id' => 1,
    'chapter_id' => 1,
    'page_number' => 1,
    'content' => 'محتوى الصفحة...'
]
```

## إعدادات التكوين

### ملف `config/bok_converter.php`

```php
return [
    // مسار أدوات MDB
    'mdb_tools_path' => env('MDB_TOOLS_PATH', 'mdb-tools'),
    
    // إعدادات الأداء
    'performance' => [
        'batch_size' => 100,
        'memory_limit' => '512M',
        'max_execution_time' => 300,
    ],
    
    // إعدادات تنظيف النصوص
    'text_cleaning' => [
        'remove_control_chars' => true,
        'normalize_whitespace' => true,
        'fix_arabic_encoding' => true,
    ],
    
    // مؤشرات بداية الفصول
    'chapter_indicators' => [
        'الفصل', 'الباب', 'المبحث', 'القسم'
    ],
];
```

## استكشاف الأخطاء

### مشاكل شائعة وحلولها

#### 1. خطأ "mdb-tools not found"
```bash
# تثبيت mdb-tools
sudo apt-get install mdb-tools

# أو تحديد المسار في .env
MDB_TOOLS_PATH=/usr/bin/mdb-tools
```

#### 2. خطأ في ترميز النصوص العربية
```php
// في config/bok_converter.php
'text_cleaning' => [
    'fix_arabic_encoding' => true,
]
```

#### 3. نفاد الذاكرة
```php
// زيادة حد الذاكرة
'performance' => [
    'memory_limit' => '1G',
    'batch_size' => 50,
]
```

#### 4. انتهاء وقت التنفيذ
```php
// زيادة وقت التنفيذ
'performance' => [
    'max_execution_time' => 600, // 10 دقائق
]
```

### تفعيل السجلات المفصلة

```env
BOK_DETAILED_LOGGING=true
```

```bash
# عرض السجلات
tail -f storage/logs/bok_converter.log
```

## الأمان والأداء

### إعدادات الأمان
- التحقق من نوع الملف قبل المعالجة
- تحديد حد أقصى لحجم الملف
- تنظيف المدخلات وتعقيم البيانات
- استخدام المعاملات لضمان سلامة البيانات

### تحسين الأداء
- معالجة البيانات على دفعات
- استخدام الفهرسة لتسريع البحث
- تنظيف الملفات المؤقتة تلقائياً
- تحسين استعلامات قاعدة البيانات

## التطوير والمساهمة

### هيكل الملفات
```
app/
├── Services/
│   └── BokConverterService.php
├── Filament/Resources/
│   └── BokImportResource.php
├── Console/Commands/
│   └── ConvertBokCommand.php
└── Providers/
    └── BokConverterServiceProvider.php

config/
└── bok_converter.php
```

### إضافة ميزات جديدة
1. إنشاء فرع جديد من `main`
2. تطوير الميزة مع الاختبارات
3. تحديث التوثيق
4. إرسال Pull Request

### اختبار الأداة
```bash
# تشغيل الاختبارات
php artisan test --filter=BokConverter

# اختبار ملف معين
php artisan bok:convert tests/fixtures/sample.bok --verbose
```

## الدعم والمساعدة

### الحصول على المساعدة
- مراجعة هذا التوثيق
- فحص ملفات السجلات
- استخدام الخيار `--verbose` للتفاصيل
- التحقق من إعدادات النظام

### الإبلاغ عن المشاكل
عند الإبلاغ عن مشكلة، يرجى تضمين:
- إصدار PHP و Laravel
- رسالة الخطأ كاملة
- خطوات إعادة إنتاج المشكلة
- ملف السجل ذي الصلة

## الترخيص

هذه الأداة مرخصة تحت رخصة MIT. يمكنك استخدامها وتعديلها بحرية.

---

**ملاحظة**: هذه الأداة مصممة خصيصاً للعمل مع ملفات `.bok` من المكتبة الشاملة وقد تحتاج لتعديلات للعمل مع مصادر أخرى.