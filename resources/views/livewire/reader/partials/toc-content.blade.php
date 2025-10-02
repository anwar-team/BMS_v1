{{-- Shared TOC Content Component --}}
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
            @php
                $hasSearchTerm = !empty(trim($tocSearch ?? ''));
                $volumeTitle = $volume->title ?: 'الجزء ' . $volume->number;
                $volumeMatchesSearch = $hasSearchTerm ? stripos($volumeTitle, trim($tocSearch)) !== false : false;
                $isCurrentVolume = $currentVolumeId === $volume->id;
                $shouldDim = $hasSearchTerm && !$volumeMatchesSearch && !$isCurrentVolume;
            @endphp
            <li class="{{ $shouldDim ? 'toc-item-dimmed' : '' }} {{ $volumeMatchesSearch ? 'toc-search-match' : '' }}">
                <div class="toc-item flex items-center justify-between p-2 rounded-lg 
                             {{ $currentVolumeId === $volume->id ? 'bg-[#5D6019] text-white shadow-md active' : 'hover:bg-[#f0e9de]' }}">
                    <div class="flex items-center cursor-pointer flex-1" 
                         wire:click="gotoVolume({{ $volume->id }})">
                        <span class="font-bold text-lg sm:text-xl {{ $currentVolumeId === $volume->id ? 'text-white' : 'text-[#5D6019]' }}">
                            @if($volumeMatchesSearch && stripos($volumeTitle, trim($tocSearch)) !== false)
                                {!! $this->highlightSearchTerm($volumeTitle, trim($tocSearch)) !!}
                            @else
                                {{ $volume->title ?: 'الجزء ' . $volume->number }}
                            @endif
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
            @php
                $hasSearchTerm = !empty($tocSearch);
                $chapterMatches = $hasSearchTerm && $this->chapterMatchesCurrentSearch($chapter);
                $isCurrentChapter = $currentChapterId === $chapter->id;
                $shouldDim = $hasSearchTerm && !$chapterMatches && !$isCurrentChapter;
            @endphp
            <li class="{{ $shouldDim ? 'toc-item-dimmed' : '' }} {{ $chapterMatches ? 'toc-search-match' : '' }}">
                <div class="toc-item flex items-center justify-between p-2 rounded-lg 
                             {{ $currentChapterId === $chapter->id ? 'bg-[#5D6019] text-white shadow-md active' : 'hover:bg-[#f0e9de]' }}">
                    <div class="flex items-center cursor-pointer flex-1" 
                         wire:click="gotoChapter({{ $chapter->id }})">
                        <span class="font-bold text-lg sm:text-xl {{ $currentChapterId === $chapter->id ? 'text-white' : 'text-[#5D6019]' }}">
                            @if($chapterMatches && stripos($chapter->title, $tocSearch) !== false)
                                {!! $this->highlightSearchTerm($chapter->title, $tocSearch) !!}
                            @else
                                {{ $chapter->title }}
                            @endif
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
                        @foreach($chapter->children as $childChapter)
                            @include('livewire.reader.partials.chapter-tree', ['chapter' => $childChapter, 'level' => 1])
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
@endif