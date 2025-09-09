# إصلاحات الأداء المطلوبة فوراً

## 1. إصلاح BookResource - Table Method

### المشكلة الحالية:
```php
// في app/Filament/Resources/BookResource.php
TextColumn::make('volumes_count')
    ->getStateUsing(fn ($record) => $record->volumes()->count()) // N+1 Query
    
TextColumn::make('pages_count')
    ->getStateUsing(fn ($record) => $record->pages()->count()) // N+1 Query
```

### الحل المقترح:
```php
// إضافة في BookResource
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->withCount(['volumes', 'pages'])
        ->with(['bookSection', 'publisher', 'authorBooks.author']);
}

// ثم تغيير الـ columns إلى:
TextColumn::make('volumes_count')
    ->label('المجلدات')
    ->badge()
    ->color('success')
    ->toggleable(),
    
TextColumn::make('pages_count')
    ->label('الصفحات')
    ->badge()
    ->color('warning')
    ->toggleable(),
```

## 2. إصلاح Book Model

### المشكلة:
```php
protected static function booted()
{
    static::saving(function ($book) {
        $book->pages_count = $book->pages()->count(); // خطير جداً
        $book->volumes_count = $book->volumes()->count();
    });
}
```

### الحل:
```php
// حذف method booted() كاملاً واستخدام:
// 1. Database columns للـ counts
// 2. Events منفصلة عند إضافة/حذف pages/volumes
// 3. أو Jobs في الخلفية لتحديث الـ counts
```

## 3. إصلاح mainAuthors Column

### الحل:
```php
TextColumn::make('main_author_name')
    ->label('المؤلف الرئيسي')
    ->getStateUsing(function ($record) {
        // البيانات محملة مسبقاً من getEloquentQuery()
        $mainAuthor = $record->authorBooks
            ->where('is_main', true)
            ->first();
        return $mainAuthor?->author?->full_name ?? 
               $record->authorBooks->first()?->author?->full_name ?? 'غير محدد';
    })
    ->searchable(query: function (Builder $query, string $search): Builder {
        return $query->whereHas('authorBooks.author', function (Builder $query) use ($search) {
            $query->where('full_name', 'like', "%{$search}%");
        });
    })
    ->limit(30),
```

## 4. تحسينات إضافية

### إضافة Caching:
```php
// في config/cache.php تأكد من استخدام Redis أو Memcached
'default' => env('CACHE_DRIVER', 'redis'),

// إضافة caching للـ widgets
protected function getCachedData(): array
{
    return Cache::remember('books_stats', 300, function () {
        return [
            'total_books' => Book::count(),
            'published_books' => Book::where('status', 'published')->count(),
            // ...
        ];
    });
}
```

### Database Indexes إضافية:
```sql
-- إضافة indexes للبحث السريع
ALTER TABLE books ADD INDEX idx_title_search (title);
ALTER TABLE books ADD INDEX idx_status_created (status, created_at);
ALTER TABLE author_book ADD INDEX idx_main_author (book_id, is_main, author_id);
```

## 5. تحسين Widgets

### BooksStatsWidget:
```php
protected function getStats(): array
{
    // استخدام query واحدة بدلاً من 4
    $stats = Book::selectRaw('
        COUNT(*) as total_books,
        SUM(CASE WHEN status = "published" THEN 1 ELSE 0 END) as published_books,
        SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as recent_books
    ', [now()->subDays(30)])->first();
    
    $totalSections = BookSection::count();
    
    return [
        Stat::make('إجمالي الكتب', $stats->total_books),
        Stat::make('الكتب المنشورة', $stats->published_books),
        Stat::make('أقسام الكتب', $totalSections),
        Stat::make('كتب جديدة', $stats->recent_books),
    ];
}
```
