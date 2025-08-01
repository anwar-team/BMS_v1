<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper ">
            <!-- background pattern-->
            <div class="relative">
                <div class="pattern-top top-24"></div>
                <div class="pattern-top top-80"></div>
                <!-- end of background pattern-->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                            <div>
                                <h2 class="text-4xl text-green-800 font-bold">أقسام الكتب</h2>
                                @if(request('search'))
                                    <p class="text-sm text-gray-600 mt-1">نتائج البحث عن: "{{ request('search') }}"</p>
                                @endif
                            </div>
                        </div>

                        {{-- Search Box - مربع البحث المفعل --}}
                        <div class="relative max-w-md mx-auto">
                            <form method="GET" action="{{ route('categories') }}">
                                <input type="text" 
                                       name="search"
                                       value="{{ request('search') }}"
                                       placeholder="ابحث في الأقسام..."
                                       class="w-full px-4 py-3 pr-12 text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400 hover:text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        {{-- End Search Box --}}
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
                            <div class="col-span-full text-center py-12">
                                <div class="text-gray-500">
                                    @if(request('search'))
                                        {{-- رسالة عند عدم وجود نتائج بحث --}}
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <p class="text-lg font-medium text-gray-900 mb-1">لا توجد أقسام تطابق البحث</p>
                                        <p class="text-sm text-gray-500 mb-4">لم يتم العثور على أقسام تحتوي على "{{ request('search') }}"</p>
                                        <a href="{{ route('categories') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                            عرض جميع الأقسام
                                        </a>
                                    @else
                                        {{-- رسالة عند عدم وجود أقسام أصلاً --}}
                                        <p class="text-gray-500">لا توجد أقسام متوفرة حالياً</p>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>
            </section>
    </div>
    </main>
    </div>
</x-superduper.main>