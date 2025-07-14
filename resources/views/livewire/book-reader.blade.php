<div class="bg-gray-50 min-h-screen" dir="rtl">
    <!-- Header with book title and author -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 font-tajawal">{{ $book->title }}</h1>
                    @if($mainAuthors->count() > 0)
                        <p class="text-gray-600 mt-1">
                            <span class="text-sm">تأليف:</span>
                            @foreach($mainAuthors as $index => $author)
                                <span class="font-medium">{{ $author->full_name }}</span>
                                @if($index < $mainAuthors->count() - 1), @endif
                            @endforeach
                        </p>
                    @endif
                </div>
                <div class="mt-2 md:mt-0 flex items-center">
                    <button class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md text-sm flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        حفظ للمفضلة
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main content area with sidebar and content pane -->
    <div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row">
            <!-- Sidebar with chapters -->
            <div class="w-full lg:w-1/4 lg:ml-6 mb-6 lg:mb-0">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <h2 class="text-lg font-bold mb-4 text-gray-900 font-tajawal border-b pb-2">فهرس الكتاب</h2>
                    
                    <div class="space-y-2 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        @foreach($chapters as $chapter)
                            <div>
                                <!-- Main chapter -->
                                <div 
                                    class="flex items-center justify-between p-2 rounded-md cursor-pointer {{ $activeChapter && $activeChapter->id === $chapter->id ? 'bg-primary-50 text-primary-700' : 'hover:bg-gray-50' }}"
                                    wire:click="selectChapter({{ $chapter->id }})"
                                >
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm">{{ $chapter->title }}</span>
                                    </div>
                                    
                                    @if($chapter->children->count() > 0)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform {{ $activeChapter && $activeChapter->id === $chapter->id ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    @endif
                                </div>
                                
                                <!-- Subchapters -->
                                @if($chapter->children->count() > 0 && $activeChapter && ($activeChapter->id === $chapter->id || $activeChapter->parent_id === $chapter->id))
                                    <div class="mr-6 mt-1 space-y-1 border-r-2 border-gray-200 pr-2">
                                        @foreach($chapter->children as $subChapter)
                                            <div 
                                                class="flex items-center p-1.5 rounded-md cursor-pointer {{ $activeChapter && $activeChapter->id === $subChapter->id ? 'bg-primary-50 text-primary-700' : 'hover:bg-gray-50' }}"
                                                wire:click="selectChapter({{ $subChapter->id }})"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                                <span class="text-xs">{{ $subChapter->title }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Content pane -->
            <div class="w-full lg:w-3/4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    @if($activeChapter)
                        <h2 class="text-xl font-bold mb-6 pb-2 border-b text-primary-800 font-tajawal">{{ $activeChapter->title }}</h2>
                    @endif
                    
                    @if($activePage)
                        <div class="prose prose-lg max-w-none rtl font-naskh leading-relaxed text-right">
                            {!! $activePage->content !!}
                        </div>
                        
                        <!-- Pagination controls -->
                        <div class="mt-8 pt-4 border-t flex justify-between items-center">
                            <button 
                                wire:click="previousPage" 
                                class="flex items-center text-gray-600 hover:text-primary-700"
                                @if(!$activePage) disabled @endif
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                الصفحة السابقة
                            </button>
                            
                            <div class="text-sm text-gray-500">
                                صفحة {{ $activePage->page_number }} من {{ $book->pages_count }}
                            </div>
                            
                            <button 
                                wire:click="nextPage" 
                                class="flex items-center text-gray-600 hover:text-primary-700"
                                @if(!$activePage) disabled @endif
                            >
                                الصفحة التالية
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
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
</div>

<style>
    /* Custom scrollbar for webkit browsers */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #aaa;
    }
    
    /* RTL specific adjustments */
    .rtl {
        direction: rtl;
        text-align: right;
    }
    
    /* Typography */
    .font-tajawal {
        font-family: 'Tajawal', sans-serif;
    }
    
    .font-naskh {
        font-family: 'Noto Naskh Arabic', serif;
    }
    
    /* Primary colors */
    .bg-primary-50 {
        background-color: #f8f9e8;
    }
    
    .text-primary-700 {
        color: #5D6019;
    }
    
    .text-primary-800 {
        color: #4a4d14;
    }
    
    .bg-primary-600 {
        background-color: #5D6019;
    }
    
    .bg-primary-700 {
        background-color: #4a4d14;
    }
    
    /* Secondary colors */
    .text-secondary-700 {
        color: #39100C;
    }
    
    .text-secondary-600 {
        color: #FF7300;
    }
</style> 