<div class="min-h-screen bg-white relative overflow-hidden font-tajawal text-neutral-dark-1">

    <!-- Header/Navigation -->
    <header class="bg-white border-b border-neutral-line py-4 px-4 md:px-32 relative z-10">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center justify-between w-full">
                <!-- Navigation Links (Right to Left for RTL) -->
                <nav class="flex items-center gap-6">
                    <a href="#" class="text-neutral-dark-1 text-base font-normal leading-6 relative hover:text-primary-green transition-colors duration-200">
                        الكتب
                    </a>
                    <div class="bg-neutral-line w-px h-6"></div>
                    <a href="#" class="text-neutral-dark-1 text-base font-normal leading-6 relative hover:text-primary-green transition-colors duration-200">
                        الأقسام
                    </a>
                    <div class="bg-neutral-line w-px h-6"></div>
                    <a href="#" class="text-neutral-dark-1 text-base font-normal leading-6 relative hover:text-primary-green transition-colors duration-200">
                        عن المكتبة
                    </a>
                    <div class="bg-neutral-line w-px h-6"></div>
                    <div class="flex flex-col items-start justify-center relative">
                        <a href="#" class="text-neutral-dark-1 text-base font-normal leading-6 relative">
                            الرئيسية
                        </a>
                        <div class="mt-[-2px] border-b-2 border-primary-green w-full h-0 relative"></div>
                    </div>
                </nav>
                <!-- Logos -->
                <div class="flex items-center gap-2">
                    <img class="w-36 h-11 object-cover aspect-[145/44]" src="{{ asset('storage/icon/untitled-design-7-20.png') }}" alt="Logo 1" />
                    <img class="w-11 h-11 object-cover aspect-square" src="{{ asset('storage/icon/untitled-design-8-10.png') }}" alt="Logo 2" />
                </div>
            </div>
        </div>
    </header>

    <!-- Background patterns - positioned absolutely relative to the main container -->
    <!-- Adjusted top positions to match Figma more closely -->
    <img class="opacity-20 w-48 h-auto absolute -left-14 top-20 overflow-visible z-0" src="{{ asset('storage/icon/pattern-ff-18-e-023-20.svg') }}" alt="Pattern 1" />
    <img class="opacity-30 w-48 h-auto absolute -left-14 top-80 overflow-visible z-0" src="{{ asset('storage/icon/pattern-ff-18-e-023-30.svg') }}" alt="Pattern 2" />
    <img class="opacity-40 w-48 h-auto absolute -left-14 top-[590px] overflow-visible z-0" src="{{ asset('storage/icon/pattern-ff-18-e-023-40.svg') }}" alt="Pattern 3" />
    
    <div class="opacity-40 flex flex-row gap-0 items-center justify-start absolute left-1/2 -translate-x-1/2 top-20 z-0">
        <img class="flex-shrink-0 w-80 h-auto relative overflow-visible" src="{{ asset('storage/icon/pattern-ff-18-e-023-50.svg') }}" alt="Pattern 4" />
        <img class="flex-shrink-0 w-80 h-auto relative overflow-visible" src="{{ asset('storage/icon/pattern-ff-18-e-023-60.svg') }}" alt="Pattern 5" />
        <img class="flex-shrink-0 w-80 h-auto relative overflow-visible" src="{{ asset('storage/icon/pattern-ff-18-e-023-70.svg') }}" alt="Pattern 6" />
        <img class="flex-shrink-0 w-80 h-auto relative overflow-visible" src="{{ asset('storage/icon/pattern-ff-18-e-023-71.svg') }}" alt="Pattern 7" />
        <!-- Duplicated patterns for wider effect as in Figma -->
        <img class="flex-shrink-0 w-80 h-auto relative overflow-visible" src="{{ asset('storage/icon/pattern-ff-18-e-023-60.svg') }}" alt="Pattern 5" />
        <img class="flex-shrink-0 w-80 h-auto relative overflow-visible" src="{{ asset('storage/icon/pattern-ff-18-e-023-70.svg') }}" alt="Pattern 6" />
        <img class="flex-shrink-0 w-80 h-auto relative overflow-visible" src="{{ asset('storage/icon/pattern-ff-18-e-023-71.svg') }}" alt="Pattern 7" />
    </div>

    <!-- Main content area -->
    <main class="container mx-auto px-4 md:px-0 mt-20 pb-24 relative z-10">
        <div class="flex flex-col gap-6 items-end">
            <!-- Book title with icon -->
            <div class="flex items-center gap-3 flex-wrap justify-end text-right">
                <h1 class="text-[41px] leading-[60px] font-bold text-neutral-dark-1 flex items-center justify-end">
                    <span>
                        <span>{{ $book->title }}</span>
                        @if($mainAuthors->count() > 0)
                            <span class="mr-2">[</span>
                            <span>{{ $mainAuthors->first()->full_name }}</span>
                            <span>]</span>
                        @endif
                    </span>
                </h1>
                <img class="w-15 h-15 overflow-visible" src="{{ asset('storage/icon/group0.svg') }}" alt="Book Icon" />
            </div>

            <!-- Search and tools -->
            <div class="flex flex-col gap-4 items-start w-full">
                <div class="flex flex-col-reverse md:flex-row items-center justify-between w-full gap-6">
                    <!-- Search box -->
                    <div class="flex flex-1 relative h-10 w-full md:w-auto">
                        <input type="text" placeholder="ابحث ..." class="bg-white rounded-md border border-gray-300 w-full h-full px-4 text-right text-sm text-neutral-dark-4 placeholder-neutral-dark-4 focus:outline-none focus:border-primary-green pr-10" />
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <img class="w-6 h-6" src="{{ asset('storage/icon/iconly-light-search0.svg') }}" alt="Search" />
                        </div>
                    </div>
                    <!-- Tools -->
                    <div class="flex items-center gap-6">
                        <img class="w-5 h-6 overflow-visible cursor-pointer" src="{{ asset('storage/icon/group1.svg') }}" alt="Bookmark" />
                        <div class="w-6 h-6 overflow-hidden cursor-pointer">
                            <img class="w-5 h-5 absolute inset-1 overflow-visible" src="{{ asset('storage/icon/group2.svg') }}" alt="Share" />
                        </div>
                        <div class="w-6 h-6 overflow-hidden cursor-pointer">
                            <img class="w-6 h-4.5 absolute inset-y-1.5 inset-x-0 overflow-visible" src="{{ asset('storage/icon/group3.svg') }}" alt="Download" />
                        </div>
                    </div>
                </div>

                <!-- Content area with sidebar -->
                <div class="flex flex-col md:flex-row-reverse gap-3 w-full">
                    <!-- Main content area -->
                    <div class="flex flex-col gap-8 flex-1 w-full md:w-[774px]">
                        <div class="flex flex-col gap-2 w-full">
                            <!-- Book content panel -->
                            <div class="bg-background-paper rounded-md border border-neutral-line shadow-md overflow-hidden">
                                <div class="flex flex-col w-full">
                                    <div class="flex items-center justify-end w-full">
                                        <div class="p-4 flex-1 relative">
                                            <div class="flex flex-col items-end justify-start relative">
                                                <div class="text-right text-base leading-relaxed tracking-wide font-normal text-text-primary w-full">
                                                    @if($activeChapter)
                                                        <h2 class="text-xl font-bold mb-4">{{ $activeChapter->title }}</h2>
                                                    @endif
                                                    
                                                    @if($activePage)
                                                        <div class="rtl font-naskh leading-relaxed text-right">
                                                            {!! $activePage->content !!}
                                                        </div>
                                                    @else
                                                        <div class="text-center py-12">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                            </svg>
                                                            <p class="mt-4 text-gray-500">لم يتم العثور على محتوى لهذا الفصل</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Pagination controls -->
                                    @if($activePage)
                                    <div class="p-4 flex items-center justify-between w-full border-t border-neutral-line">
                                        <button wire:click="previousPage" class="flex items-center gap-2 cursor-pointer text-primary-green disabled:opacity-50 disabled:cursor-not-allowed" {{ $activePage->page_number == 1 ? 'disabled' : '' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M15 18l-6-6 6-6"/>
                                            </svg>
                                            <span>الصفحة السابقة</span>
                                        </button>
                                        
                                        <div class="text-gray-600 text-sm">
                                            صفحة {{ $activePage->page_number }} من {{ $book->pages_count }}
                                        </div>
                                        
                                        <button wire:click="nextPage" class="flex items-center gap-2 cursor-pointer text-primary-green disabled:opacity-50 disabled:cursor-not-allowed" {{ $activePage->page_number == $book->pages_count ? 'disabled' : '' }}>
                                            <span>الصفحة التالية</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 18l6-6-6-6"/>
                                            </svg>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar with chapters -->
                    <div class="flex flex-col gap-3 w-full md:w-96">
                        <div class="bg-background-paper rounded-md border border-neutral-line p-4 flex flex-col gap-3 shadow-md">
                            <h3 class="text-right font-bold text-base leading-normal text-text-primary w-full">
                                فهرس الكتاب
                            </h3>
                            
                            <div class="h-px bg-gray-300 w-full my-1"></div>
                            
                            <div class="max-h-[500px] overflow-y-auto w-full">
                                @foreach($chapters as $chapter)
                                    <div class="mb-2">
                                        <!-- Main chapter -->
                                        <div 
                                            wire:click="selectChapter({{ $chapter->id }})"
                                            class="flex justify-between items-center p-2 cursor-pointer rounded-md transition-colors duration-200 
                                            {{ $activeChapter && $activeChapter->id === $chapter->id ? 'bg-light-green text-primary-green' : 'hover:bg-gray-50' }}"
                                        >
                                            <div class="flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                                <span class="text-sm">{{ $chapter->title }}</span>
                                            </div>
                                            
                                            @if($chapter->children->count() > 0)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                                    class="transition-transform duration-200 {{ $activeChapter && ($activeChapter->id === $chapter->id || $activeChapter->parent_id === $chapter->id) ? 'rotate-90' : '' }}">
                                                    <path d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        
                                        <!-- Subchapters -->
                                        @if($chapter->children->count() > 0 && $activeChapter && ($activeChapter->id === $chapter->id || $activeChapter->parent_id === $chapter->id))
                                            <div class="mr-4 pr-2 border-r-2 border-gray-300">
                                                @foreach($chapter->children as $subChapter)
                                                    <div 
                                                        wire:click="selectChapter({{ $subChapter->id }})"
                                                        class="flex items-center p-1.5 cursor-pointer rounded-md mt-1 transition-colors duration-200 
                                                        {{ $activeChapter && $activeChapter->id === $subChapter->id ? 'bg-light-green text-primary-green' : 'hover:bg-gray-50' }}"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M9 5l7 7-7 7"></path>
                                                        </svg>
                                                        <span class="text-xs mr-2">{{ $subChapter->title }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="w-full mt-20 pt-24 relative clear-both bg-gray-100 py-8 text-center border-t border-neutral-line">
        <div class="max-w-6xl mx-auto px-5">
            <div class="flex justify-center">
                <div class="w-30 h-30 mx-auto mb-5 overflow-hidden rounded-full">
                    <img src="{{ asset('images/figma/logo.jpg') }}" alt="Logo" class="w-full h-full object-cover">
                </div>
            </div>
            <div class="h-px bg-neutral-line my-5"></div>
            <div class="font-tajawal text-gray-600 text-sm">
                © حقوق الطبع والنشر {{ date('Y') }}. جميع الحقوق محفوظة.
            </div>
        </div>
    </footer>
</div>
