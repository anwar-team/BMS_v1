<div>
    {{-- Search Box --}}
    @if($showSearch)
        <div class="relative max-w-md mx-auto mb-8">
            <input type="text" 
                   wire:model.live.debounce.300ms="search"
                   placeholder=" ابحث في عناوين الكتب أو المؤلفين أو اسماء الأقسام..."
                   class="w-full px-4 py-3 pr-12 text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    @endif

    {{-- Filter Buttons --}}
    @if($showFilters)
        <div class="flex flex-wrap gap-2 mb-4">
            <a href="{{ route('show-all', ['type' => 'books', 'section' => 'الفقه-الحنفي']) }}" class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                كتب الفقه الحنفي
            </a>
            <a href="{{ route('show-all', ['type' => 'books']) }}" class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                عرض جميع الكتب
            </a>
        </div>
    @endif



    {{-- Books Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden" wire:loading.class="opacity-50" wire:target="search,perPage,section,previousPage,nextPage">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-right font-medium text-gray-900 uppercase tracking-wider w-16" style="font-size: 1.1rem;">
                            #
                        </th>
                        <th scope="col" class="px-6 py-3 text-right font-medium text-gray-900 uppercase tracking-wider w-2/5" style="font-size: 1.1rem;">
                            اسم الكتاب
                        </th>
                        <th scope="col" class="px-6 py-3 text-right font-medium text-gray-900 uppercase tracking-wider w-1/4" style="font-size: 1.1rem;">
                            المؤلف
                        </th>
                        <th scope="col" class="px-6 py-3 text-right font-medium text-gray-900 uppercase tracking-wider w-1/4" style="font-size: 1.1rem;">
                            القسم
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($books as $index => $book)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-4 whitespace-nowrap text-gray-900 w-16" style="font-size: 1.3rem;">
                                {{ ($books->currentPage() - 1) * $books->perPage() + $index + 1 }}
                            </td>
                            <td class="px-6 py-4 w-2/5">
                                <div class="font-medium text-gray-900" style="font-size: 1.3rem;">
                                    <a href="{{ route('book.read', $book->id) }}" 
                                       class="text-green-700 hover:text-green-900 hover:underline transition-colors duration-200">
                                        {!! $this->highlightText($book->title, $search) !!}
                                    </a>
                                </div>
                                @if($book->subtitle)
                                    <div class="text-gray-500" style="font-size: 1.1rem;">{!! $this->highlightText($book->subtitle, $search) !!}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap w-1/4">
                                <div class="text-gray-900" style="font-size: 1.3rem;">
                                    @if($book->author)
                                        <a href="{{ route('authors.details', $book->author->id) }}" 
                                           class="text-green-700 hover:text-green-900 hover:underline transition-colors duration-200">
                                            {!! $this->highlightText($book->author->full_name, $search) !!}
                                        </a>
                                    @else
                                        <span class="text-gray-400">غير محدد</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap w-1/4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium bg-green-100 text-green-800" style="font-size: 1.1rem;">
                                    {{ $book->section->name ?? 'غير محدد' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center">
                                <div class="text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="font-medium text-gray-900 mb-1" style="font-size: 1.3rem;">لا توجد كتب متوفرة</p>
                                    <p class="text-gray-500" style="font-size: 1.1rem;">
                                        {{ $search ? 'جرب البحث بكلمات أخرى' : 'سيتم إضافة الكتب قريباً' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if($showPagination && ($books->hasPages() || $books->count() > 0))
            <div class="px-6 py-4 flex items-center justify-center border-t border-gray-200 bg-gray-50 relative">
                {{-- Navigation Buttons --}}
                <div class="flex items-center gap-2">
                    @if($books->hasPages())
                        @if($books->onFirstPage())
                            <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @else
                            <button wire:click="previousPage" class="p-2 rounded-full hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @endif
                        
                        <span class="text-sm text-gray-600 mx-2">
                            {{ $books->firstItem() ?: 0 }}-{{ $books->lastItem() ?: 0 }} من {{ $books->total() }}
                        </span>

                        @if($books->hasMorePages())
                            <button wire:click="nextPage" class="p-2 rounded-full hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                        @else
                            <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                        @endif
                    @endif
                </div>

                {{-- Items per page selector --}}
                @if($showPerPageSelector)
                    <div class="flex items-center gap-2 absolute right-6">
                        <span class="text-sm text-gray-600">نتيجة</span>
                        <select wire:model.live="perPage" 
                                class="border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>