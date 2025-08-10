<x-superduper.main>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $book->title }} - قراءة الكتاب</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
        <style>
            .font-tajawal {
                font-family: 'Tajawal', sans-serif;
            }
        </style>
    </head>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper bg-[#f8f5f0]">
            <!-- أنماط الخلفية -->
            <div class="relative">
                <div class="pattern-top top-24"></div>
                <!-- المحتوى الرئيسي -->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 md:py-12 lg:py-36">
                    <!-- عنوان الصفحة -->
                    <div class="mb-6 sm:mb-8 md:mb-10 lg:mb-12 z-10">
                        <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 mb-4 sm:mb-6">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16">
                            <h2 class="text-2xl sm:text-3xl md:text-4xl text-[#5D6019] font-bold font-tajawal">معاينة كتاب: {{ $book->title }}</h2>
                        </div>
                    </div>
                    <!-- Header -->
                    <header class="bg-white shadow-sm py-2 sm:py-3 px-4 sm:px-6 rounded-xl mb-4 sm:mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                            <div class="flex items-center w-full sm:w-auto">
                                <img src="{{ asset('images/logo-01.jpg') }}" alt="الشعار" class="h-10 sm:h-12 ml-3 sm:ml-4">
                                <div class="w-full sm:w-auto">
                                    <h1 class="text-xl sm:text-2xl font-bold text-[#5D6019] font-tajawal">{{ $book->title }}</h1>
                                    <p class="text-gray-600 text-base sm:text-lg">
                                        تأليف: 
                                        @if($book->authors->isNotEmpty())
                                            {{ $book->authors->pluck('name')->join('، ') }}
                                        @else
                                            غير محدد
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                                <div class="flex items-center bg-[#f0e9de] rounded-full px-3 py-1 sm:px-4 sm:py-1.5 w-full sm:w-auto">
                                    <span class="text-[#39100C] font-medium text-sm sm:text-base ml-2 sm:ml-2">الصفحة</span>
                                    <span class="bg-[#5D6019] text-white rounded-full w-6 h-6 sm:w-8 sm:h-8 flex items-center justify-center font-bold text-sm sm:text-base">{{ $navigationInfo['current_page_number'] }}</span>
                                    <span class="text-[#39100C] mx-1 sm:mx-2 text-sm sm:text-base">من</span>
                                    <span class="text-[#957717] font-bold text-sm sm:text-base">{{ $navigationInfo['total_pages'] }}</span>
                                </div>
                                <button class="bg-[#FF7300] hover:bg-[#e06600] text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg font-medium transition-colors flex items-center w-full sm:w-auto justify-center mt-2 sm:mt-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m0 4v2m-6 4h12m-6 4v2m-6-8h12a2 2 0 012 2v4a2 2 0 01-2 2H3a2 2 0 01-2-2v-4a2 2 0 012-2z" />
                                    </svg>
                                    English
                                </button>
                            </div>
                        </div>
                    </header>
                    <!-- Main Content -->
                    <div class="flex flex-col gap-4 sm:gap-6">
                        <!-- Toolbar -->
                        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-[#e0d9cc] p-2 sm:p-3">
                            <div class="flex flex-col sm:flex-row sm:flex-wrap items-center gap-2 sm:gap-3">
                                <div class="flex items-center border-b border-[#e0d9cc] pb-2 w-full sm:w-auto sm:border-0 sm:pb-0 sm:border-r sm:pr-4">
                                    <button id="decreaseFontSize" class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-[#f0e9de] hover:bg-[#e8e0d0] text-[#5D6019] font-bold transition-colors text-sm sm:text-base">
                                        A-
                                    </button>
                                    <span id="fontSizeDisplay" class="mx-1 sm:mx-2 text-[#39100C] font-medium text-sm sm:text-base">100%</span>
                                    <button id="increaseFontSize" class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-[#f0e9de] hover:bg-[#e8e0d0] text-[#5D6019] font-bold transition-colors text-sm sm:text-base">
                                        A+
                                    </button>
                                </div>
                                <div class="flex items-center flex-1 min-w-[150px] w-full sm:w-auto relative">
                                    <input type="text" id="searchInput" placeholder="ابحث في النص..." class="flex-1 px-3 py-1.5 sm:px-4 sm:py-2 border border-[#e0d9cc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#957717] font-tajawal text-sm">
                                    <button id="searchButton" class="mr-1 sm:mr-2 bg-[#5D6019] text-white p-1.5 sm:p-2 rounded-lg hover:bg-[#4a4d13] transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </button>
                                    <!-- نتائج البحث -->
                                    <div id="searchResults" class="absolute top-full left-0 right-0 bg-white border border-[#e0d9cc] rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto z-50 hidden">
                                        <!-- سيتم ملء النتائج هنا بواسطة JavaScript -->
                                    </div>
                                </div>
                                <div class="flex items-center border-t border-[#e0d9cc] pt-2 w-full sm:w-auto sm:border-0 sm:pt-0 sm:border-l sm:pl-4">
                                    <button id="toggleMovements" class="bg-[#f0e9de] text-[#5D6019] px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg hover:bg-[#e8e0d0] transition-colors font-tajawal text-sm sm:text-base whitespace-nowrap">
                                        إظهار الحركات
                                    </button>
                                </div>
                                <div class="flex items-center w-full sm:w-auto justify-end pt-2 sm:pt-0 sm:border-l sm:pl-4">
                                    <div class="flex space-x-1 sm:space-x-2 space-x-reverse">
                                        <!-- Share Button -->
                                        <button id="shareBtn" class="text-gray-600 hover:text-[#5D6019] p-1.5 sm:p-2 rounded-full hover:bg-[#f0e9de] transition-colors flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6.632L15.316 9m-4.065 8.814a5.97 5.97 0 001.23.247m-1.23-.247A5.97 5.97 0 015 12c0-.95.23-1.84-.632-2.684m0 2.684a3 3 0 110-2.684m0 2.684L8.684 13.342m0-2.684l6.632-3.316m-4.065-1.186a5.97 5.97 0 011.23-.247m-1.23.247A5.97 5.97 0 005 12c0-.95.23-1.84-.632-2.684m0 2.684a3 3 0 110-2.684" />
                                            </svg>
                                        </button>
                                        <!-- Fourth Button (from the far left) -->
                                        <button id="fourthBtn" class="text-gray-600 hover:text-[#5D6019] p-1.5 sm:p-2 rounded-full hover:bg-[#f0e9de] transition-colors flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5v14a2 2 0 002 2h18a2 2 0 002-2V5a2 2 0 00-2-2H3a2 2 0 00-2 2zM7 10l5 5 5-5"></path>
                                            </svg>
                                        </button>
                                        <!-- Fullscreen Button -->
                                        <button id="fullscreenButton" class="text-gray-600 hover:text-[#5D6019] p-1.5 sm:p-2 rounded-full hover:bg-[#f0e9de] transition-colors flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16h4m0 0v4m-4 0l5-5m11-1h-4m4 0v4m-4 0l5-5" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Content Area -->
                        <div class="flex flex-col lg:flex-row gap-4 sm:gap-6">
                            <!-- Sidebar (Right) -->
                            <aside class="lg:w-72 flex-shrink-0 w-full">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-[#e0d9cc] h-full">
                                    <div class="bg-[#5D6019] p-3 sm:p-4">
                                        <h2 class="text-white text-lg sm:text-xl font-bold font-tajawal">فهرس المحتويات</h2>
                                    </div>
                                    <div class="p-3 sm:p-4 max-h-[70vh] overflow-y-auto">
                                        @if($tableOfContents['type'] === 'volumes_with_chapters')
                                            <!-- عرض الأجزاء مع الفصول -->
                                            <ul class="space-y-2">
                                                @foreach($tableOfContents['data'] as $volume)
                                                    <li>
                                                        <div class="text-[#5D6019] font-bold flex items-center text-base sm:text-lg mb-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 ml-1 sm:ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                            </svg>
                                                            {{ $volume->title ?: 'الجزء ' . $volume->number }}
                                                        </div>
                                                        @if($volume->chapters->isNotEmpty())
                                                            <ul class="mr-3 space-y-1 border-r-2 border-[#e0d9cc] pr-2 sm:pr-3">
                                                                @foreach($volume->chapters as $chapter)
                                                                    @include('partials.chapter-tree', ['chapter' => $chapter, 'level' => 0])
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <!-- عرض الفصول فقط -->
                                            <ul class="space-y-2">
                                                @foreach($tableOfContents['data'] as $chapter)
                                                    @include('partials.chapter-tree', ['chapter' => $chapter, 'level' => 0])
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </aside>
                            <!-- Main Content Area -->
                            <main class="flex-1">
                                <!-- Book Page Content -->
                                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-[#e0d9cc] min-h-[50vh] sm:min-h-[60vh] md:min-h-[70vh] flex flex-col">
                                    <!-- Book Content -->
                                    <div id="bookContent" class="flex-1 p-4 sm:p-6 md:p-8 font-tajawal text-right leading-loose text-base sm:text-lg text-[#39100C] bg-[#faf8f5]">
                                        <div class="max-w-3xl mx-auto">
                                            @if($currentPage)
                                                @if($currentPage->chapter)
                                                    <h2 class="text-xl sm:text-2xl font-bold text-[#5D6019] mb-4 sm:mb-6 border-b border-[#e0d9cc] pb-3 sm:pb-4">
                                                        {{ $currentPage->chapter->title }}
                                                        @if($currentPage->volume)
                                                            <span class="text-sm text-gray-600 font-normal block mt-1">
                                                                {{ $currentPage->volume->title ?: 'الجزء ' . $currentPage->volume->number }}
                                                            </span>
                                                        @endif
                                                    </h2>
                                                @endif
                                                
                                                <div class="prose prose-lg max-w-none">
                                                    {!! $currentPage->content !!}
                                                </div>
                                                
                                                @if(!$currentPage->content || trim(strip_tags($currentPage->content)) === '')
                                                    <div class="text-center py-12">
                                                        <div class="text-gray-500 text-lg mb-4">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                        </div>
                                                        <p class="text-gray-600">لا يوجد محتوى متاح لهذه الصفحة</p>
                                                        <p class="text-sm text-gray-500 mt-2">الصفحة رقم {{ $currentPage->page_number }}</p>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="text-center py-12">
                                                    <div class="text-gray-500 text-lg mb-4">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                        </svg>
                                                    </div>
                                                    <p class="text-gray-600">لا توجد صفحات متاحة لهذا الكتاب</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Updated Navigation Bar -->
                                    <div class="px-3 py-2 sm:px-4 sm:py-3 border-t border-[#e0d9cc] bg-[#faf8f5]">
                                        <div class="flex flex-col lg:flex-row items-center justify-between gap-3 sm:gap-4">
                                            <!-- Page Navigation and Action Buttons -->
                                            <div class="flex items-center space-x-2 sm:space-x-3 space-x-reverse order-2 lg:order-1">
                                                <!-- Page Navigation -->
                                                <div class="flex items-center space-x-1 sm:space-x-2 space-x-reverse">
                                                    @if($navigationInfo['previous_page'])
                                                    <a href="{{ route('book.read', ['bookId' => $book->id, 'pageNumber' => $navigationInfo['previous_page']->page_number]) }}" 
                                                           class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                                            </svg>
                                                        </a>
                                                    @else
                                                        <button disabled class="bg-gray-300 p-1.5 sm:p-2 rounded-full cursor-not-allowed opacity-50">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                    <div class="flex items-center space-x-1 sm:space-x-2 space-x-reverse">
                                                        <form action="#" method="POST" class="inline-flex items-center" onsubmit="return goToPageFromInput(event)">
                                            @csrf
                                            <input
                                                type="number"
                                                name="pageNumber"
                                                min="1"
                                                max="{{ $navigationInfo['total_pages'] }}"
                                                value="{{ $navigationInfo['current_page_number'] }}"
                                                class="w-12 sm:w-16 px-2 py-1 border border-[#e0d9cc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#957717] text-center text-sm"
                                                onchange="goToPageFromInput(event)">
                                        </form>
                                                        <span class="text-[#39100C] font-medium text-sm sm:text-base">/ {{ $navigationInfo['total_pages'] }}</span>
                                                    </div>
                                                    @if($navigationInfo['next_page'])
                                                    <a href="{{ route('book.read', ['bookId' => $book->id, 'pageNumber' => $navigationInfo['next_page']->page_number]) }}" 
                                                           class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        </a>
                                                    @else
                                                        <button disabled class="bg-gray-300 p-1.5 sm:p-2 rounded-full cursor-not-allowed opacity-50">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            <!-- Centered Progress Bar -->
                                            <div class="flex-1 max-w-md mx-4 order-1 lg:order-2">
                                                <div class="relative">
                                                    <div class="flex items-center justify-center mb-2">
                                                        <span id="progressPercentage" class="text-xs sm:text-sm font-semibold text-[#5D6019] bg-[#f8f5f0] px-2 py-1 rounded-full border border-[#e0d9cc]">
                                                            {{ number_format(($navigationInfo['current_page_number'] / $navigationInfo['total_pages']) * 100, 1) }}%
                                                        </span>
                                                    </div>
                                                    <div class="relative">
                                                        <div class="overflow-hidden h-2 sm:h-2.5 rounded-full bg-[#f0e9de] border border-[#e0d9cc]">
                                                            <div id="progressBar" class="h-full bg-gradient-to-r from-[#5D6019] to-[#957717] transition-all duration-300" style="width: {{ number_format(($navigationInfo['current_page_number'] / $navigationInfo['total_pages']) * 100, 1) }}%"></div>
                                                        </div>
                                                        <input
                                                            type="range"
                                                            min="1"
                                                            max="{{ $navigationInfo['total_pages'] }}"
                                                            value="{{ $navigationInfo['current_page_number'] }}"
                                                            id="pageSlider"
                                                            class="absolute top-0 w-full h-full opacity-0 cursor-pointer"
                                                            oninput="updateProgress(this.value)">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Parts Selection (Far Right) -->
                            @if($book->volumes()->count() > 0)
                                <div class="flex items-center space-x-1 sm:space-x-2 space-x-reverse order-3">
                                    <span class="text-[#39100C] font-medium text-sm sm:text-base whitespace-nowrap">الأجزاء:</span>
                                    <select id="volumeSelect" class="bg-white border border-[#e0d9cc] rounded-lg px-2 py-1 sm:px-3 sm:py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#957717] min-w-[80px]" onchange="goToVolume(this.value)">
                                        @foreach($book->volumes()->orderBy('number')->get() as $volume)
                                            <option value="{{ $volume->id }}" {{ $currentPage && $currentPage->volume_id == $volume->id ? 'selected' : '' }}>
                                                {{ $volume->title ?: 'الجزء ' . $volume->number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                                        </div>
                                    </div>
                                </div>
                            </main>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <!-- JavaScript for functionality -->
    <script>
        // Progress bar functionality
        function updateProgress(currentPage) {
            const totalPages = {{ $navigationInfo['total_pages'] }};
            const percentage = ((currentPage / totalPages) * 100).toFixed(1);
            // Update percentage display
            document.getElementById('progressPercentage').textContent = percentage + '%';
            // Update progress bar width
            document.getElementById('progressBar').style.width = percentage + '%';
            // Update page input
            const pageInput = document.querySelector('input[name="pageNumber"]');
            if (pageInput) {
                pageInput.value = currentPage;
            }
        }

        function goToPage(page) {
            if (page >= 1 && page <= {{ $navigationInfo['total_pages'] }}) {
                window.location.href = `{{ route('book.read', ['bookId' => $book->id, 'pageNumber' => '']) }}${page}`;
            }
        }

        function goToPageFromInput(event) {
            event.preventDefault();
            const pageInput = event.target.querySelector('input[name="pageNumber"]') || event.target;
            const pageNumber = parseInt(pageInput.value);
            if (pageNumber >= 1 && pageNumber <= {{ $navigationInfo['total_pages'] }}) {
                goToPage(pageNumber);
            }
            return false;
        }

        // Navigate to specific volume
        function goToVolume(volumeId) {
            // Find the first page of the selected volume
            @if($book->volumes()->count() > 0)
                const volumes = @json($book->volumes()->with('pages')->orderBy('number')->get());
                const selectedVolume = volumes.find(v => v.id == volumeId);
                if (selectedVolume && selectedVolume.pages && selectedVolume.pages.length > 0) {
                    const firstPage = Math.min(...selectedVolume.pages.map(p => p.page_number));
                    window.location.href = `{{ route('book.read', ['bookId' => $book->id, 'pageNumber' => '']) }}${firstPage}`;
                }
            @endif
        }

        // Search functionality
        function performSearch(query) {
            fetch(`{{ route('book.search', ['bookId' => $book->id]) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ query: query })
            })
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data.results);
            })
            .catch(error => {
                console.error('Search error:', error);
                // Page slider functionality
            const pageSlider = document.getElementById('pageSlider');
            if (pageSlider) {
                pageSlider.addEventListener('input', function() {
                    updateProgress(this.value);
                });
                
                pageSlider.addEventListener('change', function() {
                    goToPage(this.value);
                });
            }
        });
        }

        function displaySearchResults(results) {
            const resultsContainer = document.getElementById('searchResults');
            if (results.length === 0) {
                resultsContainer.innerHTML = '<div class="p-4 text-center text-gray-500">لم يتم العثور على نتائج</div>';
            } else {
                let html = '<div class="max-h-64 overflow-y-auto">';
                results.forEach(result => {
                    html += `
                        <div class="p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer" onclick="goToPage(${result.page_number})">
                            <div class="text-sm text-gray-600 mb-1">الصفحة ${result.page_number}</div>
                            <div class="text-sm">${result.excerpt}</div>
                        </div>
                    `;
                });
                html += '</div>';
                resultsContainer.innerHTML = html;
            }
            resultsContainer.classList.remove('hidden');
        }
        // Font size control functionality - Completely fixed to properly adjust text size
        let currentFontSize = 100;
        const minFontSize = 50;
        const maxFontSize = 200;
        const fontSizeStep = 10;
        const baseFontSize = 16; // Base font size in pixels

        function updateFontSize() {
            const contentArea = document.getElementById('bookContent');
            if (contentArea) {
                // Calculate the actual pixel size based on base font size
                const pixelSize = baseFontSize * (currentFontSize / 100);

                // Apply the font size to the container
                contentArea.style.fontSize = pixelSize + 'px';

                // Update all text elements to inherit the size
                const textElements = contentArea.querySelectorAll('p, h2, h3, h4, h5, h6, span, div');
                textElements.forEach(element => {
                    element.style.fontSize = 'inherit';
                });
            }
            document.getElementById('fontSizeDisplay').textContent = currentFontSize + '%';
        }

        // Initialize font size on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateFontSize();

            // Add click event listeners to font size buttons
            document.getElementById('increaseFontSize').addEventListener('click', function() {
                if (currentFontSize < maxFontSize) {
                    currentFontSize += fontSizeStep;
                    updateFontSize();
                }
            });

            document.getElementById('decreaseFontSize').addEventListener('click', function() {
                if (currentFontSize > minFontSize) {
                    currentFontSize -= fontSizeStep;
                    updateFontSize();
                }
            });

            // Search functionality
            document.getElementById('searchButton').addEventListener('click', function() {
                const query = document.getElementById('searchInput').value.trim();
                if (query) {
                    performSearch(query);
                }
            });

            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const query = this.value.trim();
                    if (query) {
                        performSearch(query);
                    }
                }
            });

            // Hide search results when clicking outside
            document.addEventListener('click', function(e) {
                const searchContainer = document.getElementById('searchResults');
                const searchInput = document.getElementById('searchInput');
                const searchButton = document.getElementById('searchButton');
                
                if (!searchContainer.contains(e.target) && e.target !== searchInput && e.target !== searchButton) {
                    searchContainer.classList.add('hidden');
                }
            });

            // Toggle movements functionality
            let movementsVisible = false;
            const toggleMovementsBtn = document.getElementById('toggleMovements');
            if (toggleMovementsBtn) {
                toggleMovementsBtn.addEventListener('click', function() {
                    movementsVisible = !movementsVisible;
                    const button = this;
                    if (movementsVisible) {
                        button.textContent = 'إخفاء الحركات';
                        button.classList.add('bg-[#5D6019]', 'text-white');
                        button.classList.remove('bg-[#f0e9de]', 'text-[#5D6019]');
                    } else {
                        button.textContent = 'إظهار الحركات';
                        button.classList.remove('bg-[#5D6019]', 'text-white');
                        button.classList.add('bg-[#f0e9de]', 'text-[#5D6019]');
                    }
                });
            }

            // Fullscreen functionality
            const fullscreenBtn = document.getElementById('fullscreenButton');
            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', function() {
                    const element = document.documentElement;
                    if (!document.fullscreenElement) {
                        if (element.requestFullscreen) {
                            element.requestFullscreen();
                        } else if (element.mozRequestFullScreen) {
                            element.mozRequestFullScreen();
                        } else if (element.webkitRequestFullscreen) {
                            element.webkitRequestFullscreen();
                        } else if (element.msRequestFullscreen) {
                            element.msRequestFullscreen();
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if (document.mozCancelFullScreen) {
                            document.mozCancelFullScreen();
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        } else if (document.msExitFullscreen) {
                            document.msExitFullscreen();
                        }
                    }
                });
            }

            // Fourth button functionality
            const fourthBtn = document.getElementById('fourthBtn');
            if (fourthBtn) {
                fourthBtn.addEventListener('click', function() {
                    const contentArea = document.getElementById('bookContent');
                    if (contentArea) {
                        contentArea.classList.toggle('text-justify');
                        contentArea.classList.toggle('text-right');

                        if (contentArea.classList.contains('text-justify')) {
                            this.classList.add('bg-[#5D6019]', 'text-white');
                            this.classList.remove('text-gray-600', 'hover:text-[#5D6019]');
                        } else {
                            this.classList.remove('bg-[#5D6019]', 'text-white');
                            this.classList.add('text-gray-600', 'hover:text-[#5D6019]');
                        }
                    }
                });
            }

            // Share button functionality
            const shareBtn = document.getElementById('shareBtn');
            if (shareBtn) {
                shareBtn.addEventListener('click', function() {
                    if (navigator.share) {
                        navigator.share({
                                title: '{{ $book->title }}',
                                text: 'لقد وجدت هذا الكتاب مثيرًا للاهتمام',
                                url: window.location.href
                            })
                            .catch((error) => console.log('Error sharing:', error));
                    } else {
                        // Copy URL to clipboard
                        navigator.clipboard.writeText(window.location.href).then(function() {
                            alert('تم نسخ الرابط إلى الحافظة');
                        }, function() {
                            alert('متصفحك لا يدعم ميزة المشاركة. يمكنك نسخ الرابط يدويًا.');
                        });
                    }
                });
            }
        });
    </script>
</x-superduper.main>