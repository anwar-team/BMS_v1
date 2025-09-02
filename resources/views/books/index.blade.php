<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">
            <div class="relative">
                <div class="pattern-top top-24"></div>
                
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    <!-- Page Title Section -->
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                            <h1 class="text-4xl text-green-800 font-bold">مكتبة الكتب</h1>
                        </div>
                        <p class="text-lg text-gray-600">استكشف مجموعة كبيرة من الكتب الرقمية</p>
                    </div>

                    @if($books->count() > 0)
                        <!-- Books Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                            @foreach($books as $book)
                                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                                    <!-- Book Cover -->
                                    <div class="aspect-[3/4] bg-gray-100">
                                        @if($book->cover_image)
                                            <img src="{{ asset('storage/' . $book->cover_image) }}" 
                                                 alt="{{ $book->title }}" 
                                                 class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Book Info -->
                                    <div class="p-4">
                                        <!-- Card Title with Icon -->
                                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-5 h-5">
                                            <h4 class="text-sm font-semibold text-green-800">معلومات الكتاب</h4>
                                        </div>

                                        <!-- Book Title -->
                                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 hover:text-green-700">
                                            <a href="{{ route('books.details', $book->id) }}" class="cursor-pointer">
                                                {{ $book->title }}
                                            </a>
                                        </h3>

                                        <!-- Author -->
                                        @if($book->authors->count() > 0)
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-xs font-medium text-gray-500">المؤلف:</span>
                                                <span class="text-sm text-gray-700">
                                                    @foreach($book->mainAuthors as $author)
                                                        {{ $author->name }}@if(!$loop->last), @endif
                                                    @endforeach
                                                    @if($book->mainAuthors->count() == 0)
                                                        @foreach($book->authors->take(1) as $author)
                                                            {{ $author->name }}
                                                        @endforeach
                                                    @endif
                                                </span>
                                            </div>
                                        @endif

                                        <!-- Publisher -->
                                        @if($book->publisher)
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-xs font-medium text-gray-500">الناشر:</span>
                                                <span class="text-sm text-gray-700">{{ $book->publisher->name }}</span>
                                            </div>
                                        @endif

                                        <!-- Book Stats Grid -->
                                        <div class="grid grid-cols-2 gap-2 mb-3">
                                            @if($book->pages()->count() > 0)
                                                <div class="text-center bg-gray-50 rounded-md py-2">
                                                    <div class="text-sm font-medium text-green-700">عدد الصفحات:</div>
                                                    <div class="text-xs text-gray-600">{{ $book->pages()->count() }} صفحة</div>
                                                </div>
                                            @endif
                                            
                                            @if($book->volumes_count)
                                                <div class="text-center bg-gray-50 rounded-md py-2">
                                                    <div class="text-sm font-medium text-green-700">عدد المجلدات:</div>
                                                    <div class="text-xs text-gray-600">{{ $book->volumes_count }} مجلد</div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Status -->
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="text-xs font-medium text-gray-500">حالة الكتاب:</span>
                                            <span class="px-2 py-1 rounded text-xs
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

                                        <!-- Published Year -->
                                        @if($book->published_year)
                                            <div class="flex items-center gap-2 mb-3">
                                                <span class="text-xs font-medium text-gray-500">سنة النشر:</span>
                                                <span class="text-sm text-gray-700">{{ $book->published_year }}</span>
                                            </div>
                                        @endif

                                        <!-- Description Preview -->
                                        @if($book->description)
                                            <p class="text-xs text-gray-600 line-clamp-2 mb-3 border-t border-gray-100 pt-2">
                                                {{ Str::limit($book->description, 80) }}
                                            </p>
                                        @endif

                                        <!-- Action Buttons -->
                                        <div class="flex gap-2 mt-4">
                                            <a href="{{ route('books.details', $book->id) }}" 
                                               class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium py-2 px-3 rounded-md text-center transition-colors duration-200">
                                                التفاصيل
                                            </a>
                                            
                                            @if($book->pages()->count() > 0)
                                                <a href="{{ route('book.read', $book->id) }}" 
                                                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium py-2 px-3 rounded-md text-center transition-colors duration-200">
                                                    قراءة
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                            <div class="flex justify-center">
                                {{ $books->links() }}
                            </div>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8">
                            <div class="text-center py-12">
                                <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
                                </svg>
                                <h3 class="text-xl font-medium text-gray-900 mb-2">لا توجد كتب متاحة حالياً</h3>
                                <p class="text-gray-500">سيتم إضافة المزيد من الكتب قريباً</p>
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>

    <!-- Add custom styles for line-clamp if not available -->
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</x-superduper.main>
