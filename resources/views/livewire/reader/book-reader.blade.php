<div>
    <!-- Google Fonts for Tajawal -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .font-tajawal {
            font-family: 'Tajawal', sans-serif;
        }
    </style>
    
    <!-- Loading indicator -->
    <div wire:loading.delay wire:target="gotoPage, previousPage, nextPage, performSearch, gotoVolume, gotoChapter" 
         class="fixed z-50 flex items-center gap-3 px-4 py-3 bg-white rounded-lg shadow-lg bottom-4 right-4">
        <svg class="w-5 h-5 text-primary-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm font-medium text-gray-700">جاري التحميل...</span>
    </div>
    
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
                            <h2 class="text-2xl sm:text-3xl md:text-4xl text-[#5D6019] font-bold font-tajawal">
                                معاينة كتاب: 
                                <a href="{{ route('books.details', $book->id) }}" 
                                   class="hover:text-green-600 transition-colors duration-200 cursor-pointer underline decoration-2 underline-offset-4">
                                    {{ $book->title }}
                                </a>
                            </h2>
                        </div>
                    </div>
                    
                    <!-- Main Content -->
                    <div class="flex flex-col gap-4 sm:gap-6">
                        <!-- Toolbar -->
                        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-[#e0d9cc] p-2 sm:p-3">
                            <!-- Top Row: All Action Buttons -->
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-[#e0d9cc] gap-2">
                                <!-- Left Side: Menu and Font Controls -->
                                <div class="flex items-center gap-1 sm:gap-2">
                                    <!-- Enhanced Mobile Hamburger Menu Button -->
                                    <button id="book-reader-hamburger" wire:click="toggleMobileToc" class="lg:hidden flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#f0e9de] hover:bg-[#e8e0d0] text-[#5D6019] transition-all duration-200 {{ $showMobileToc ? 'bg-[#5D6019] text-white shadow-lg scale-105' : 'hover:scale-105' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            @if($showMobileToc)
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                            @endif
                                        </svg>
                                    </button>
                                    
                                    <button id="decrease-font-btn" wire:click="decreaseFontSize" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-full bg-[#f0e9de] hover:bg-[#e8e0d0] text-[#5D6019] font-bold transition-colors text-xs sm:text-sm">
                                        A-
                                    </button>
                                    <span id="font-percent-display" class="px-1 text-[#39100C] font-medium text-xs sm:text-sm">{{ $fontPercent }}%</span>
                                    <button id="increase-font-btn" wire:click="increaseFontSize" class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-full bg-[#f0e9de] hover:bg-[#e8e0d0] text-[#5D6019] font-bold transition-colors text-xs sm:text-sm">
                                        A+
                                    </button>
                                </div>

                                <!-- Center: Movements Toggle (Responsive) -->
                                <div class="flex items-center">
                                    <button wire:click="toggleMovements" class="{{ $showMovements ? 'bg-[#5D6019] text-white' : 'bg-[#f0e9de] text-[#5D6019]' }} px-2 py-1 sm:px-3 sm:py-1.5 rounded-lg hover:bg-[#e8e0d0] transition-colors font-tajawal text-xs sm:text-sm whitespace-nowrap">
                                        <span class="hidden sm:inline">{{ $showMovements ? 'إخفاء الحركات' : 'إظهار الحركات' }}</span>
                                        <span class="sm:hidden">{{ $showMovements ? 'إخفاء' : 'إظهار' }}</span>
                                    </button>
                                </div>

                                <!-- Right Side: Action Buttons -->
                                <div class="flex items-center gap-1">
                                    <!-- Share Button -->
                                    <button class="text-gray-600 hover:text-[#5D6019] p-1 sm:p-1.5 rounded-full hover:bg-[#f0e9de] transition-colors flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6.632L15.316 9m-4.065 8.814a5.97 5.97 0 001.23.247m-1.23-.247A5.97 5.97 0 015 12c0-.95.23-1.84-.632-2.684m0 2.684a3 3 0 110-2.684m0 2.684L8.684 13.342m0-2.684l6.632-3.316m-4.065-1.186a5.97 5.97 0 011.23-.247m-1.23.247A5.97 5.97 0 005 12c0-.95.23-1.84-.632-2.684m0 2.684a3 3 0 110-2.684" />
                                        </svg>
                                    </button>
                                    <!-- Fullscreen Button -->
                                    <button onclick="toggleFullscreen()" class="text-gray-600 hover:text-[#5D6019] p-1 sm:p-1.5 rounded-full hover:bg-[#f0e9de] transition-colors flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16h4m0 0v4m-4 0l5-5m11-1h-4m4 0v4m-4 0l5-5" />
                                        </svg>
                                    </button>
                                    <!-- Options Menu Button -->
                                    <div class="relative">
                                        <button wire:click="toggleOptionsMenu" class="text-gray-600 hover:text-[#5D6019] p-1 sm:p-1.5 rounded-full hover:bg-[#f0e9de] transition-colors flex items-center justify-center {{ $showOptionsMenu ? 'bg-[#f0e9de] text-[#5D6019]' : '' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Options Dropdown Menu -->
                                        @if($showOptionsMenu)
                                            <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-[#e0d9cc] z-50 options-menu transition-all" x-data x-on:click.away="$wire.set('showOptionsMenu', false)">
                                                <div class="py-2">
                                                    <!-- Dark Mode Toggle -->
                                                    <button wire:click="toggleDarkMode" class="w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-[#f0e9de] flex items-center justify-between">
                                                        <span>{{ $darkMode ? 'الوضع الفاتح' : 'الوضع المظلم' }}</span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            @if($darkMode)
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                                            @else
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                                            @endif
                                                        </svg>
                                                    </button>
                                                    
                                                    <!-- Font Size Reset -->
                                                    <button wire:click="resetFontSize" class="w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-[#f0e9de] flex items-center justify-between">
                                                        <span>إعادة تعيين حجم الخط</span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                        </svg>
                                                    </button>
                                                    
                                                    <!-- Separator -->
                                                    <div class="border-t border-gray-200 my-1"></div>
                                                    
                                                    <!-- Print Page -->
                                                    <button class="w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-[#f0e9de] flex items-center justify-between" onclick="window.print()">
                                                        <span>طباعة الصفحة</span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                        </svg>
                                                    </button>
                                                    
                                                    <!-- Add to Favorites -->
                                                    <button class="w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-[#f0e9de] flex items-center justify-between">
                                                        <span>إضافة إلى المفضلة</span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Middle Row: Search Box -->
                            <div class="mb-3">
                                <div class="flex items-center relative">
                                    <input type="text" 
                                           wire:model.live.debounce.500ms="search" 
                                           placeholder="ابحث في النص..." 
                                           class="flex-1 px-3 py-1.5 sm:px-4 sm:py-2 border border-[#e0d9cc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#957717] font-tajawal text-sm">
                                    <button wire:click="performSearch" class="mr-1 sm:mr-2 bg-[#5D6019] text-white p-1.5 sm:p-2 rounded-lg hover:bg-[#4a4d13] transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </button>
                                    
                                    <!-- Search Results -->
                                    @if($showSearchResults && !empty($searchResults))
                                        <div class="absolute top-full left-0 right-0 bg-white border border-[#e0d9cc] rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto z-50">
                                            @foreach($searchResults as $result)
                                                <div wire:click="gotoPage({{ $result['page_number'] }})" class="p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer">
                                                    <div class="text-sm text-gray-600 mb-1">
                                                        الصفحة {{ $result['page_number'] }}
                                                        @if($result['internal_index'])
                                                            ({{ $result['internal_index'] }})
                                                        @endif
                                                    </div>
                                                    <div class="text-sm">{!! $result['excerpt'] !!}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($showSearchResults && empty($searchResults))
                                        <div class="absolute top-full left-0 right-0 bg-white border border-[#e0d9cc] rounded-lg shadow-lg mt-1 z-50">
                                            <div class="p-4 text-center text-gray-500">لم يتم العثور على نتائج</div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Bottom Row: Page Navigation -->
                            <div class="pt-2 border-t border-[#e0d9cc] bg-[#faf8f5] -mx-2 -mb-2 px-2 pb-2 sm:-mx-3 sm:-mb-3 sm:px-3 sm:pb-3">
                                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
                                    <!-- Page Navigation -->
                                    <div class="flex items-center space-x-2 sm:space-x-3 space-x-reverse order-2 sm:order-1">
                                        <div class="flex items-center space-x-1 sm:space-x-2 space-x-reverse">
                                            <!-- First Page Button -->
                                            @if($navigation['current_page_number'] > 1)
                                                <button wire:click="gotoPage(1)" class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors" title="الصفحة الأولى">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-[#5D6019]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button disabled class="bg-gray-200 p-1.5 sm:p-2 rounded-full cursor-not-allowed" title="الصفحة الأولى">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                            @endif

                                            <!-- Previous Page Button -->
                                            @if($navigation['previous_page'])
                                                <button id="prev-page-btn" wire:click="previousPage" class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-[#5D6019]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button id="prev-page-btn-disabled" disabled class="bg-gray-200 p-1.5 sm:p-2 rounded-full cursor-not-allowed">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                            @endif
                                            
                                            <!-- Page Input -->
                                            <div class="flex items-center space-x-1 mx-2">
                                                <input id="page-number-input" 
                                                       type="number" 
                                                       wire:model.live.debounce.500ms="internalIndex" 
                                                       min="1" 
                                                       max="{{ $navigation['total_pages'] }}" 
                                                       class="w-16 px-2 py-1 text-center border border-[#e0d9cc] rounded focus:outline-none focus:ring-2 focus:ring-[#957717] text-sm">
                                                <span class="text-sm text-gray-600">من {{ $navigation['total_pages'] }}</span>
                                            </div>
                                            
                                            <!-- Next Page Button -->
                                            @if($navigation['next_page'])
                                                <button id="next-page-btn" wire:click="nextPage" class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-[#5D6019]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button id="next-page-btn-disabled" disabled class="bg-gray-200 p-1.5 sm:p-2 rounded-full cursor-not-allowed">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                                    </svg>
                                                </button>
                                            @endif

                                            <!-- Last Page Button -->
                                            @if($navigation['current_page_number'] < $navigation['total_pages'])
                                                <button wire:click="gotoPage({{ $navigation['total_pages'] }})" class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors" title="الصفحة الأخيرة">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-[#5D6019]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button disabled class="bg-gray-200 p-1.5 sm:p-2 rounded-full cursor-not-allowed" title="الصفحة الأخيرة">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="flex-1 max-w-md mx-4 order-1 sm:order-2">
                                        <div class="relative">
                                            <div class="flex items-center justify-center mb-2">
                                                <span class="text-xs sm:text-sm font-semibold text-[#5D6019] bg-[#f8f5f0] px-2 py-1 rounded-full border border-[#e0d9cc]">
                                                    {{ $navigation['progress_percentage'] }}%
                                                </span>
                                            </div>
                                            <div class="relative">
                                                <div class="overflow-hidden h-2 sm:h-2.5 rounded-full bg-[#f0e9de] border border-[#e0d9cc]">
                                                    <div class="h-full bg-gradient-to-r from-[#5D6019] to-[#957717] transition-all duration-300" style="width: {{ $navigation['progress_percentage'] }}%"></div>
                                                </div>
                                                <input type="range" 
                                                       wire:model.live.debounce.300ms="pageNumber"
                                                       min="1" 
                                                       max="{{ $navigation['total_pages'] }}" 
                                                       class="absolute top-0 w-full h-full opacity-0 cursor-pointer">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Volume Selection -->
                                    @if($book->volumes()->count() > 0)
                                        <div class="flex items-center space-x-1 sm:space-x-2 space-x-reverse order-3">
                                            <span class="text-[#39100C] font-medium text-sm sm:text-base whitespace-nowrap">الأجزاء:</span>
                                            <select x-data x-on:change="$wire.call('gotoVolume', $event.target.value)" class="bg-white border border-[#e0d9cc] rounded-lg px-2 py-1 sm:px-3 sm:py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#957717] min-w-[80px]">
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
                        
                        <!-- Mobile TOC Overlay -->
                        @if($showMobileToc)
                            <div id="book-reader-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden mobile-toc-backdrop" wire:click="closeMobileToc"></div>
                            <div id="book-reader-mobile-toc" class="fixed top-0 right-0 h-full w-80 max-w-[85vw] bg-white shadow-2xl z-50 lg:hidden mobile-toc-sidebar">
                                <!-- Enhanced Mobile TOC Header -->
                                <div class="bg-gradient-to-r from-[#5D6019] to-[#4a4d13] p-4 flex items-center justify-between shadow-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                                        <h2 class="text-white text-xl font-bold font-tajawal">فهرس المحتويات</h2>
                                    </div>
                                    <button wire:click="closeMobileToc" class="text-white hover:text-gray-200 p-2 rounded-full hover:bg-white/10 transition-all duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- Mobile TOC Search -->
                                <div class="p-4 border-b border-[#e0d9cc]">
                                    <div class="relative">
                                        <input type="text" 
                                               wire:model.live.debounce.300ms="tocSearch" 
                                               placeholder="ابحث في الفهرس..." 
                                               class="w-full px-3 py-2 pr-10 border border-[#e0d9cc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#5D6019] focus:border-[#5D6019] bg-white text-[#5D6019] placeholder-gray-500 text-sm">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            @if($tocSearch)
                                                <button wire:click="clearTocSearch" class="text-gray-400 hover:text-gray-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Mobile TOC Content -->
                                <div class="flex-1 overflow-y-auto p-4 max-h-[calc(100vh-140px)]">
                                    @if($tocSearch && empty($filteredTableOfContents['data']))
                                        <!-- No search results message -->
                                        <div class="text-center py-8">
                                            <div class="text-gray-500 mb-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </div>
                                            <p class="text-gray-600 text-sm">لم يتم العثور على نتائج في الفهرس</p>
                                            <p class="text-gray-500 text-xs mt-1">جرب البحث بكلمات مختلفة</p>
                                        </div>
                                    @elseif($filteredTableOfContents['type'] === 'volumes_with_chapters')
                                        <!-- عرض الأجزاء مع الفصول -->
                                        <ul class="space-y-3">
                                            @foreach($filteredTableOfContents['data'] as $volume)
                                                <li>
                                                    <div class="toc-item mobile-toc-item flex items-center justify-between p-2 rounded-lg 
                                                                {{ $currentVolumeId === $volume->id ? 'bg-[#5D6019] text-white shadow-md active' : 'hover:bg-[#f0e9de]' }}">
                                                        <div class="flex items-center cursor-pointer flex-1" 
                                                             wire:click="gotoVolume({{ $volume->id }})">
                                                            <span class="font-bold text-lg {{ $currentVolumeId === $volume->id ? 'text-white' : 'text-[#5D6019]' }}">
                                                                {{ $volume->title ?: 'الجزء ' . $volume->number }}
                                                            </span>
                                                        </div>
                                                        @if($volume->chapters->isNotEmpty())
                                                            <button wire:click="toggleVolume({{ $volume->id }})" 
                                                                    class="toc-expand-btn p-1 rounded-full hover:bg-black/10 transition-colors">
                                                                <svg xmlns="http://www.w3.org/2000/svg" 
                                                                     class="h-4 w-4 transition-transform duration-200 
                                                                            {{ in_array($volume->id, $expandedVolumes) ? 'rotate-180' : '' }}" 
                                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                    @if($volume->chapters->isNotEmpty() && in_array($volume->id, $expandedVolumes))
                                                        <ul class="toc-children mr-4 mt-2 space-y-1 border-r-2 border-[#e0d9cc] pr-3">
                                                            @foreach($volume->chapters as $chapter)
                                                                @include('livewire.reader.partials.chapter-tree', ['chapter' => $chapter, 'level' => 0])
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <!-- عرض الفصول فقط -->
                                        <ul class="space-y-3">
                                            @foreach($filteredTableOfContents['data'] as $chapter)
                                                <li>
                                                    <div class="toc-item mobile-toc-item flex items-center justify-between p-2 rounded-lg 
                                                                 {{ $currentChapterId === $chapter->id ? 'bg-[#5D6019] text-white shadow-md active' : 'hover:bg-[#f0e9de]' }}">
                                                        <div class="flex items-center cursor-pointer flex-1" 
                                                             wire:click="gotoChapter({{ $chapter->id }})">
                                                            <span class="font-bold text-lg {{ $currentChapterId === $chapter->id ? 'text-white' : 'text-[#5D6019]' }}">
                                                                {{ $chapter->title }}
                                                            </span>
                                                        </div>
                                                        @if($chapter->children->isNotEmpty())
                                                            <button wire:click="toggleChapter({{ $chapter->id }})" 
                                                                    class="toc-expand-btn p-1 rounded-full hover:bg-black/10 transition-colors">
                                                                <svg xmlns="http://www.w3.org/2000/svg" 
                                                                     class="h-4 w-4 transition-transform duration-200 
                                                                            {{ in_array($chapter->id, $expandedChapters) ? 'rotate-180' : '' }}" 
                                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                    @if($chapter->children->isNotEmpty() && in_array($chapter->id, $expandedChapters))
                                                        <ul class="toc-children mr-4 mt-2 space-y-1 border-r-2 border-[#e0d9cc] pr-3">
                                                            @foreach($chapter->children as $subChapter)
                                                                @include('livewire.reader.partials.chapter-tree', ['chapter' => $subChapter, 'level' => 1])
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <!-- Content Area -->
                        <div class="flex flex-col lg:flex-row gap-4 sm:gap-6">
                            <!-- Sidebar (Right) - Hidden on mobile when mobile TOC is active -->
                            <aside class="lg:w-72 flex-shrink-0 w-full hidden lg:block">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-[#e0d9cc] h-full">
                                    <div class="bg-[#5D6019] p-3 sm:p-4">
                                        <h2 class="text-white text-xl sm:text-2xl font-bold font-tajawal mb-3">فهرس المحتويات</h2>
                                        <!-- TOC Search Bar -->
                                        <div class="relative">
                                            <input type="text" 
                                                   wire:model.live.debounce.300ms="tocSearch" 
                                                   placeholder="ابحث في الفهرس..." 
                                                   class="w-full px-3 py-2 pr-10 border border-[#e0d9cc] rounded-lg focus:outline-none focus:ring-2 focus:ring-white focus:border-white bg-white text-[#5D6019] placeholder-gray-500 text-sm">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                @if($tocSearch)
                                                    <button wire:click="clearTocSearch" class="text-gray-400 hover:text-gray-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4 sm:p-5 max-h-[70vh] overflow-y-auto toc-container">
                                        @if($tocSearch && empty($filteredTableOfContents['data']))
                                            <!-- No search results message -->
                                            <div class="text-center py-8">
                                                <div class="text-gray-500 mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                </div>
                                                <p class="text-gray-600 text-sm">لم يتم العثور على نتائج في الفهرس</p>
                                                <p class="text-gray-500 text-xs mt-1">جرب البحث بكلمات مختلفة</p>
                                            </div>
                                        @elseif($filteredTableOfContents['type'] === 'volumes_with_chapters')
                                            <!-- عرض الأجزاء مع الفصول -->
                                            <ul class="space-y-3">
                                                @foreach($filteredTableOfContents['data'] as $volume)
                                                    <li>
                                                        <div class="toc-item flex items-center justify-between p-2 rounded-lg 
                                                                    {{ $currentVolumeId === $volume->id ? 'bg-[#5D6019] text-white shadow-md active' : 'hover:bg-[#f0e9de]' }}">
                                                            <div class="flex items-center cursor-pointer flex-1" 
                                                                 wire:click="gotoVolume({{ $volume->id }})">
                                                                <span class="font-bold text-lg sm:text-xl {{ $currentVolumeId === $volume->id ? 'text-white' : 'text-[#5D6019]' }}">
                                                                    {{ $volume->title ?: 'الجزء ' . $volume->number }}
                                                                </span>
                                                            </div>
                                                            @if($volume->chapters->isNotEmpty())
                                                                <button wire:click="toggleVolume({{ $volume->id }})" 
                                                                        class="toc-expand-btn p-1 rounded-full hover:bg-black/10 transition-colors">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" 
                                                                         class="h-4 w-4 sm:h-5 sm:w-5 transition-transform duration-200 
                                                                                {{ in_array($volume->id, $expandedVolumes) ? 'rotate-180' : '' }}" 
                                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                    </svg>
                                                                </button>
                                                            @endif
                                                        </div>
                                                        @if($volume->chapters->isNotEmpty() && in_array($volume->id, $expandedVolumes))
                                                            <ul class="toc-children mr-4 mt-2 space-y-1 border-r-2 border-[#e0d9cc] pr-3 sm:pr-4">
                                                                @foreach($volume->chapters as $chapter)
                                                                    @include('livewire.reader.partials.chapter-tree', ['chapter' => $chapter, 'level' => 0])
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <!-- عرض الفصول فقط -->
                                            <ul class="space-y-3">
                                                @foreach($filteredTableOfContents['data'] as $chapter)
                                                    <li>
                                                        <div class="toc-item flex items-center justify-between p-2 rounded-lg 
                                                                     {{ $currentChapterId === $chapter->id ? 'bg-[#5D6019] text-white shadow-md active' : 'hover:bg-[#f0e9de]' }}">
                                                            <div class="flex items-center cursor-pointer flex-1" 
                                                                 wire:click="gotoChapter({{ $chapter->id }})">
                                                                <span class="font-bold text-lg sm:text-xl {{ $currentChapterId === $chapter->id ? 'text-white' : 'text-[#5D6019]' }}">
                                                                    {{ $chapter->title }}
                                                                </span>
                                                            </div>
                                                            @if($chapter->children->isNotEmpty())
                                                                <button wire:click="toggleChapter({{ $chapter->id }})" 
                                                                        class="toc-expand-btn p-1 rounded-full hover:bg-black/10 transition-colors">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" 
                                                                         class="h-4 w-4 sm:h-5 sm:w-5 transition-transform duration-200 
                                                                                {{ in_array($chapter->id, $expandedChapters) ? 'rotate-180' : '' }}" 
                                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                    </svg>
                                                                </button>
                                                            @endif
                                                        </div>
                                                        @if($chapter->children->isNotEmpty() && in_array($chapter->id, $expandedChapters))
                                                            <ul class="toc-children mr-4 mt-2 space-y-1 border-r-2 border-[#e0d9cc] pr-3 sm:pr-4">
                                                                @foreach($chapter->children as $subChapter)
                                                                    @include('livewire.reader.partials.chapter-tree', ['chapter' => $subChapter, 'level' => 1])
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
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
                                    <div class="flex-1 p-4 sm:p-6 md:p-8 font-tajawal text-right leading-loose text-base sm:text-lg text-[#39100C] bg-[#faf8f5]" 
                                         style="font-size: {{ $fontPercent / 100 }}em" 
                                         data-book-content>
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
                                                
                                                <div class="prose prose-lg max-w-none {{ $showMovements ? '' : 'no-movements' }}" style="font-size: {{ $fontPercent / 100 }}em !important;" id="book-content">
                                                    @if($showMovements)
                                                        {!! $currentContent !!}
                                                    @else
                                                        {!! preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $currentContent) !!}
                                                    @endif
                                                </div>
                                                
                                                @if(!$currentContent || trim(strip_tags($currentContent)) === '')
                                                    <div class="text-center py-12">
                                                        <div class="text-gray-500 text-lg mb-4">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                        </div>
                                                        <p class="text-gray-600">لا يوجد محتوى متاح لهذه الصفحة</p>
                                                        <p class="text-sm text-gray-500 mt-2">
                                                            الصفحة رقم {{ $currentPage->page_number }}
                                                            @if($currentPage->internal_index)
                                                                ({{ $currentPage->internal_index }})
                                                            @endif
                                                        </p>
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
                                    
                                    <!-- Navigation Bar - Original Position -->
                                    <div class="px-3 py-2 sm:px-4 sm:py-3 border-t border-[#e0d9cc] bg-[#faf8f5]">
                                        <div class="flex flex-col lg:flex-row items-center justify-between gap-3 sm:gap-4">
                                            <!-- Page Navigation -->
                                            <div class="flex items-center space-x-2 sm:space-x-3 space-x-reverse order-2 lg:order-1">
                                                <div class="flex items-center space-x-1 sm:space-x-2 space-x-reverse">
                                                    <!-- First Page Button -->
                                                    @if($navigation['current_page_number'] > 1)
                                                        <button wire:click="gotoPage(1)" class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors" title="الصفحة الأولى">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-[#5D6019]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <button disabled class="bg-gray-200 p-1.5 sm:p-2 rounded-full cursor-not-allowed" title="الصفحة الأولى">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                            </svg>
                                                        </button>
                                                    @endif

                                                    <!-- Previous Page Button -->
                                                    @if($navigation['previous_page'])
                                                        <button wire:click="previousPage" class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-[#5D6019]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <button disabled class="bg-gray-200 p-1.5 sm:p-2 rounded-full cursor-not-allowed">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                    
                                                    <!-- Page Input -->
                                                    <div class="flex items-center space-x-1 mx-2">
                                                        <input type="number" 
                                                               wire:model.live.debounce.500ms="internalIndex" 
                                                               min="1" 
                                                               max="{{ $navigation['total_pages'] }}" 
                                                               class="w-16 px-2 py-1 text-center border border-[#e0d9cc] rounded focus:outline-none focus:ring-2 focus:ring-[#957717] text-sm">
                                                        <span class="text-sm text-gray-600">من {{ $navigation['total_pages'] }}</span>
                                                    </div>
                                                    
                                                    <!-- Next Page Button -->
                                                    @if($navigation['next_page'])
                                                        <button wire:click="nextPage" class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-[#5D6019]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <button disabled class="bg-gray-200 p-1.5 sm:p-2 rounded-full cursor-not-allowed">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                                            </svg>
                                                        </button>
                                                    @endif

                                                    <!-- Last Page Button -->
                                                    @if($navigation['current_page_number'] < $navigation['total_pages'])
                                                        <button wire:click="gotoPage({{ $navigation['total_pages'] }})" class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors" title="الصفحة الأخيرة">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-[#5D6019]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <button disabled class="bg-gray-200 p-1.5 sm:p-2 rounded-full cursor-not-allowed" title="الصفحة الأخيرة">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Progress Bar -->
                                            <div class="flex-1 max-w-md mx-4 order-1 lg:order-2">
                                                <div class="relative">
                                                    <div class="flex items-center justify-center mb-2">
                                                        <span class="text-xs sm:text-sm font-semibold text-[#5D6019] bg-[#f8f5f0] px-2 py-1 rounded-full border border-[#e0d9cc]">
                                                            {{ $navigation['progress_percentage'] }}%
                                                        </span>
                                                    </div>
                                                    <div class="relative">
                                                        <div class="overflow-hidden h-2 sm:h-2.5 rounded-full bg-[#f0e9de] border border-[#e0d9cc]">
                                                            <div class="h-full bg-gradient-to-r from-[#5D6019] to-[#957717] transition-all duration-300" style="width: {{ $navigation['progress_percentage'] }}%"></div>
                                                        </div>
                                                        <input type="range" 
                                                               wire:model.live.debounce.300ms="pageNumber"
                                                               min="1" 
                                                               max="{{ $navigation['total_pages'] }}" 
                                                               class="absolute top-0 w-full h-full opacity-0 cursor-pointer">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Volume Selection -->
                                            @if($book->volumes()->count() > 0)
                                                <div class="flex items-center space-x-1 sm:space-x-2 space-x-reverse order-3">
                                                    <span class="text-[#39100C] font-medium text-sm sm:text-base whitespace-nowrap">الأجزاء:</span>
                                                    <select x-data x-on:change="$wire.call('gotoVolume', $event.target.value)" class="bg-white border border-[#e0d9cc] rounded-lg px-2 py-1 sm:px-3 sm:py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#957717] min-w-[80px]">
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
    
    <!-- JavaScript for additional functionality -->
    <script>
        // Fullscreen functionality
        function toggleFullscreen() {
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
        }
        
        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            const searchContainer = e.target.closest('.relative');
            const searchInput = document.querySelector('input[placeholder="ابحث في النص..."]');
            if (!searchContainer || !searchContainer.contains(searchInput)) {
                @this.set('showSearchResults', false);
            }
        });
        
        // Hide options menu when clicking outside
        document.addEventListener('click', function(e) {
            const optionsContainer = e.target.closest('.relative');
            const optionsButton = e.target.closest('button[wire\\:click="toggleOptionsMenu"]');
            const optionsMenu = document.querySelector('.absolute.left-0.mt-2.w-48');
            
            // If click is outside the options container and menu is visible
            if (!optionsContainer && optionsMenu && !e.target.closest('.absolute.left-0.mt-2.w-48')) {
                @this.set('showOptionsMenu', false);
            }
        });
        
        // Close options menu when clicking on menu items (except for buttons that need to stay open)
         document.addEventListener('click', function(e) {
             if (e.target.closest('.absolute.left-0.mt-2.w-48 button')) {
                 // Close menu after a short delay to allow the action to complete
                 setTimeout(() => {
                     @this.set('showOptionsMenu', false);
                 }, 100);
             }
         });
         
         // Simplified mobile TOC handling - Only essential interactions
         // Let Livewire handle most of the state management
         
         // Enhanced keyboard and navigation handling
         document.addEventListener('keydown', function(e) {
             if (e.key === 'Escape') {
                 const showMobileToc = @json($showMobileToc ?? false);
                 if (showMobileToc) {
                     @this.set('showMobileToc', false);
                 }
                 @this.set('showOptionsMenu', false);
             }
         });
         
         // Close mobile TOC when navigating to a chapter/volume (but not when expanding)
         document.addEventListener('click', function(e) {
             // Don't close if clicking on expand/collapse buttons
             const expandBtn = e.target.closest('[wire\\:click^="toggleVolume"], [wire\\:click^="toggleChapter"]');
             if (expandBtn) {
                 return; // Let the expand/collapse work normally
             }
             
             const tocNavItem = e.target.closest('[wire\\:click^="gotoVolume"], [wire\\:click^="gotoChapter"]');
             if (tocNavItem && e.target.closest('#book-reader-mobile-toc')) {
                 // Close TOC after a short delay to allow navigation
                 setTimeout(() => {
                     @this.set('showMobileToc', false);
                 }, 300);
             }
         });
         
         // Removed livewire:updated event listener to avoid conflicts
         // Body scroll will be managed by CSS only
         
         // Mobile TOC initialization
         document.addEventListener('DOMContentLoaded', function() {
             // Initialize mobile TOC state on page load
             const showMobileToc = @json($showMobileToc ?? false);
             if (showMobileToc) {
                 document.body.classList.add('mobile-toc-open');
             }
         });
         
         // Enhanced Livewire updates with smooth animations
         document.addEventListener('livewire:updated', function() {
             const showMobileToc = @json($showMobileToc ?? false);
             const mobileToc = document.getElementById('book-reader-mobile-toc');
             
             // Manage body scroll class
             if (showMobileToc) {
                 document.body.classList.add('mobile-toc-open');
                 // Add show class for smooth animation
                 if (mobileToc) {
                     setTimeout(() => mobileToc.classList.add('show'), 10);
                 }
             } else {
                 document.body.classList.remove('mobile-toc-open');
                 // Remove show class
                 if (mobileToc) {
                     mobileToc.classList.remove('show');
                 }
             }
         });
        
        // Listen for URL updates and font size changes
        document.addEventListener('livewire:init', () => {
            console.log('Livewire initialized successfully');
            
            Livewire.on('url-update', (event) => {
                console.log('URL update:', event);
                window.history.pushState({}, '', event.url);
            });
            
            // Listen for font size changes
            Livewire.on('fontSizeChanged', (event) => {
                const fontPercent = event.detail ? event.detail[0] : event;
                console.log('Font size changed to:', fontPercent);
                const contentArea = document.querySelector('[data-book-content]');
                const proseArea = document.querySelector('.prose');
                const fontDisplay = document.getElementById('font-percent-display');
                
                const relativeSize = (fontPercent / 100) + 'em';
                
                // Apply to main content area
                if (contentArea) {
                    contentArea.style.fontSize = relativeSize;
                    console.log('Applied font size to content area:', relativeSize);
                }
                
                // Apply to prose area with !important
                if (proseArea) {
                    proseArea.style.setProperty('font-size', relativeSize, 'important');
                    console.log('Applied font size to prose area:', relativeSize);
                }
                
                // Update font display
                if (fontDisplay) {
                    fontDisplay.textContent = fontPercent + '%';
                    console.log('Updated font display to:', fontPercent + '%');
                } else {
                    console.error('Font display element not found');
                }
            });
            
            // Listen for dark mode changes
            Livewire.on('darkModeToggled', (event) => {
                const isDarkMode = event.detail ? event.detail[0] : event;
                console.log('Dark mode toggled:', isDarkMode);
                
                if (isDarkMode) {
                    document.documentElement.classList.add('dark');
                    document.body.classList.add('bg-gray-900', 'text-white');
                    // Apply dark mode styles to main content areas
                    const contentAreas = document.querySelectorAll('.bg-white');
                    contentAreas.forEach(area => {
                        area.classList.add('dark:bg-gray-800', 'dark:text-white');
                    });
                } else {
                    document.documentElement.classList.remove('dark');
                    document.body.classList.remove('bg-gray-900', 'text-white');
                    // Remove dark mode styles
                    const contentAreas = document.querySelectorAll('.dark\\:bg-gray-800');
                    contentAreas.forEach(area => {
                        area.classList.remove('dark:bg-gray-800', 'dark:text-white');
                    });
                }
            });
            
            // Also listen for Livewire updates to refresh font display
             document.addEventListener('livewire:updated', () => {
                 const fontDisplay = document.getElementById('font-percent-display');
                 if (fontDisplay) {
                     console.log('Livewire updated, font display found');
                 }
             });
        });
        
        // Test font size buttons and page input on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, testing components');
            
            // Debug TOC expansion state
            console.log('Expanded volumes:', @json($expandedVolumes ?? []));
            console.log('Expanded chapters:', @json($expandedChapters ?? []));
            
            // Test font size buttons
            const increaseFontBtn = document.getElementById('increase-font-btn');
            const decreaseFontBtn = document.getElementById('decrease-font-btn');
            const fontDisplay = document.getElementById('font-percent-display');
            
            if (increaseFontBtn) {
                console.log('Increase font button found');
                // Add click event listener for testing
                increaseFontBtn.addEventListener('click', function() {
                    console.log('Increase font button clicked!');
                });
            } else {
                console.error('Increase font button NOT found');
            }
            
            if (decreaseFontBtn) {
                console.log('Decrease font button found');
                // Add click event listener for testing
                decreaseFontBtn.addEventListener('click', function() {
                    console.log('Decrease font button clicked!');
                });
            } else {
                console.error('Decrease font button NOT found');
            }
            
            if (fontDisplay) {
                console.log('Font display found, current text:', fontDisplay.textContent);
            } else {
                console.error('Font display NOT found');
            }
            
            // Test page input field
            const pageInput = document.getElementById('page-number-input');
            if (pageInput) {
                console.log('Page input field found, current value:', pageInput.value);
                // Add input event listener for testing
                pageInput.addEventListener('input', function() {
                    console.log('Page input changed to:', this.value);
                });
                
                // Add change event listener
                pageInput.addEventListener('change', function() {
                    console.log('Page input change event triggered, value:', this.value);
                });
            } else {
                console.error('Page input field NOT found');
            }
            
            // Test navigation buttons (check both enabled and disabled states)
            const prevBtn = document.getElementById('prev-page-btn');
            const prevBtnDisabled = document.getElementById('prev-page-btn-disabled');
            const nextBtn = document.getElementById('next-page-btn');
            const nextBtnDisabled = document.getElementById('next-page-btn-disabled');
            
            if (prevBtn) {
                console.log('Previous page button found (enabled)');
                prevBtn.addEventListener('click', function() {
                    console.log('Previous page button clicked!');
                });
            } else if (prevBtnDisabled) {
                console.log('Previous page button found (disabled - at first page)');
            } else {
                console.log('Previous page button not found at all');
            }
            
            if (nextBtn) {
                console.log('Next page button found (enabled)');
                nextBtn.addEventListener('click', function() {
                    console.log('Next page button clicked!');
                });
            } else if (nextBtnDisabled) {
                console.log('Next page button found (disabled - at last page)');
            } else {
                console.log('Next page button not found at all');
            }
            
            // Test TOC expand buttons
            const expandButtons = document.querySelectorAll('.toc-expand-btn');
            console.log('Found', expandButtons.length, 'TOC expand buttons');
            
            expandButtons.forEach((btn, index) => {
                btn.addEventListener('click', function() {
                    console.log('TOC expand button', index, 'clicked!');
                });
            });
        });
    </script>
    
    <!-- CSS for movements toggle, TOC enhancements, and dark mode -->
    <style>
        /* Hide diacritics and Arabic vowel marks */
        .no-movements .diacritic,
        .no-movements .harakat,
        .no-movements [class*="diacritic"],
        .no-movements [class*="harakat"],
        .no-movements [class*="tashkeel"] {
            display: none !important;
        }
        
        /* Dark mode styles */
        .dark {
            color-scheme: dark;
        }
        
        .dark .bg-\[\#f8f5f0\] {
            background-color: #1f2937 !important;
        }
        
        .dark .bg-white {
            background-color: #374151 !important;
            color: #f9fafb !important;
        }
        
        .dark .text-\[\#5D6019\] {
            color: #a3a3a3 !important;
        }
        
        .dark .text-\[\#39100C\] {
            color: #d1d5db !important;
        }
        
        .dark .border-\[\#e0d9cc\] {
            border-color: #4b5563 !important;
        }
        
        .dark .bg-\[\#5D6019\] {
            background-color: #4b5563 !important;
        }
        
        .dark .bg-\[\#f0e9de\] {
            background-color: #4b5563 !important;
        }
        
        .dark .hover\:bg-\[\#f0e9de\]:hover {
            background-color: #6b7280 !important;
        }
        
        .dark .bg-\[\#faf8f5\] {
            background-color: #374151 !important;
        }
        
        /* Options menu animations */
        .options-menu {
            animation: slideDown 0.2s ease-out;
            transform-origin: top;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* TOC search highlight */
        .toc-search-highlight {
            background-color: #fef3c7;
            padding: 1px 2px;
            border-radius: 2px;
        }
        
        .dark .toc-search-highlight {
            background-color: #92400e;
            color: #fef3c7;
        }
        
        /* Smooth transitions */
        .transition-all {
            transition: all 0.3s ease;
        }
        
        /* Custom scrollbar for dark mode */
         .dark .toc-container::-webkit-scrollbar {
             width: 6px;
         }
         
         .dark .toc-container::-webkit-scrollbar-track {
             background: #374151;
         }
         
         .dark .toc-container::-webkit-scrollbar-thumb {
             background: #6b7280;
             border-radius: 3px;
         }
         
         .dark .toc-container::-webkit-scrollbar-thumb:hover {
             background: #9ca3af;
         }
         
         /* Simplified Mobile TOC Styles - Remove problematic transforms */
         .mobile-toc-sidebar {
             /* No initial transform - let it show normally */
             transition: opacity 0.3s ease;
         }
         
         .mobile-toc-sidebar.show {
             /* Simple opacity animation only */
             opacity: 1;
         }
         
         .mobile-toc-item {
             transition: all 0.2s ease;
             border-radius: 8px;
         }
         
         .mobile-toc-item:hover {
             background-color: #f0e9de;
             transform: translateX(-3px);
             box-shadow: 0 2px 8px rgba(93, 96, 25, 0.1);
         }
         
         /* Mobile TOC backdrop with smooth fade */
         .mobile-toc-backdrop {
             animation: fadeIn 0.3s ease-out;
         }
         
         @keyframes fadeIn {
             from { opacity: 0; }
             to { opacity: 1; }
         }
         
         /* Prevent body scroll when mobile TOC is open */
         body.mobile-toc-open {
             overflow: hidden;
             padding-right: 0px;
         }
        
        /* Hide common Arabic diacritical marks by Unicode range */
        .no-movements {
            font-feature-settings: "kern" off;
        }
        
        .no-movements * {
            text-decoration: none;
        }
        
        /* Remove Arabic diacritics using CSS */
        .no-movements {
            font-variant-ligatures: none;
        }
        
        /* Enhanced TOC animations */
        .toc-item {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .toc-item:hover {
            transform: translateX(-2px);
            box-shadow: 0 2px 8px rgba(93, 96, 25, 0.15);
        }
        
        .toc-item.active {
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(93, 96, 25, 0.25);
        }
        
        .toc-expand-btn {
            transition: transform 0.2s ease-in-out;
        }
        
        .toc-children {
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                max-height: 500px;
                transform: translateY(0);
            }
        }
        
        /* Smooth scrolling for TOC */
        .toc-container {
            scroll-behavior: smooth;
        }
        
        /* Custom scrollbar for TOC */
        .toc-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .toc-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .toc-container::-webkit-scrollbar-thumb {
            background: #5D6019;
            border-radius: 3px;
        }
        
        .toc-container::-webkit-scrollbar-thumb:hover {
            background: #4a4d13;
        }
        
        /* Force font size inheritance for prose content */
        .prose * {
            font-size: inherit !important;
        }
        
        .prose p, .prose div, .prose span {
            font-size: inherit !important;
        }
    </style>
</div>