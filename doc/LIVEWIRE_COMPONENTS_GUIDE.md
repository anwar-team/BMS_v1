# دليل استخدام Livewire Components للجداول

## نظرة عامة

تم تحويل الجداول في صفحتي `home` و `show-all` إلى Livewire Components قابلة لإعادة الاستخدام مع الحفاظ على نفس التصميم والوظائف.

## المكونات المنشأة

### 1. BooksTable Component
**الملف:** `app/Livewire/BooksTable.php`
**العرض:** `resources/views/livewire/books-table.blade.php`

#### الخصائص المتاحة:
- `search`: البحث في الكتب
- `perPage`: عدد العناصر في الصفحة (10, 25, 50, 100)
- `section`: فلترة حسب القسم
- `showSearch`: إظهار/إخفاء مربع البحث
- `showFilters`: إظهار/إخفاء أزرار الفلاتر
- `title`: عنوان الجدول
- `showPagination`: إظهار/إخفاء التصفح

#### مثال على الاستخدام:
```blade
@livewire('books-table', [
    'section' => 'fiqh',
    'showSearch' => true,
    'showFilters' => true,
    'title' => 'كتب الفقه',
    'perPage' => 25,
    'showPagination' => true
])
```

### 2. AuthorsTable Component
**الملف:** `app/Livewire/AuthorsTable.php`
**العرض:** `resources/views/livewire/authors-table.blade.php`

#### الخصائص المتاحة:
- `search`: البحث في المؤلفين
- `perPage`: عدد العناصر في الصفحة
- `showSearch`: إظهار/إخفاء مربع البحث
- `showFilters`: إظهار/إخفاء أزرار الفلاتر
- `title`: عنوان الجدول
- `showPagination`: إظهار/إخفاء التصفح

#### مثال على الاستخدام:
```blade
@livewire('authors-table', [
    'showSearch' => true,
    'showFilters' => false,
    'title' => 'المؤلفون المشهورون',
    'perPage' => 15,
    'showPagination' => true
])
```

## التغييرات في الصفحات

### صفحة Home
- تم استبدال جدول الكتب بـ `@livewire('books-table')`
- تم استبدال جدول المؤلفين بـ `@livewire('authors-table')`
- تم الحفاظ على جميع العناصر الأخرى (Banner, Statistics, Categories)

### صفحة Show-All
- تم استبدال الجداول بـ Livewire Components حسب النوع
- تم الحفاظ على العنوان ومربع البحث العلوي
- تم الحفاظ على نفس التصميم والوظائف

## المميزات الجديدة

### 1. البحث المباشر (Live Search)
- البحث يتم بشكل مباشر أثناء الكتابة
- تأخير 300ms لتحسين الأداء
- البحث في جميع الحقول ذات الصلة

### 2. التصفح التفاعلي
- تغيير عدد العناصر في الصفحة بدون إعادة تحميل
- أزرار التنقل التفاعلية
- عرض معلومات التصفح

### 3. الفلترة المتقدمة
- فلترة الكتب حسب القسم
- فلترة المؤلفين حسب المعايير المختلفة
- الحفاظ على الفلاتر في URL

### 4. المرونة في التخصيص
- إمكانية إظهار/إخفاء أي عنصر
- تخصيص عدد العناصر في الصفحة
- تخصيص العنوان والنصوص

## كيفية إضافة مكون جديد

### 1. إنشاء Livewire Component
```bash
php artisan make:livewire TableName
```

### 2. إضافة الخصائص المطلوبة
```php
class TableName extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    public $showSearch = true;
    // ... باقي الخصائص
}
```

### 3. تحديد Query String Parameters
```php
protected $queryString = [
    'search' => ['except' => ''],
    'page' => ['except' => 1],
    'perPage' => ['except' => 10],
];
```

### 4. إضافة دوال التحديث
```php
public function updatingSearch()
{
    $this->resetPage();
}

public function updatingPerPage()
{
    $this->resetPage();
}
```

## نصائح للاستخدام

### 1. الأداء
- استخدم `wire:model.live.debounce.300ms` للبحث
- تجنب الاستعلامات المعقدة في كل تحديث
- استخدم الفهرسة المناسبة في قاعدة البيانات

### 2. التصميم
- احتفظ بنفس classes الـ CSS الموجودة
- استخدم نفس الألوان والخطوط
- تأكد من التوافق مع الشاشات المختلفة

### 3. إدارة الحالة
- استخدم Query String للحفاظ على الحالة
- تجنب تخزين البيانات الحساسة في URL
- استخدم Session للبيانات المؤقتة

## استكشاف الأخطاء

### مشكلة عدم ظهور النتائج
1. تأكد من وجود البيانات في قاعدة البيانات
2. تحقق من صحة العلاقات في النماذج
3. تأكد من صحة شروط البحث

### مشكلة التصفح
1. تأكد من استخدام `WithPagination` trait
2. تحقق من إعدادات `$queryString`
3. تأكد من استدعاء `resetPage()` عند التحديث

### مشكلة التصميم
1. تأكد من تضمين CSS الخاص بـ Livewire
2. تحقق من صحة classes الـ CSS
3. تأكد من عدم تعارض الـ JavaScript

## الخلاصة

تم تحويل الجداول بنجاح إلى Livewire Components مع:
- ✅ الحفاظ على نفس التصميم
- ✅ الحفاظ على جميع الوظائف
- ✅ إضافة ميزات تفاعلية جديدة
- ✅ تحسين تجربة المستخدم
- ✅ سهولة الصيانة والتطوير

يمكن الآن استخدام هذه المكونات في أي مكان في التطبيق بسهولة ومرونة كاملة.