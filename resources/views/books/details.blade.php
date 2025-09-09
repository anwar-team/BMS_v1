<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">
            <div class="relative">
                <div class="pattern-top top-24"></div>
                
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    <!-- Book Title Section -->
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                            <h1 class="text-4xl text-green-800 font-bold">تفاصيل الكتاب</h1>
                        </div>
                    </div>

                    <!-- Main Book Details Section -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8 mb-8">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Book Cover Image -->
                            <div class="lg:col-span-1">
                                @if($book->cover_image)
                                    <img src="{{ asset('storage/' . $book->cover_image) }}" 
                                         alt="{{ $book->title }}" 
                                         class="w-full h-auto rounded-lg shadow-md">
                                @else
                                    <div class="w-full h-80 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <span class="text-gray-500">لا توجد صورة للكتاب</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Book Information Card -->
                            <div class="lg:col-span-2">
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <!-- Card Title with Icon -->
                                    <div class="flex items-center gap-3 mb-6">
                                        <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-6 h-6">
                                        <h3 class="text-xl font-semibold text-green-800">{{ $book->title }}</h3>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @if($book->authors->count() > 0)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700">المؤلف:</span>
                                                <span class="text-gray-600">
                                                    @foreach($book->mainAuthors as $author)
                                                        <a href="{{ route('authors.details', $author->id) }}" 
                                                           class="text-green-600 hover:text-green-800 hover:underline transition-colors duration-200">
                                                            {{ $author->full_name }}
                                                        </a>@if(!$loop->last), @endif
                                                    @endforeach
                                                    @if($book->mainAuthors->count() == 0)
                                                        @foreach($book->authors->take(1) as $author)
                                                            <a href="{{ route('authors.details', $author->id) }}" 
                                                               class="text-green-600 hover:text-green-800 hover:underline transition-colors duration-200">
                                                                {{ $author->full_name }}
                                                            </a>
                                                        @endforeach
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                        
                                        @if($book->publisher)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700">الناشر:</span>
                                                <span class="text-gray-600">
                                                    <a href="{{ route('publishers.details', $book->publisher->id) }}" 
                                                       class="text-green-600 hover:text-green-800 hover:underline transition-colors duration-200">
                                                        {{ $book->publisher->name }}
                                                    </a>
                                                </span>
                                            </div>
                                        @endif
                                        

                                        @if($book->pages()->count() > 0)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700">عدد الصفحات:</span>
                                                <span class="text-gray-600">{{ $book->pages()->count() }} صفحة</span>
                                            </div>
                                        @endif
                                        
                                        @if($book->volumes_count)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700">عدد المجلدات:</span>
                                                <span class="text-gray-600">{{ $book->volumes_count }} مجلد</span>
                                            </div>
                                        @endif
                                        
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-700">حالة الكتاب:</span>
                                            <span class="px-2 py-1 rounded text-sm
                                                @if($book->status === 'published') bg-green-100 text-green-800
                                                @elseif($book->status === 'review') bg-yellow-100 text-yellow-800
                                                @elseif($book->status === 'draft') bg-gray-100 text-gray-800
                                                @else bg-red-100 text-red-800
                                                @endif
                                            ">
                                                @if($book->status === 'published') منشور
                                                @elseif($book->status === 'review') قيد المراجعة
                                                @elseif($book->status === 'draft') مسودة
                                                @else مؤرشف
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="mt-6 flex flex-wrap gap-3">
                                        @if($book->pages()->count() > 0)
                                            <a href="{{ route('book.read', $book->id) }}" 
                                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
                                                </svg>
                                                قراءة الكتاب
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Book Overview Section -->
                    @if($book->description)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8 mb-8">
                            <div class="flex items-center gap-3 mb-6">
                                <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-8 h-8">
                                <h3 class="text-2xl font-semibold text-[#5D6019]">نبذة عن الكتاب</h3>
                            </div>
                            <div class="text-lg text-gray-700 leading-relaxed">
                                {{ $book->description }}
                            </div>
                        </div>
                    @endif

                    <!-- Book Summary Section -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-8 h-8">
                            <h3 class="text-2xl font-semibold text-[#5D6019]">ملخص الكتاب</h3>
                        </div>
                        <div class="text-lg text-gray-700 leading-relaxed">
                            @if($book->summary ?? false)
                                {{ $book->summary }}
                            @else
                                @if($book->description)
                                    <!-- عرض جزء من الوصف كملخص -->
                                    <p class="mb-4">
                                        {{ Str::limit($book->description, 300, '...') }}
                                    </p>
                                    @if(strlen($book->description) > 300)
                                        <p class="text-sm text-gray-500 italic">
                                            يمكنك قراءة الوصف الكامل في قسم "نبذة عن الكتاب" أعلاه.
                                        </p>
                                    @endif
                                @else
                                    <div class="text-center py-8">
                                        <div class="text-gray-400 mb-2">
                                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-gray-500">لا يتوفر ملخص لهذا الكتاب حالياً</p>
                                        <p class="text-sm text-gray-400 mt-2">يمكنك البدء بقراءة الكتاب لاكتشاف محتواه</p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</x-superduper.main>
