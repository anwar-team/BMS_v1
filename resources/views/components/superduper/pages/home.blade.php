<x-superduper.main>

    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">

            <!--
========================================
          My Main Aziz     
========================================
 -->
            <!-- <div class="bg-white min-h-screen">
                 Main Hero Section 
                <section class="relative overflow-hidden">
                    <img
                        src="{{ asset('images/whats-app-image-2025-03-20-at-1-58-04-pm-10.png') }}"
                        alt="Library background"
                        class="absolute inset-0 w-full h-96 object-cover">
                    <div class="absolute inset-0 bg-black/30"></div>

                    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-20">
                        <div class="max-w-3xl mx-auto text-center">
                            <h1 class="text-5xl text-white font-bold mb-6 leading-tight">
                                مكتبة تكاملت موضوعاتها<br>وكتبها
                            </h1>
                            <p class="text-xl text-white mb-10">
                                اكتشف آلاف الكتب في الحديث، الفقه، الأدب، البلاغة، و التاريخ و الأنساب و غيرها الكثير متاحة لك في مكان واحد
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4 mb-10 justify-center">
                                <button class="bg-white text-green-800 px-8 py-3 rounded-3xl font-bold shadow-md">
                                    المؤلفين
                                </button>
                                <button class="bg-green-700 text-white px-8 py-3 rounded-3xl font-bold shadow-md relative">
                                    <span class="absolute inset-0 border-2 border-green-900 rounded-3xl"></span>
                                    محتوى الكتب
                                </button>
                                <button class="bg-white text-green-800 px-8 py-3 rounded-3xl font-bold shadow-md">
                                    عناوين الكتب
                                </button>
                            </div>

                            <div class="max-w-xl mx-auto bg-white rounded-full px-6 py-3 flex items-center gap-3">
                                <img src="{{ asset('images/iconly-light-search0.svg') }}" alt="Search" class="w-6 h-6">
                                <span class="text-gray-500">إبحث في محتوى الكتب ...</span>
                                <img src="{{ asset('images/iconly-bold-send0.svg') }}" alt="Search icon" class="w-5 h-5">
                            </div>
                        </div>
                    </div>
                </section> -->
            <x-superduper.components.banner />


            <!-- Book Categories-->
            <div class="relative">
                <div class="pattern-top top-0"></div>
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                            <h2 class="text-4xl text-green-800 font-bold">أقسام الكتب</h2>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @forelse ($sections as $section)
                            <a href="{{ route('show-all', ['type' => 'books', 'section' => $section->slug]) }}" 
                               class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                                <div class="relative">
                                    <img
                                        src="{{ asset('images/mask-group0.svg') }}"
                                        alt="Category image"
                                        class="absolute left-0 top-0 w-32 h-32">
                                    <div class="p-8">
                                        <div class="flex justify-around items-center">
                                            <img src="{{ $section->logo_path ? asset($section->logo_path) : asset('images/group1.svg') }}" 
                                                 alt="Icon" class="w-16 h-16">
                                            <div>
                                                <h3 class="text-xl text-green-800 font-bold mb-1">{{ $section->name }}</h3>
                                                <p class="text-sm text-gray-600">{{ $section->books_count }} كتاب</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-full text-center py-8">
                                <p class="text-gray-500">لا توجد أقسام متوفرة حالياً</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-12 text-center">
                        <a href="{{ route('categories') }}" class="bg-white text-green-800 border border-green-800 px-8 py-3 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white font-bold shadow-md inline-block">
                            عرض جميع الأقسام
                        </a>
                    </div>
                </section>
            </div>

    <!-- Books Table -->
    <!-- background pattern-->
    <div class="relative">
        <div class="pattern-top top-10"></div>
        <!-- end of background pattern-->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10">
            <div class="flex items-center gap-3 mb-8">
                <img src="{{ asset('images/group7.svg') }}" alt="Icon" class="w-16 h-16">
                <h2 class="text-4xl text-green-800 font-bold">الكتب</h2>
            </div>

            <div class="flex flex-wrap gap-4 mb-8">
                <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                    الكتب المفتوحة مؤخراً
                </button>
                <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                    أكثر الكتب قراءةً
                </button>
                <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                    كتب مضافة حديثاً
                </button>
                <a href="{{ route('show-all', ['type' => 'books']) }}" class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                    جميع الكتب
                </a>
            </div>

            {{-- Books Table --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    #
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    عنوان الكتاب
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    المؤلف
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    القسم
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    سنة النشر
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($books as $book)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium">
                                            <a href="{{ route('book.read', ['bookId' => $book->id]) }}" 
                                               class="text-green-700 hover:text-green-900 hover:underline transition-colors duration-200">
                                                {{ $book->title }}
                                            </a>
                                        </div>
                                        @if($book->description)
                                            <div class="text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($book->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $book->authors->pluck('full_name')->implode(', ') ?: 'غير محدد' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($book->bookSection)
                                            <a href="{{ route('show-all', ['type' => 'books', 'section' => $book->bookSection->slug]) }}" 
                                               class="text-green-600 hover:text-green-800 hover:underline">
                                                {{ $book->bookSection->name }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">غير محدد</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $book->published_year ?: 'غير محدد' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center">
                                        <div class="text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-lg font-medium text-gray-900 mb-1">لا توجد كتب متوفرة</p>
                                            <p class="text-sm text-gray-500">سيتم إضافة الكتب قريباً</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination for Books (Simple for Homepage) --}}
                <div class="px-6 py-4 flex items-center justify-between border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center gap-2">
                        @if($books->hasPages())
                            @if($books->onFirstPage())
                                <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                            @else
                                <a href="{{ $books->previousPageUrl() }}" class="p-2 rounded-full hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </a>
                            @endif

                            @if($books->hasMorePages())
                                <a href="{{ $books->nextPageUrl() }}" class="p-2 rounded-full hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @else
                                <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @endif
                        @endif
                        
                        <span class="text-sm text-gray-600 mx-2">
                            {{ $books->firstItem() ?: 0 }}-{{ $books->lastItem() ?: 0 }} من {{ $books->total() }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">10 كتب في الصفحة</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Authors Section -->
    <!-- background pattern-->
    <div class="relative">
        <div class="pattern-top top-10"></div>
        <!-- end of background pattern-->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10">
            <div class="flex items-center gap-3 mb-8">
                <img src="{{ asset('images/group8.svg') }}" alt="Icon" class="w-16 h-16">
                <h2 class="text-4xl text-green-800 font-bold">المؤلفين</h2>
            </div>

            <div class="flex flex-wrap gap-4 mb-8">
                <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                    المؤلفون المشهورون
                </button>
                <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                    الأكثر كتابة
                </button>
                <a href="{{ route('show-all', ['type' => 'authors']) }}" class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                    جميع المؤلفين
                </a>
            </div>

            {{-- Authors Table --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    #
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    اسم المؤلف
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    عدد الكتب
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    المذهب
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($authors as $author)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $author->full_name }}</div>
                                        @if($author->biography)
                                            <div class="text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($author->biography, 60) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $author->books_count }} كتاب
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $author->madhhab ?: 'غير محدد' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center">
                                        <div class="text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <p class="text-lg font-medium text-gray-900 mb-1">لا يوجد مؤلفون متوفرون</p>
                                            <p class="text-sm text-gray-500">سيتم إضافة المؤلفين قريباً</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination for Authors (Simple for Homepage) --}}
                <div class="px-6 py-4 flex items-center justify-between border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center gap-2">
                        @if($authors->hasPages())
                            @if($authors->onFirstPage())
                                <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                            @else
                                <a href="{{ $authors->previousPageUrl() }}" class="p-2 rounded-full hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </a>
                            @endif

                            @if($authors->hasMorePages())
                                <a href="{{ $authors->nextPageUrl() }}" class="p-2 rounded-full hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @else
                                <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @endif
                        @endif
                        
                        <span class="text-sm text-gray-600 mx-2">
                            {{ $authors->firstItem() ?: 0 }}-{{ $authors->lastItem() ?: 0 }} من {{ $authors->total() }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">10 مؤلفين في الصفحة</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
    </main>
    </div>
</x-superduper.main>