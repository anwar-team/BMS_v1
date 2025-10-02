@php
    $hasSearchTerm = !empty(trim($tocSearch ?? ''));
    $matchesSearch = $hasSearchTerm ? $this->chapterMatchesCurrentSearch($chapter) : false;
    $isCurrentChapter = $currentChapterId === $chapter->id;
    $shouldDim = $hasSearchTerm && !$matchesSearch && !$isCurrentChapter;
    
    // دعم uniqueChildren من Trait
    $children = $chapter->uniqueChildren ?? $chapter->children ?? collect();
    $hasChildren = $children->isNotEmpty();
    
    // دعم تمييز الفصول بنفس الصفحة
    $hasSamePage = $chapter->hasSiblingsSamePage ?? false;
    $samePageCount = $chapter->samePageCount ?? 1;
@endphp

<li class="{{ $level > 0 ? 'mr-' . ($level * 3) : '' }} {{ $shouldDim ? 'toc-item-dimmed' : '' }}">
    <div class="toc-item flex items-center justify-between p-2 rounded-lg transition-all duration-300
              {{ $isCurrentChapter ? 'bg-[#957717] text-white shadow-sm active' : 'hover:bg-[#f0e9de]' }}
              {{ $matchesSearch ? 'toc-search-match' : '' }}
              {{ $hasSamePage ? 'ring-2 ring-amber-300' : '' }}">
        <div class="flex items-center cursor-pointer flex-1 gap-1" wire:click="gotoChapter({{ $chapter->id }})">
            @if($hasChildren)
                <div class="w-4 h-4"></div>
            @else
                <div class="w-4 h-4"></div>
            @endif
            
            <span class="{{ $level === 0 ? 'text-base sm:text-lg font-semibold' : 'text-sm sm:text-base' }} 
                         {{ $isCurrentChapter ? 'text-white' : ($level === 0 ? 'text-[#5D6019]' : 'text-gray-700') }} 
                         {{ !$isCurrentChapter ? 'hover:text-[#957717]' : '' }} transition-colors flex-1">
                @if($hasSearchTerm && $matchesSearch)
                    {!! $this->highlightSearchTerm($chapter->title) !!}
                @else
                    {{ $chapter->title }}
                @endif
            </span>
            
            {{-- شارة تمييز للفصول بنفس الصفحة --}}
            @if($hasSamePage && $samePageCount > 1)
                <span class="inline-flex items-center gap-1 text-xs bg-amber-200 text-amber-800 px-1.5 py-0.5 rounded-full font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ $samePageCount }}
                </span>
            @endif
            
            @if($chapter->page_start || $chapter->page_number)
                <span class="text-xs sm:text-sm {{ $isCurrentChapter ? 'text-white/80' : 'text-gray-500' }} {{ $hasSamePage ? 'bg-amber-100 px-1.5 py-0.5 rounded' : '' }}">
                    ({{ $chapter->page_start ?? $chapter->page_number }})
                </span>
            @endif
        </div>
        
        @if($hasChildren)
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
    
    @if($hasChildren && in_array($chapter->id, $expandedChapters))
        <ul class="toc-children mr-3 mt-1 space-y-1 border-r border-[#e0d9cc] pr-2 sm:pr-3">
            @foreach($children as $childChapter)
                @include('livewire.reader.partials.chapter-tree', ['chapter' => $childChapter, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>