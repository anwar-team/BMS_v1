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

            <!-- Statistics Section -->
            <div class="relative">
                <div class="pattern-top top-0"></div>
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="mb-8 text-center">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-12 h-12">
                            <h2 class="text-3xl text-green-800 font-bold">إحصائيات المكتبة</h2>
                        </div>
                        <p class="text-gray-600">نظرة شاملة على محتويات مكتبتنا الرقمية</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                        <!-- Total Books -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                            <div class="relative">
                                <img src="{{ asset('images/mask-group0.svg') }}" alt="Background" class="absolute left-0 top-0 w-32 h-32">
                                <div class="p-6">
                                    <div class="flex justify-around items-center">
                                        <div class="bg-green-100 rounded-lg p-3">
                                            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-2xl text-green-800 font-bold mb-1">{{ number_format($stats['total_books']) }}</h3>
                                            <p class="text-sm text-gray-600">إجمالي الكتب</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Authors -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                            <div class="relative">
                                <img src="{{ asset('images/mask-group0.svg') }}" alt="Background" class="absolute left-0 top-0 w-32 h-32">
                                <div class="p-6">
                                    <div class="flex justify-around items-center">
                                        <div class="bg-blue-100 rounded-lg p-3">
                                            <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-2xl text-green-800 font-bold mb-1">{{ number_format($stats['total_authors']) }}</h3>
                                            <p class="text-sm text-gray-600">إجمالي المؤلفين</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Pages -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                            <div class="relative">
                                <img src="{{ asset('images/mask-group0.svg') }}" alt="Background" class="absolute left-0 top-0 w-32 h-32">
                                <div class="p-6">
                                    <div class="flex justify-around items-center">
                                        <div class="bg-purple-100 rounded-lg p-3">
                                            <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-2xl text-green-800 font-bold mb-1">{{ number_format($stats['total_pages']) }}</h3>
                                            <p class="text-sm text-gray-600">إجمالي الصفحات</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Sections -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                            <div class="relative">
                                <img src="{{ asset('images/mask-group0.svg') }}" alt="Background" class="absolute left-0 top-0 w-32 h-32">
                                <div class="p-6">
                                    <div class="flex justify-around items-center">
                                        <div class="bg-orange-100 rounded-lg p-3">
                                            <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-2xl text-green-800 font-bold mb-1">{{ number_format($stats['total_sections']) }}</h3>
                                            <p class="text-sm text-gray-600">إجمالي الأقسام</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

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

            {{-- استخدام Livewire Component للكتب --}}
            @livewire('books-table', [
                'showSearch' => false,
                'showFilters' => true,
                'title' => 'الكتب',
                'perPage' => 10,
                'showPagination' => true,
                'showPerPageSelector' => false
            ])
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

            {{-- استخدام Livewire Component للمؤلفين --}}
            @livewire('authors-table', [
                'showSearch' => false,
                'showFilters' => true,
                'title' => 'المؤلفين',
                'perPage' => 10,
                'showPagination' => true,
                'showPerPageSelector' => false
            ])
        </section>
    </div>
    </main>
    </div>
</x-superduper.main>