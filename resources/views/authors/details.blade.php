<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">
            <div class="relative">
                <div class="pattern-top top-24"></div>
                
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    <!-- Author Title Section -->
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group8.svg') }}" alt="Icon" class="w-16 h-16">
                            <h1 class="text-4xl text-green-800 font-bold">تفاصيل المؤلف</h1>
                        </div>
                    </div>

                    <!-- Main Author Details Section -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8 mb-8">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Author Image -->
                            <div class="lg:col-span-1">
                                @if($author->image)
                                    <img src="{{ asset('storage/' . $author->image) }}" 
                                         alt="{{ $author->full_name }}" 
                                         class="w-full h-auto rounded-lg shadow-md">
                                @else
                                    <div class="w-full h-80 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Author Information Card -->
                            <div class="lg:col-span-2">
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <!-- Card Title with Icon -->
                                    <div class="flex items-center gap-3 mb-6">
                                        <img src="{{ asset('images/group8.svg') }}" alt="Icon" class="w-6 h-6">
                                        <h3 class="text-xl font-semibold text-green-800">{{ $author->full_name }}</h3>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-700">عدد الكتب:</span>
                                            <span class="text-gray-600">{{ $author->books->count() }} كتاب</span>
                                        </div>
                                        
                                        @if($author->madhhab)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700">المذهب:</span>
                                                <span class="text-gray-600">{{ $author->madhhab }}</span>
                                            </div>
                                        @endif
                                        
                                        @if($author->birth_date)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700">تاريخ الميلاد:</span>
                                                <span class="text-gray-600">{{ $author->birth_date->format('Y-m-d') }}</span>
                                            </div>
                                        @endif
                                        
                                        @if($author->death_date)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700">تاريخ الوفاة:</span>
                                                <span class="text-gray-600">{{ $author->death_date->format('Y-m-d') }}</span>
                                            </div>
                                        @endif
                                        
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-700">الحالة:</span>
                                            <span class="px-2 py-1 rounded text-sm
                                                @if($author->is_living) bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800
                                                @endif
                                            ">
                                                @if($author->is_living) على قيد الحياة
                                                @else متوفى
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($author->biography)
                                        <!-- Biography Section -->
                                        <div class="mt-6 pt-6 border-t border-gray-200">
                                            <div class="flex items-center gap-2 mb-3">
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <h4 class="text-lg font-semibold text-gray-800">السيرة الذاتية</h4>
                                            </div>
                                            <div class="text-gray-700 leading-relaxed whitespace-pre-line bg-gray-50 p-4 rounded-lg">
                                                {{ $author->biography }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Author Biography Section -->
                    @if($author->biography)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8 mb-8">
                            <div class="flex items-center gap-3 mb-6">
                                <img src="{{ asset('images/group8.svg') }}" alt="Icon" class="w-8 h-8">
                                <h3 class="text-2xl font-semibold text-[#5D6019]">السيرة الذاتية</h3>
                            </div>
                            <div class="text-lg text-gray-700 leading-relaxed whitespace-pre-line">
                                {{ $author->biography }}
                            </div>
                        </div>
                    @endif

                    <!-- Author Books Section -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-8 h-8">
                                <h3 class="text-2xl font-semibold text-[#5D6019]">كتب المؤلف</h3>
                            </div>
                            
                            @if($books->count() > 0)
                                <!-- View Toggle Buttons -->
                                <div class="flex bg-gray-100 rounded-lg p-1">
                                    <button id="tableViewBtn" class="px-4 py-2 text-sm font-medium rounded-md bg-white text-green-700 shadow-sm transition-all duration-200">
                                        <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18m-9 8h9"></path>
                                        </svg>
                                        عرض جدول
                                    </button>
                                    <button id="cardViewBtn" class="px-4 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-green-700 transition-all duration-200">
                                        <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"></path>
                                        </svg>
                                        عرض بطاقات
                                    </button>
                                </div>
                            @endif
                        </div>
                        
                        @if($books->count() > 0)
                            <!-- Table View -->
                            <div id="tableView" class="overflow-x-auto">
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
                                                القسم
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                تاريخ الإضافة
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                الإجراءات
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach ($books as $book)
                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                                    {{ $loop->iteration + ($books->currentPage() - 1) * $books->perPage() }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $book->title }}</div>
                                                    @if($book->description)
                                                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($book->description, 50) }}</div>
                                                    @endif
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
                                                    {{ $book->created_at->format('Y-m-d') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('books.details', $book->id) }}" 
                                                       class="text-green-600 hover:text-green-900 ml-3">
                                                        التفاصيل
                                                    </a>
                                                    @if($book->pages()->count() > 0)
                                                        <a href="{{ route('book.read', $book->id) }}" 
                                                           class="text-blue-600 hover:text-blue-900">
                                                            قراءة
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination for Books --}}
                            <div class="px-6 py-4 flex items-center justify-between border-t border-gray-200 bg-gray-50 mt-4">
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
                                            </button>
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
                            
                            <!-- Card View -->
                            <div id="cardView" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
                        @else
                            <div class="text-center py-8">
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

    <!-- JavaScript for View Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableViewBtn = document.getElementById('tableViewBtn');
            const cardViewBtn = document.getElementById('cardViewBtn');
            const tableView = document.getElementById('tableView');
            const cardView = document.getElementById('cardView');

            if (tableViewBtn && cardViewBtn && tableView && cardView) {
                // Table View Button Click
                tableViewBtn.addEventListener('click', function() {
                    // Show table view
                    tableView.classList.remove('hidden');
                    cardView.classList.add('hidden');
                    
                    // Update button styles
                    tableViewBtn.classList.add('bg-white', 'text-green-700', 'shadow-sm');
                    tableViewBtn.classList.remove('text-gray-600');
                    cardViewBtn.classList.remove('bg-white', 'text-green-700', 'shadow-sm');
                    cardViewBtn.classList.add('text-gray-600');
                });

                // Card View Button Click
                cardViewBtn.addEventListener('click', function() {
                    // Show card view
                    cardView.classList.remove('hidden');
                    tableView.classList.add('hidden');
                    
                    // Update button styles
                    cardViewBtn.classList.add('bg-white', 'text-green-700', 'shadow-sm');
                    cardViewBtn.classList.remove('text-gray-600');
                    tableViewBtn.classList.remove('bg-white', 'text-green-700', 'shadow-sm');
                    tableViewBtn.classList.add('text-gray-600');
                });
            }
        });
    </script>

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