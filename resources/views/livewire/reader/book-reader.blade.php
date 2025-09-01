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
                                    <span class="bg-[#5D6019] text-white rounded-full w-6 h-6 sm:w-8 sm:h-8 flex items-center justify-center font-bold text-sm sm:text-base">{{ $navigation['current_page_number'] }}</span>
                                    @if($currentPage && $currentPage->internal_index)
                                        <span class="text-[#39100C] mx-1 sm:mx-2 text-sm sm:text-base">({{ $currentPage->internal_index }})</span>
                                    @endif
                                    <span class="text-[#39100C] mx-1 sm:mx-2 text-sm sm:text-base">من</span>
                                    <span class="text-[#957717] font-bold text-sm sm:text-base">{{ $navigation['total_pages'] }}</span>
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
                                <!-- Font Size Controls -->
                                <div class="flex items-center border-b border-[#e0d9cc] pb-2 w-full sm:w-auto sm:border-0 sm:pb-0 sm:border-r sm:pr-4">
                                    <button id="decrease-font-btn" wire:click="decreaseFontSize" class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-[#f0e9de] hover:bg-[#e8e0d0] text-[#5D6019] font-bold transition-colors text-sm sm:text-base">
                                        A-
                                    </button>
                                    <span id="font-percent-display" class="mx-1 sm:mx-2 text-[#39100C] font-medium text-sm sm:text-base">{{ $fontPercent }}%</span>
                                    <button id="increase-font-btn" wire:click="increaseFontSize" class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-[#f0e9de] hover:bg-[#e8e0d0] text-[#5D6019] font-bold transition-colors text-sm sm:text-base">
                                        A+
                                    </button>
                                </div>
                                
                                <!-- Search -->
                                <div class="flex items-center flex-1 min-w-[150px] w-full sm:w-auto relative">
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
                                
                                <!-- Toggle Movements -->
                                <div class="flex items-center border-t border-[#e0d9cc] pt-2 w-full sm:w-auto sm:border-0 sm:pt-0 sm:border-l sm:pl-4">
                                    <button wire:click="toggleMovements" class="{{ $showMovements ? 'bg-[#5D6019] text-white' : 'bg-[#f0e9de] text-[#5D6019]' }} px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg hover:bg-[#e8e0d0] transition-colors font-tajawal text-sm sm:text-base whitespace-nowrap">
                                        {{ $showMovements ? 'إخفاء الحركات' : 'إظهار الحركات' }}
                                    </button>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex items-center w-full sm:w-auto justify-end pt-2 sm:pt-0 sm:border-l sm:pl-4">
                                    <div class="flex space-x-1 sm:space-x-2 space-x-reverse">
                                        <!-- Share Button -->
                                        <button class="text-gray-600 hover:text-[#5D6019] p-1.5 sm:p-2 rounded-full hover:bg-[#f0e9de] transition-colors flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6.632L15.316 9m-4.065 8.814a5.97 5.97 0 001.23.247m-1.23-.247A5.97 5.97 0 015 12c0-.95.23-1.84-.632-2.684m0 2.684a3 3 0 110-2.684m0 2.684L8.684 13.342m0-2.684l6.632-3.316m-4.065-1.186a5.97 5.97 0 011.23-.247m-1.23.247A5.97 5.97 0 005 12c0-.95.23-1.84-.632-2.684m0 2.684a3 3 0 110-2.684" />
                                            </svg>
                                        </button>
                                        <!-- Fullscreen Button -->
                                        <button onclick="toggleFullscreen()" class="text-gray-600 hover:text-[#5D6019] p-1.5 sm:p-2 rounded-full hover:bg-[#f0e9de] transition-colors flex items-center justify-center">
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
                                        <h2 class="text-white text-xl sm:text-2xl font-bold font-tajawal">فهرس المحتويات</h2>
                                    </div>
                                    <div class="p-4 sm:p-5 max-h-[70vh] overflow-y-auto toc-container">
                                        @if($tableOfContents['type'] === 'volumes_with_chapters')
                                            <!-- عرض الأجزاء مع الفصول -->
                                            <ul class="space-y-3">
                                                @foreach($tableOfContents['data'] as $volume)
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
                                                @foreach($tableOfContents['data'] as $chapter)
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
                                                
                                                <div class="prose prose-lg max-w-none {{ $showMovements ? '' : 'no-movements' }}">
                                                    {!! $currentContent !!}
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
                                    
                                    <!-- Navigation Bar -->
                                    <div class="px-3 py-2 sm:px-4 sm:py-3 border-t border-[#e0d9cc] bg-[#faf8f5]">
                                        <div class="flex flex-col lg:flex-row items-center justify-between gap-3 sm:gap-4">
                                            <!-- Page Navigation -->
                                            <div class="flex items-center space-x-2 sm:space-x-3 space-x-reverse order-2 lg:order-1">
                                                <div class="flex items-center space-x-1 sm:space-x-2 space-x-reverse">
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
                                                    <select wire:model.live="selectedVolume" class="bg-white border border-[#e0d9cc] rounded-lg px-2 py-1 sm:px-3 sm:py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#957717] min-w-[80px]">
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
        
        // Listen for URL updates and font size changes
        document.addEventListener('livewire:init', () => {
            console.log('Livewire initialized successfully');
            
            Livewire.on('url-update', (event) => {
                console.log('URL update:', event);
                window.history.pushState({}, '', event.url);
            });
            
            // Listen for font size changes
            Livewire.on('fontSizeChanged', (fontPercent) => {
                console.log('Font size changed to:', fontPercent);
                const contentArea = document.querySelector('[data-book-content]');
                const fontDisplay = document.getElementById('font-percent-display');
                
                if (contentArea) {
                    // Convert percentage to relative size (100% = 1em, 120% = 1.2em)
                    const relativeSize = (fontPercent / 100) + 'em';
                    contentArea.style.fontSize = relativeSize;
                    console.log('Applied font size:', relativeSize);
                } else {
                    console.error('Content area not found');
                }
                
                // Update font display
                if (fontDisplay) {
                    fontDisplay.textContent = fontPercent + '%';
                    console.log('Updated font display to:', fontPercent + '%');
                } else {
                    console.error('Font display element not found');
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
    
    <!-- CSS for movements toggle and TOC enhancements -->
    <style>
        .no-movements .diacritic,
        .no-movements .harakat {
            display: none;
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
    </style>
</div>