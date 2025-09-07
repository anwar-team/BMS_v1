{{-- مكون Livewire لعرض جدول الكتب --}}
{{-- يحافظ على التصميم الأصلي مع إضافة التفاعل --}}

<div>
    {{-- مؤشر التحميل --}}
    <div wire:loading.delay wire:target="search, statusFilter, publisherFilter, sortBy, nextPage, previousPage, gotoPage"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-25">
        <div class="bg-white rounded-lg p-6 shadow-lg">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-gray-700">جاري التحميل...</span>
            </div>
        </div>
    </div>

    {{-- شريط البحث والتصفية --}}
    <div class="mb-6 bg-white rounded-lg shadow-sm border p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- حقل البحث --}}
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">البحث في الكتب</label>
                <input type="text" 
                       id="search"
                       wire:model.live.debounce.300ms="search" 
                       placeholder="ابحث في العناوين، الأوصاف، أو المؤلفين..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            {{-- تصفية الحالة --}}
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                <select id="status" wire:model.live="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="published">منشور</option>
                    <option value="review">قيد المراجعة</option>
                    <option value="draft">مسودة</option>
                    <option value="archived">مؤرشف</option>
                    <option value="all">جميع الحالات</option>
                </select>
            </div>
            
            {{-- تصفية الناشر --}}
            <div>
                <label for="publisher" class="block text-sm font-medium text-gray-700 mb-1">الناشر</label>
                <select id="publisher" wire:model.live="publisherFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الناشرين</option>
                    @foreach($publishers as $publisher)
                        <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        {{-- أزرار التحكم --}}
        <div class="flex justify-between items-center mt-4 pt-4 border-t">
            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                {{-- أزرار الترتيب --}}
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <span class="text-sm text-gray-600">ترتيب حسب:</span>
                    <button wire:click="sortBy('title')" 
                            class="px-3 py-1 text-sm rounded {{ $sortField === 'title' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        العنوان
                        @if($sortField === 'title')
                            <i class="fas fa-chevron-{{ $sortDirection === 'desc' ? 'down' : 'up' }} ml-1"></i>
                        @endif
                    </button>
                    <button wire:click="sortBy('created_at')" 
                            class="px-3 py-1 text-sm rounded {{ $sortField === 'created_at' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        تاريخ الإضافة
                        @if($sortField === 'created_at')
                            <i class="fas fa-chevron-{{ $sortDirection === 'desc' ? 'down' : 'up' }} ml-1"></i>
                        @endif
                    </button>
                </div>
            </div>
            
            {{-- زر مسح المرشحات --}}
            <button wire:click="clearFilters" 
                    class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                <i class="fas fa-times ml-1"></i>
                مسح المرشحات
            </button>
        </div>
    </div>

    {{-- عرض النتائج --}}
    @if($search || $statusFilter !== 'published' || $publisherFilter)
        <div class="mb-4 text-sm text-gray-600">
            <span class="font-medium">النتائج:</span>
            {{ $books->total() }} كتاب
            @if($search)
                <span class="mx-2">•</span>
                <span>البحث عن: "{{ $search }}"</span>
            @endif
            @if($statusFilter !== 'published')
                <span class="mx-2">•</span>
                <span>الحالة: {{ $statusFilter === 'all' ? 'جميع الحالات' : $statusFilter }}</span>
            @endif
            @if($publisherFilter)
                <span class="mx-2">•</span>
                <span>الناشر: {{ $publishers->firstWhere('id', $publisherFilter)->name ?? 'غير محدد' }}</span>
            @endif
        </div>
    @endif

    {{-- الحاوية الرئيسية للكتب (نفس التصميم الأصلي) --}}
    <div class="page-wrapper">
        <main class="main">
            <div class="page-title">
                <div class="container">
                    <div class="page-title-content">
                        <div class="page-title-icon">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <h1 class="page-title-heading">مكتبة الكتب</h1>
                    </div>
                </div>
            </div>

            <section class="section-padding">
                <div class="container">
                    @if($books->count() > 0)
                        {{-- شبكة الكتب --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($books as $book)
                                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300" wire:key="book-{{ $book->id }}">
                                    {{-- صورة الغلاف --}}
                                    <div class="aspect-[3/4] bg-gray-100 relative overflow-hidden">
                                        @if($book->cover_image)
                                            <img src="{{ asset('storage/' . $book->cover_image) }}" 
                                                 alt="{{ $book->title }}" 
                                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-100 to-blue-200">
                                                <i class="fas fa-book text-4xl text-blue-400"></i>
                                            </div>
                                        @endif
                                        
                                        {{-- شارة الحالة --}}
                                        <div class="absolute top-2 right-2">
                                            @if($book->status === 'published')
                                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">منشور</span>
                                            @elseif($book->status === 'review')
                                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">مراجعة</span>
                                            @elseif($book->status === 'draft')
                                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">مسودة</span>
                                            @elseif($book->status === 'archived')
                                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">مؤرشف</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- معلومات الكتاب --}}
                                    <div class="p-4">
                                        {{-- العنوان --}}
                                        <h3 class="font-bold text-lg mb-2 line-clamp-2">
                                            <a href="{{ route('books.show', $book->id) }}" 
                                               class="text-gray-900 hover:text-blue-600 transition-colors">
                                                {{ $book->title }}
                                            </a>
                                        </h3>
                                        
                                        {{-- المؤلفين --}}
                                        @if($book->authors->count() > 0)
                                            <div class="text-sm text-gray-600 mb-2">
                                                <i class="fas fa-user-edit ml-1"></i>
                                                {{ $book->authors->pluck('name')->join('، ') }}
                                            </div>
                                        @endif
                                        
                                        {{-- الناشر --}}
                                        @if($book->publisher)
                                            <div class="text-sm text-gray-600 mb-3">
                                                <i class="fas fa-building ml-1"></i>
                                                {{ $book->publisher->name }}
                                            </div>
                                        @endif
                                        
                                        {{-- الإحصائيات --}}
                                        <div class="grid grid-cols-2 gap-4 mb-3 text-sm">
                                            <div class="text-center p-2 bg-gray-50 rounded">
                                                <div class="font-semibold text-gray-900">{{ $book->pages_count ?? 0 }}</div>
                                                <div class="text-gray-600">صفحة</div>
                                            </div>
                                            <div class="text-center p-2 bg-gray-50 rounded">
                                                <div class="font-semibold text-gray-900">{{ $book->volumes_count ?? 1 }}</div>
                                                <div class="text-gray-600">مجلد</div>
                                            </div>
                                        </div>
                                        
                                        {{-- سنة النشر --}}
                                        @if($book->published_year)
                                            <div class="text-sm text-gray-600 mb-3">
                                                <i class="fas fa-calendar ml-1"></i>
                                                {{ $book->published_year }}
                                            </div>
                                        @endif
                                        
                                        {{-- الوصف --}}
                                        @if($book->description)
                                            <p class="text-sm text-gray-700 mb-4 line-clamp-3">
                                                {{ $book->description }}
                                            </p>
                                        @endif
                                        
                                        {{-- أزرار الإجراءات --}}
                                        <div class="flex space-x-2 rtl:space-x-reverse">
                                            <a href="{{ route('books.details', $book->id) }}" 
                                               class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded hover:bg-blue-700 transition-colors text-sm">
                                                <i class="fas fa-info-circle ml-1"></i>
                                                التفاصيل
                                            </a>
                                            <a href="{{ route('books.read', $book->id) }}" 
                                               class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded hover:bg-green-700 transition-colors text-sm">
                                                <i class="fas fa-book-open ml-1"></i>
                                                قراءة
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- الترقيم --}}
                        <div class="mt-8">
                            {{ $books->links() }}
                        </div>
                    @else
                        {{-- رسالة عدم وجود كتب --}}
                        <div class="text-center py-12">
                            <div class="max-w-md mx-auto">
                                <i class="fas fa-book text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">لا توجد كتب</h3>
                                <p class="text-gray-500 mb-4">
                                    @if($search || $statusFilter !== 'published' || $publisherFilter)
                                        لم يتم العثور على كتب تطابق معايير البحث المحددة.
                                    @else
                                        لا توجد كتب متاحة حالياً.
                                    @endif
                                </p>
                                @if($search || $statusFilter !== 'published' || $publisherFilter)
                                    <button wire:click="clearFilters" 
                                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">
                                        مسح المرشحات
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        </main>
    </div>
</div>

{{-- الأنماط المخصصة للنص المقطوع --}}
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>