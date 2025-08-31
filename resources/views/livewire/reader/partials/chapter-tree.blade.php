<li class="{{ $level > 0 ? 'mr-' . ($level * 3) : '' }}">
    <div class="flex items-center py-1 cursor-pointer hover:bg-[#f0e9de] rounded transition-colors"
         wire:click="gotoChapter({{ $chapter->id }})">
        @if($chapter->children->isNotEmpty())
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1 text-[#5D6019]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        @else
            <div class="w-3 h-3 ml-1"></div>
        @endif
        
        <span class="text-sm {{ $level === 0 ? 'font-semibold text-[#5D6019]' : 'text-gray-700' }} hover:text-[#957717] transition-colors">
            {{ $chapter->title }}
        </span>
        
        @if($chapter->page_number)
            <span class="mr-auto text-xs text-gray-500">
                ({{ $chapter->page_number }})
            </span>
        @endif
    </div>
    
    @if($chapter->children->isNotEmpty())
        <ul class="mr-2 space-y-1 border-r border-[#e0d9cc] pr-2">
            @foreach($chapter->children as $childChapter)
                @include('livewire.reader.partials.chapter-tree', ['chapter' => $childChapter, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>