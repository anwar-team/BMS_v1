# BuildsTableOfContents Trait - دليل المطور

## 📖 نظرة عامة

`BuildsTableOfContents` هو Trait موحد لبناء فهرس الكتب بدون تكرار مع تمييز الفصول المتشابهة.

## 🎯 الاستخدام

### في Controller
```php
<?php

namespace App\Http\Controllers;

use App\Traits\BuildsTableOfContents;

class BookReadController extends Controller
{
    use BuildsTableOfContents;
    
    public function show(Request $request, $bookId, $pageNumber = 1)
    {
        $tableOfContents = $this->buildUniqueTableOfContents($bookId);
        
        return view('pages.book-read', compact('tableOfContents'));
    }
}
```

### في Livewire Component
```php
<?php

namespace App\Livewire\Reader;

use App\Traits\BuildsTableOfContents;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class BookReader extends Component
{
    use BuildsTableOfContents;
    
    private function loadTableOfContents(): void
    {
        $cacheKey = "book_toc_{$this->bookId}";
        
        $this->tableOfContents = Cache::remember($cacheKey, now()->addHours(6), function () {
            return $this->buildUniqueTableOfContents($this->bookId);
        });
    }
}
```

### في Blade Templates
```blade
{{-- استخدام uniqueChapters --}}
@if($tableOfContents['type'] === 'volumes_with_chapters')
    @foreach($tableOfContents['data'] as $volume)
        @if(isset($volume->uniqueChapters) && $volume->uniqueChapters->isNotEmpty())
            @foreach($volume->uniqueChapters as $chapter)
                @include('partials.chapter-tree', ['chapter' => $chapter, 'level' => 0])
            @endforeach
        @endif
    @endforeach
@endif
```

## 🔧 الدوال المتاحة

### `buildUniqueTableOfContents(int $bookId): array`
بناء الفهرس الكامل للكتاب.

**المعطيات:**
- `$bookId`: معرف الكتاب

**المخرجات:**
```php
[
    'type' => 'volumes_with_chapters' | 'chapters_only',
    'data' => Collection of Volumes or Chapters
]
```

### `getUniqueRootChapters(int $bookId, ?int $volumeId): Collection`
جلب الفصول الرئيسية الفريدة.

### `getUniqueChildChapters(int $parentId): Collection`
جلب الفصول الفرعية بشكل متكرر.

### `removeDuplicateChapters(Collection $chapters): Collection`
إزالة التكرار من مجموعة فصول.

### `checkSiblingsSamePage(Chapter $chapter, Collection $siblings): bool`
التحقق إذا كان الفصل له فصول أخرى بنفس الصفحة.

### `countSamePageSiblings(Chapter $chapter, Collection $siblings): int`
عد الفصول بنفس الصفحة.

### `clearTableOfContentsCache(int $bookId): void`
تنظيف الكاش للفهرس.

## 📦 المتغيرات الإضافية

يضيف الـ Trait متغيرات إضافية لكل فصل:

```php
$chapter->uniqueChildren      // Collection: الفصول الفرعية بدون تكرار
$chapter->hasSiblingsSamePage // bool: هل له فصول بنفس الصفحة؟
$chapter->samePageCount       // int: عدد الفصول بنفس الصفحة
```

## 🎨 التمييز البصري في Blade

### في partials/chapter-tree.blade.php
```blade
@php
    $children = $chapter->uniqueChildren ?? $chapter->children ?? collect();
    $hasSamePage = $chapter->hasSiblingsSamePage ?? false;
    $samePageCount = $chapter->samePageCount ?? 1;
@endphp

<div class="{{ $hasSamePage ? 'bg-amber-50 border-amber-200' : '' }}">
    <span>{{ $chapter->title }}</span>
    
    @if($hasSamePage && $samePageCount > 1)
        <span class="bg-amber-200 text-amber-800">
            🔸 {{ $samePageCount }}
        </span>
    @endif
</div>
```

## ⚙️ معايير إزالة التكرار

يتم إزالة التكرار على أساس 6 حقول:

```php
[
    'parent_id'  => null | int,
    'volume_id'  => null | int,
    'order'      => null | int,
    'title'      => string (trimmed),
    'page_start' => null | int,
    'page_end'   => null | int,
]
```

**مثال:**
```
فصل A: parent_id=null, volume_id=1, order=1, title="مقدمة", page_start=1, page_end=5
فصل B: parent_id=null, volume_id=1, order=1, title="مقدمة", page_start=1, page_end=5

النتيجة: يتم الاحتفاظ بـ فصل A فقط ✓
```

## 🚨 الحالات الخاصة

