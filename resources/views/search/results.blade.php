
<x-superduper.main>
    <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
        <div class="mb-12 z-10">
            <div class="flex items-center gap-3 mb-8">
                <img src="{{ asset('images/group0.svg') }}" alt="نتائج البحث" class="w-16 h-16">
                <h2 class="text-4xl text-green-800 font-bold">نتائج البحث</h2>
            </div>
        </div>

<div class="container mx-auto px-4 py-8" dir="rtl">
    <div class="max-w-4xl mx-auto">
        <!-- Search Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">نتائج البحث</h1>
            @if(request('query'))
                <p class="text-gray-600">البحث عن: <span class="font-semibold">"{{ request('query') }}"</span></p>
            @endif
            @if($results->total() > 0)
                <p class="text-sm text-gray-500 mt-1">تم العثور على {{ $results->total() }} نتيجة</p>
            @endif
        </div>

        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" action="{{ route('search.results') }}" class="space-y-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="query" 
                               value="{{ request('query') }}"
                               placeholder="ابحث في النصوص..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        بحث
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المؤلف</label>
                        <select name="author" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">جميع المؤلفين</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}" {{ request('author') == $author->id ? 'selected' : '' }}>
                                    {{ $author->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">القسم</label>
                        <select name="book_section" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">جميع الأقسام</option>
                            @foreach($bookSections as $section)
                                <option value="{{ $section->id }}" {{ request('book_section') == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results -->
        @if($results->count() > 0)
            <div class="space-y-4">
                @foreach($results as $page)
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">
                                    <a href="{{ route('books.show', $page->book->slug) }}" 
                                       class="hover:text-blue-600 transition-colors">
                                        {{ $page->book->title }}
                                    </a>
                                </h3>
                                @if($page->book->authors->count() > 0)
                                    <p class="text-sm text-gray-600">
                                        المؤلف: 
                                        @foreach($page->book->authors as $author)
                                            <a href="{{ route('authors.show', $author->slug) }}" 
                                               class="hover:text-blue-600 transition-colors">
                                                {{ $author->name }}
                                            </a>@if(!$loop->last), @endif
                                        @endforeach
                                    </p>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">
                                صفحة {{ $page->page_number }}
                            </div>
                        </div>
                        
                        <div class="text-gray-700 leading-relaxed mb-3">
                            {!! Str::limit(strip_tags($page->content), 300) !!}
                        </div>
                        
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <div>
                                @if($page->volume)
                                    المجلد: {{ $page->volume->title }}
                                @endif
                                @if($page->chapter)
                                    | الفصل: {{ $page->chapter->title }}
                                @endif
                            </div>
                            <a href="{{ route('books.show', $page->book->slug) }}?page={{ $page->page_number }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium">
                                اقرأ المزيد ←
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $results->appends(request()->query())->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">لم يتم العثور على نتائج</h3>
                <p class="text-gray-500 mb-4">جرب تعديل كلمات البحث أو الفلاتر</p>
                <a href="{{ route('search.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    بحث جديد
                </a>
            </div>
        @endif
    </section>

    <x-slot name="head">
        <style>
            .highlight {
                background-color: #fef3c7;
                padding: 0 2px;
                border-radius: 2px;
            }
        </style>
    </x-slot>
</x-superduper.main>