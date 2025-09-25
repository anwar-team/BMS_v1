# Ultra-Fast Search Module (Laravel + Elasticsearch)

## نظرة عامة

هذه الحزمة توفر نظام بحث فوري متقدم وسريع جداً للغة العربية باستخدام Laravel وElasticsearch. تم تطويرها لتكون سهلة النقل لأي مشروع Laravel آخر، مع واجهة مستخدم بسيطة وخصائص متقدمة (بحث مرن، مطابقة عبارات، تباعد، تحميل المزيد، إلخ).

---

## محتويات المجلد

- `views/ultra-fast.blade.php` : واجهة البحث (Blade + JS + CSS)
- `services/UltraFastSearchService.php` : منطق البحث الخلفي (Laravel Service)
- `controllers/SearchController.php` : الكونترولر المطلوب لواجهة API
- `models/` : جميع النماذج المطلوبة (Page, Book, Author, BookSection)
- `config/` : ملفات الإعدادات (scout.php, services.php)
- `web.php` : جميع المسارات (Routes) وواجهات API المطلوبة
- `README.md` : هذا الملف التوثيقي

---

## المتطلبات

- Laravel 10 أو أحدث
- PHP 8.1 أو أحدث
- مكتبة elasticsearch/elasticsearch (يفضل 7.17.x)
- مكتبة laravel/scout
- قاعدة بيانات بها الجداول المطلوبة
- إعداد Elasticsearch يعمل (مع Arabic Analyzer)

---

## قاعدة البيانات المطلوبة

تحتاج قاعدة البيانات للجداول التالية:

### 1. جدول `pages`
```sql
CREATE TABLE pages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    book_id BIGINT,
    page_number INT,
    content TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2. جدول `books`
```sql
CREATE TABLE books (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    book_section_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3. جدول `authors`
```sql
CREATE TABLE authors (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 4. جدول `book_sections`
```sql
CREATE TABLE book_sections (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 5. جدول `author_book` (Many-to-Many)
```sql
CREATE TABLE author_book (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    author_id BIGINT,
    book_id BIGINT
);
```

---

## طريقة التركيب

### 1. نسخ الملفات
```bash
# انسخ مجلد ultra-fast-search بالكامل إلى مشروعك
cp -r ultra-fast-search/ /path/to/your/laravel/project/
```

### 2. تثبيت المكتبات المطلوبة
```bash
composer require elasticsearch/elasticsearch:^7.17
composer require laravel/scout
```

### 3. نسخ ملفات النماذج
```bash
# انسخ النماذج إلى مشروعك أو استخدم النماذج الموجودة
cp ultra-fast-search/models/* app/Models/
```

### 4. نسخ الكونترولر
```bash
# انسخ الكونترولر
cp ultra-fast-search/controllers/SearchController.php app/Http/Controllers/
```

### 5. نسخ الخدمة
```bash
# انسخ خدمة البحث
cp ultra-fast-search/services/UltraFastSearchService.php app/Services/
```

### 6. نسخ ملف الواجهة
```bash
# انسخ ملف الواجهة
cp ultra-fast-search/views/ultra-fast.blade.php resources/views/search/
```

### 7. إضافة المسارات
أضف محتوى `ultra-fast-search/web.php` إلى ملف `routes/web.php` أو ضمّنه:
```php
// في routes/web.php
require_once base_path('ultra-fast-search/web.php');
```

### 8. نسخ ملفات الإعدادات
```bash
# نسخ إعدادات scout إذا لم تكن موجودة
cp ultra-fast-search/config/scout.php config/
```

### 9. إعداد متغيرات البيئة
أضف في ملف `.env`:
```env
SCOUT_DRIVER=elasticsearch
ELASTICSEARCH_HOST=http://localhost:9200
ELASTICSEARCH_INDEX=pages
```

### 10. إعداد إعدادات Elasticsearch في `config/services.php`
```php
'elasticsearch' => [
    'host' => env('ELASTICSEARCH_HOST', 'http://localhost:9200'),
    'index' => env('ELASTICSEARCH_INDEX', 'pages'),
],
```

---

## شرح الملفات

### 1. النماذج (Models)
- **Page.php**: النموذج الأساسي مع دعم Scout وElasticsearch
- **Book.php**: نموذج الكتب مع العلاقات
- **Author.php**: نموذج المؤلفين
- **BookSection.php**: نموذج أقسام الكتب

### 2. الخدمة (Service)
- **UltraFastSearchService.php**: منطق البحث مع:
  - اتصال مباشر بـ Elasticsearch
  - Fallback تلقائي إلى Scout
  - Fallback أخير إلى قاعدة البيانات

### 3. الكونترولر (Controller)
- **SearchController.php**: يتعامل مع طلبات API ويعيد JSON

### 4. الواجهة (View)
- **ultra-fast.blade.php**: واجهة كاملة مع JS وCSS مدمج

### 5. المسارات (Routes)
- **web.php**: جميع المسارات المطلوبة

---

## الاستخدام

### 1. فهرسة البيانات (إذا كنت تستخدم Scout)
```bash
php artisan scout:import "App\Models\Page"
```

### 2. الوصول للواجهة
```
http://your-domain.com/search/ultra-fast
```

### 3. استخدام API مباشرة
```bash
curl "http://your-domain.com/api/ultra-search?q=كلمة البحث"
```

---

## المزايا

- **بحث فوري** من أول حرف
- **دعم جميع أنماط البحث** (مرن، عبارة، تباعد، جميع الكلمات، أي كلمة)
- **تحميل المزيد** من النتائج (Load More)
- **نافذة شرح الخصائص**
- **دعم RTL والعربية** بالكامل
- **أداء عالي جداً** (أقل من 350ms)
- **Fallback متعدد المستويات** (Elasticsearch → Scout → Database)
- **سهل النقل** لأي مشروع Laravel

---

## استكشاف الأخطاء

### 1. اختبار الاتصال بـ Elasticsearch
```bash
curl http://localhost:9200/_cluster/health
```

### 2. اختبار البحث عبر API
```bash
curl "http://your-domain.com/api/ultra-search?q=test"
```

### 3. التحقق من الفهارس
```bash
curl http://localhost:9200/_cat/indices
```

---

## التخصيص

### 1. تعديل إعدادات البحث
عدل الثوابت في `UltraFastSearchService.php`:
```php
// تغيير الفهارس المستخدمة
$indices = ['your_custom_index', 'pages'];

// تغيير إعدادات الـ host
$this->elasticsearch = ClientBuilder::create()
    ->setHosts(['http://your-elasticsearch:9200'])
```

### 2. إضافة فلاتر جديدة
أضف فلاتر في `buildOptimizedQuery()`:
```php
if (!empty($filters['your_filter'])) {
    $boolQuery['bool']['filter'][] = [
        'term' => ['your_field' => $filters['your_filter']]
    ];
}
```

### 3. تخصيص الواجهة
عدل `ultra-fast.blade.php` حسب تصميم مشروعك.

---

## الدعم والتطوير

لأي استفسار أو تطوير إضافي، تواصل مع المطور أو راجع توثيق:
- [Elasticsearch Documentation](https://www.elastic.co/guide/)
- [Laravel Scout Documentation](https://laravel.com/docs/scout)

---

## ملاحظات مهمة

- تأكد من توافق أسماء الجداول والـ Models مع مشروعك
- يمكن تعديل الخدمة لدعم فلاتر إضافية
- النظام يعمل حتى لو فشل Elasticsearch (fallback تلقائي)
- يفضل اختبار جميع المسارات قبل الإطلاق
