<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">
            <div class="relative">
                <div class="pattern-top top-24"></div>
                
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    <!-- Author Title Section -->
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-8 h-8">
                            <h1 class="text-4xl text-green-800 font-bold">{{ $author->full_name }}</h1>
                        </div>
                    </div>



                    <!-- Author Biography Section -->
                    @if($author->biography)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8 mb-8">
                            <div class="flex items-center gap-3 mb-6">
                                
                                <h3 class="text-2xl font-semibold text-[#5D6019]">السيرة الذاتية</h3>
                            </div>
                            <div class="text-lg text-gray-700 leading-relaxed whitespace-pre-line">
                                {{ $author->biography }}
                            </div>
                        </div>
                    @endif

                    <!-- Author Books Section -->
                    <div class="mb-12">
                        <div class="flex items-center gap-3 mb-6">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-8 h-8">
                            <h3 class="text-2xl font-semibold text-[#5D6019]">كتب المؤلف</h3>
                        </div>
                        
                        {{-- استخدام Livewire Component للكتب --}}
                        @if($author->books()->count() > 0)
                            @livewire('books-table', [
                                'author_id' => $author->id,
                                'showSearch' => false,
                                'showFilters' => false,
                                'title' => 'كتب المؤلف',
                                'perPage' => 10,
                                'showPagination' => true,
                                'showPerPageSelector' => true
                            ])
                        @else
                            <div class="text-center py-8 bg-white rounded-lg shadow-md border border-gray-200">
                                <div class="text-gray-400 mb-2">
                                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500">لا توجد كتب منشورة لهذا المؤلف حالياً</p>
                                <p class="text-sm text-gray-400 mt-2">سيتم إضافة الكتب عند نشرها</p>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Custom Styles -->
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</x-superduper.main>