### فصول بدون صفحات
```php
$chapter->page_start = null;
$chapter->page_end = null;
// لن يتم اعتبارها متشابهة
$chapter->hasSiblingsSamePage = false;
```

### فصول بدون volume_id
```php
$chapter->volume_id = null;
// سيتم معاملتها بشكل منفصل عن الفصول ذات volume_id
```

### تفرعات عميقة
```
المستوى 0 (parent_id=null)
├─ المستوى 1 (parent_id=فصل_رئيسي)
│  ├─ المستوى 2 (parent_id=فصل_مستوى_1)
│  │  └─ المستوى 3...
```
يتم دعم جميع المستويات بشكل متكرر ✓

## 🧪 الاختبار

### اختبار يدوي
```bash
php test_final_comprehensive.php
```

### اختبار وحدة (Unit Test)
```php
use Tests\TestCase;
use App\Traits\BuildsTableOfContents;

class TableOfContentsTest extends TestCase
{
    use BuildsTableOfContents;
    
    public function test_removes_duplicate_chapters()
    {
        $chapters = Chapter::factory()->count(5)->create([
            'book_id' => 1,
            'parent_id' => null,
            'order' => 1,
            'title' => 'مقدمة',
        ]);
        
        $unique = $this->removeDuplicateChapters($chapters);
        
        $this->assertEquals(1, $unique->count());
    }
}
```

## 📊 الأداء

### Caching
```php
// مثال في Livewire
$this->tableOfContents = Cache::remember(
    "book_toc_{$this->bookId}", 
    now()->addHours(6), 
    fn() => $this->buildUniqueTableOfContents($this->bookId)
);
```

### إبطال الكاش
```php
// عند تحديث الفصول
$this->clearTableOfContentsCache($bookId);

// أو يدويًا
Cache::forget("book_toc_{$bookId}");
```

### تحسين Queries
```php
// الـ Trait يستخدم queries منفصلة بدلاً من eager loading
// هذا يسمح بإزالة التكرار في PHP بعد الجلب
$chapters = Chapter::where('book_id', $bookId)
    ->whereNull('parent_id')
    ->orderBy('order')
    ->get();

$uniqueChapters = $this->removeDuplicateChapters($chapters);
```

## 🔍 التنقيح (Debugging)

### عرض البيانات
```php
$tableOfContents = $this->buildUniqueTableOfContents($bookId);

dd([
    'type' => $tableOfContents['type'],
    'volumes_count' => $tableOfContents['data']->count(),
    'first_volume_chapters' => $tableOfContents['data']->first()->uniqueChapters ?? []
]);
```

### فحص فصل محدد
```php
$chapter = Chapter::find($chapterId);

dd([
    'title' => $chapter->title,
    'has_same_page' => $chapter->hasSiblingsSamePage ?? 'not_set',
    'same_page_count' => $chapter->samePageCount ?? 'not_set',
    'unique_children_count' => $chapter->uniqueChildren->count() ?? 0
]);
```

## 🛠️ استكشاف الأخطاء

### الفصول لا تظهر
```php
// تحقق من uniqueChapters
if (!isset($volume->uniqueChapters)) {
    // الـ Trait لم يعمل، تأكد من:
    // 1. استخدام buildUniqueTableOfContents()
    // 2. الـ Trait موجود في الكلاس
}
```

### التمييز البصري لا يعمل
```blade
{{-- تأكد من استخدام المتغيرات الصحيحة --}}
@php
    $hasSamePage = $chapter->hasSiblingsSamePage ?? false;
@endphp

@if($hasSamePage)
    {{-- تمييز بصري --}}
@endif
```

### الكاش قديم
```php
// تنظيف الكاش
php artisan cache:clear

// أو برمجيًا
Cache::forget("book_toc_{$bookId}");
```

## 📝 الملاحظات المهمة

1. **الترتيب**: يتم الحفاظ على ترتيب `order` الأصلي
2. **التفرد**: أول فصل فريد يتم الاحتفاظ به
3. **Recursion**: دعم كامل للتفرعات العميقة
4. **Performance**: استخدم Caching في الإنتاج
5. **Compatibility**: يعمل مع Controller و Livewire

## 🤝 المساهمة

عند إضافة ميزات جديدة:
1. اتبع نفس نمط التسمية
2. أضف توثيق DocBlock
3. اختبر مع كتب مختلفة
4. حدث التوثيق

## 📚 المراجع

- `COMPREHENSIVE_INDEX_FIX_FINAL.md` - التوثيق الشامل
- `INDEX_FIX_SUMMARY.md` - الملخص السريع
- `test_final_comprehensive.php` - ملف الاختبار

---

**تم التطوير بواسطة**: فريق anwar-team  
**التاريخ**: أكتوبر 2, 2025  
**الإصدار**: 2.0.0
