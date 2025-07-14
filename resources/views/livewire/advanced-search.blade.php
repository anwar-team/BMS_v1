<div class="bg-gray-50 min-h-screen" dir="rtl">
    <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 font-tajawal mb-8">البحث المتقدم</h1>
        
        <!-- Search form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="query" class="block text-sm font-medium text-gray-700 mb-1">كلمة البحث</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="query"
                            wire:model.live.debounce.300ms="query"
                            placeholder="اكتب كلمة أو عبارة للبحث..."
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"
                        />
                        <button wire:click="search" class="absolute left-2 top-2 text-gray-400 hover:text-primary-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div>
                    <label for="searchType" class="block text-sm font-medium text-gray-700 mb-1">نوع البحث</label>
                    <select
                        id="searchType"
                        wire:model.live="searchType"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"
                    >
                        <option value="all">الكل</option>
                        <option value="books">الكتب</option>
                        <option value="chapters">الفصول</option>
                        <option value="pages">الصفحات</option>
                    </select>
                </div>
                
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">الترتيب</label>
                    <select
                        id="sort"
                        wire:model.live="sort"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"
                    >
                        <option value="relevance">الأكثر صلة</option>
                        <option value="most_read">الأكثر قراءة</option>
                        <option value="newest">الأحدث</option>
                        <option value="oldest">الأقدم</option>
                    </select>
                </div>
            </div>
            
            <!-- Advanced filters -->
            <div class="mt-6 pt-4 border-t border-gray-200">
                <h3 class="text-sm font-medium text-gray-700 mb-3">تصفية النتائج</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="sectionId" class="block text-sm font-medium text-gray-700 mb-1">القسم</label>
                        <select
                            id="sectionId"
                            wire:model.live="sectionId"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"
                        >
                            <option value="">جميع الأقسام</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="authorId" class="block text-sm font-medium text-gray-700 mb-1">المؤلف</label>
                        <select
                            id="authorId"
                            wire:model.live="authorId"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"
                        >
                            <option value="">جميع المؤلفين</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}">{{ $author->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search results -->
        <div>
            @if($query)
                <div class="mb-4">
                    <h2 class="text-lg font-medium text-gray-900">
                        {{ $totalResults }} نتيجة بحث عن "{{ $query }}"
                    </h2>
                </div>
                
                @if($totalResults > 0)
                    <!-- Book results -->
                    @if(isset($results['books']) && count($results['books']) > 0)
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-3 pb-2 border-b">الكتب ({{ count($results['books']) }})</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($results['books'] as $book)
                                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                                        <a href="{{ route('book.show', $book->slug) }}" class="block">
                                            <div class="aspect-w-3 aspect-h-4 bg-gray-200">
                                                @if($book->cover_image)
                                                    <img src="{{ $book->cover_image }}" alt="{{ $book->title }}" class="object-cover w-full h-full">
                                                @else
                                                    <div class="flex items-center justify-center h-full bg-gray-100 text-gray-400">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="p-4">
                                                <h4 class="font-bold text-gray-900 mb-1 line-clamp-2">{{ $book->title }}</h4>
                                                <p class="text-sm text-gray-600 mb-2">
                                                    @if($book->mainAuthors->count() > 0)
                                                        {{ $book->mainAuthors->first()->full_name }}
                                                    @endif
                                                </p>
                                                <p class="text-sm text-gray-500 line-clamp-2">{{ $book->description }}</p>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Chapter results -->
                    @if(isset($results['chapters']) && count($results['chapters']) > 0)
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-3 pb-2 border-b">الفصول ({{ count($results['chapters']) }})</h3>
                            
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 divide-y">
                                @foreach($results['chapters'] as $chapter)
                                    <div class="p-4 hover:bg-gray-50">
                                        <a href="{{ route('book.show', ['slug' => $chapter->book->slug, 'chapterId' => $chapter->id]) }}" class="block">
                                            <h4 class="font-medium text-gray-900 mb-1">{{ $chapter->title }}</h4>
                                            <p class="text-sm text-gray-600">
                                                من كتاب: {{ $chapter->book->title }}
                                            </p>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Page results -->
                    @if(isset($results['pages']) && count($results['pages']) > 0)
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-3 pb-2 border-b">الصفحات ({{ count($results['pages']) }})</h3>
                            
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 divide-y">
                                @foreach($results['pages'] as $page)
                                    <div class="p-4 hover:bg-gray-50">
                                        <a href="{{ route('book.show', ['slug' => $page->book->slug, 'pageId' => $page->id]) }}" class="block">
                                            <h4 class="font-medium text-gray-900 mb-1">
                                                صفحة {{ $page->page_number }} 
                                                @if($page->chapter)
                                                    - {{ $page->chapter->title }}
                                                @endif
                                            </h4>
                                            <p class="text-sm text-gray-600 mb-2">
                                                من كتاب: {{ $page->book->title }}
                                            </p>
                                            <p class="text-sm text-gray-500 line-clamp-2">
                                                {!! preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="bg-yellow-100">$1</span>', strip_tags($page->content)) !!}
                                            </p>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-4 text-gray-500">لم يتم العثور على نتائج مطابقة لبحثك</p>
                        <p class="mt-2 text-gray-500 text-sm">جرب استخدام كلمات مفتاحية مختلفة أو تغيير خيارات البحث</p>
                    </div>
                @endif
            @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p class="mt-4 text-gray-500">أدخل كلمة بحث للعثور على الكتب والفصول والصفحات</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* RTL specific adjustments */
    [dir="rtl"] .line-clamp-2 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    
    /* Typography */
    .font-tajawal {
        font-family: 'Tajawal', sans-serif;
    }
    
    /* Primary colors */
    .bg-primary-50 {
        background-color: #f8f9e8;
    }
    
    .text-primary-600 {
        color: #5D6019;
    }
    
    .focus\:border-primary-500:focus {
        border-color: #5D6019;
    }
    
    .focus\:ring-primary-500:focus {
        --tw-ring-color: #5D6019;
    }
    
    .hover\:text-primary-600:hover {
        color: #5D6019;
    }
</style> 