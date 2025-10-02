<li class="{{ $level > 0 ? 'mt-1 sm:mt-2' : '' }}">
    <a href="{{ route('book.read', ['bookId' => $chapter->book_id, 'pageNumber' => $chapter->page_start ?? 1]) }}" 
       class="{{ $level === 0 ? 'text-[#5D6019] font-bold' : ($level === 1 ? 'text-gray-700' : 'text-gray-600') }} hover:text-[#5D6019] flex items-center {{ $level === 0 ? 'text-base sm:text-lg' : ($level === 1 ? 'text-sm sm:text-base' : 'text-xs sm:text-sm') }} block">
        @if($chapter->children->isNotEmpty())
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 ml-1 sm:ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        @endif
        {{ $chapter->title }}
        @if($chapter->page_start)
            <span class="text-xs text-gray-500 mr-2">(ص {{ $chapter->page_start }})</span>
        @endif
    </a>
    
    @if($chapter->children->isNotEmpty())
        <ul class="mr-3 mt-1 sm:mt-2 space-y-1 border-r-2 {{ $level === 0 ? 'border-[#e0d9cc]' : 'border-dashed border-[#e0d9cc]' }} pr-2 sm:pr-3">
            @foreach($chapter->children as $childChapter)
                @include('partials.chapter-tree', ['chapter' => $childChapter, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>