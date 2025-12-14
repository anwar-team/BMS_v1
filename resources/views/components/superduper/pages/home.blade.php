<x-superduper.main>

    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">
            <!-- Search Section -->
            <x-superduper.components.search-section :stats="$stats" />

            <!-- Book Categories-->

            <!-- Book Categories-->
            <div class="relative z-1">
                <div class="pattern-top top-0"></div>
                <section class="relative z-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div class="mb-12 z-1">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-8 h-8">
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
    <div class="relative z-1">
        <div class="pattern-top top-10"></div>
        <!-- end of background pattern-->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-1">
            <div class="flex items-center gap-3 mb-8">
                <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-8 h-8">
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
                <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-8 h-8">
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