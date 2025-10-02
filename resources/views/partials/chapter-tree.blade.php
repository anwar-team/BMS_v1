{{-- 
    مكون شجرة الفصول - موحد لجميع الأجهزة
    المتغيرات المطلوبة:
    - $chapter: الفصل الحالي
    - $level: مستوى التفرع (0 = رئيسي، 1 = فرعي، ...)
    
    المزايا الجديدة:
    - دعم uniqueChildren (من Trait)
    - تمييز الفصول بنفس الصفحة بصريًا
--}}

@php
    $children = $chapter->uniqueChildren ?? $chapter->children ?? collect();
    $hasChildren = $children->isNotEmpty();
    $hasSamePage = $chapter->hasSiblingsSamePage ?? false;
    $samePageCount = $chapter->samePageCount ?? 1;
@endphp

<li class="chapter-item {{ $level > 0 ? 'mt-1 sm:mt-2' : 'mb-2' }}">
    <a href="{{ route('book.read', ['bookId' => $chapter->book_id, 'pageNumber' => $chapter->page_start ?? 1]) }}" 
       class="chapter-link flex items-center gap-1 sm:gap-2 hover:text-[#957717] transition-colors duration-200 group
              {{ $level === 0 ? 'text-[#5D6019] font-bold text-base sm:text-lg' : '' }}
              {{ $level === 1 ? 'text-gray-700 font-semibold text-sm sm:text-base' : '' }}
              {{ $level >= 2 ? 'text-gray-600 text-xs sm:text-sm' : '' }}
              {{ $hasSamePage ? 'bg-amber-50 hover:bg-amber-100 px-2 py-1 rounded-md border border-amber-200' : '' }}">
        
        {{-- أيقونة السهم/النقطة --}}
        @if($hasChildren)
            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="flex-shrink-0 h-3 w-3 sm:h-4 sm:w-4 text-gray-400 group-hover:text-[#957717]" 
                 fill="none" 
                 viewBox="0 0 24 24" 
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        @else
            {{-- نقطة صغيرة للفصول بدون فروع --}}
            <span class="flex-shrink-0 inline-block w-1 h-1 sm:w-1.5 sm:h-1.5 bg-gray-400 rounded-full group-hover:bg-[#957717]"></span>
        @endif
        
        {{-- عنوان الفصل --}}
        <span class="flex-1 break-words">{{ $chapter->title }}</span>
        
        {{-- شارة تمييز للفصول بنفس الصفحة --}}
        @if($hasSamePage && $samePageCount > 1)
            <span class="flex-shrink-0 inline-flex items-center gap-1 text-xs bg-amber-200 text-amber-800 px-2 py-0.5 rounded-full font-medium border border-amber-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                {{ $samePageCount }}
            </span>
        @endif
        
        {{-- رقم الصفحة --}}
        @if($chapter->page_start)
            <span class="flex-shrink-0 text-xs {{ $hasSamePage ? 'bg-amber-100 border-amber-300' : 'bg-gray-100' }} text-gray-600 px-1.5 py-0.5 rounded border">
                ص{{ $chapter->page_start }}
            </span>
        @endif
    </a>
    
    {{-- الفصول الفرعية (متكرر) --}}
    @if($hasChildren)
        <ul class="chapter-children mr-2 sm:mr-3 mt-1 sm:mt-2 space-y-1 
                   border-r-2 pr-2 sm:pr-3
                   {{ $level === 0 ? 'border-[#e0d9cc]' : 'border-dashed border-gray-300' }}">
            @foreach($children as $childChapter)
                @include('partials.chapter-tree', ['chapter' => $childChapter, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>