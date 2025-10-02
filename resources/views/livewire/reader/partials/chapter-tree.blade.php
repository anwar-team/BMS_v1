@php
    $hasSearchTerm = !empty(trim($tocSearch ?? ''));
    $matchesSearch = $hasSearchTerm ? $this->chapterMatchesCurrentSearch($chapter) : false;
    $isCurrentChapter = $currentChapterId === $chapter->id;
    $shouldDim = $hasSearchTerm && !$matchesSearch && !$isCurrentChapter;
@endphp

<li class="{{ $level > 0 ? 'mr-' . ($level * 3) : '' }} {{ $shouldDim ? 'toc-item-dimmed' : '' }}">
    <div class="toc-item flex items-center justify-between p-2 rounded-lg transition-all duration-300
              {{ $isCurrentChapter ? 'bg-[#957717] text-white shadow-sm active' : 'hover:bg-[#f0e9de]' }}
              {{ $matchesSearch ? 'toc-search-match' : '' }}">
        <div class="flex items-center cursor-pointer flex-1" wire:click="gotoChapter({{ $chapter->id }})">
            @if($chapter->children->isNotEmpty())
                <div class="w-4 h-4 ml-1"></div>
            @else
                <div class="w-4 h-4 ml-1"></div>
            @endif
            
            <span class="{{ $level === 0 ? 'text-base sm:text-lg font-semibold' : 'text-sm sm:text-base' }} 
                         {{ $isCurrentChapter ? 'text-white' : ($level === 0 ? 'text-[#5D6019]' : 'text-gray-700') }} 
                         {{ !$isCurrentChapter ? 'hover:text-[#957717]' : '' }} transition-colors">
                @if($hasSearchTerm && $matchesSearch)
                    {!! $this->highlightSearchTerm($chapter->title) !!}
                @else
                    {{ $chapter->title }}
                @endif
            </span>
            
            @if($chapter->page_number)
                @php
                    // Check if there are other chapters with the same page number
                    $samePageChapters = collect($tableOfContents['data'] ?? [])->flatMap(function($item) {
                        if (isset($item->chapters)) {
                            return $item->chapters;
                        }
                        return [$item];
                    })->where('page_number', $chapter->page_number)->count();
                @endphp
                
                <span class="mr-auto text-xs sm:text-sm {{ $isCurrentChapter ? 'text-white/80' : 'text-gray-500' }} flex items-center gap-1">
                    ({{ $chapter->page_number }})
                    @if($samePageChapters > 1)
                        <span class="inline-block w-1.5 h-1.5 bg-orange-400 rounded-full" title="Multiple chapters on this page"></span>
                    @endif
                </span>
            @endif
        </div>
        
        @if($chapter->children->isNotEmpty())
            <button wire:click="toggleChapter({{ $chapter->id }})" 
                    class="toc-expand-btn p-1 rounded-full hover:bg-black/10 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="h-3 w-3 sm:h-4 sm:w-4 transition-transform duration-200 
                            {{ in_array($chapter->id, $expandedChapters) ? 'rotate-180' : '' }}" 
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        @endif
    </div>
    
    @if($chapter->children->isNotEmpty() && in_array($chapter->id, $expandedChapters))
        <ul class="toc-children mr-3 mt-1 space-y-1 border-r border-[#e0d9cc] pr-2 sm:pr-3">
            @foreach($chapter->children as $childChapter)
                @include('livewire.reader.partials.chapter-tree', ['chapter' => $childChapter, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>