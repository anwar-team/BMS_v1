<div>
    {{-- Search Box --}}
    @if($showSearch)
        <div class="relative max-w-md mx-auto mb-8">
            <input type="text" 
                   wire:model.live.debounce.300ms="search"
                   placeholder=" ابحث في المؤلفين أو سيرهم..."
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
            <button wire:click="$set('madhhab', 'المذهب الحنفي')" class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white {{ $madhhab === 'المذهب الحنفي' ? 'bg-green-800 text-white' : '' }}">
                المؤلفون الحنفيون
            </button>
            <a href="{{ route('show-all', ['type' => 'authors']) }}" class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                عرض الكل
            </a>
        </div>
    @endif

    {{-- Authors Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden" wire:loading.class="opacity-50" wire:target="search,perPage,previousPage,nextPage">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-right font-semibold text-gray-900 uppercase tracking-wider" style="font-size: 1.1rem;">
                            #
                        </th>
                        <th scope="col" class="px-6 py-3 text-right font-semibold text-gray-900 uppercase tracking-wider" style="font-size: 1.1rem;">
                            اسم المؤلف
                        </th>
                        <th scope="col" class="px-6 py-3 text-right font-semibold text-gray-900 uppercase tracking-wider" style="font-size: 1.1rem;">
                            عدد الكتب
                        </th>
                        <th scope="col" class="px-6 py-3 text-right font-semibold text-gray-900 uppercase tracking-wider" style="font-size: 1.1rem;">
                            المذهب
                        </th>
                        <th scope="col" class="px-6 py-3 text-right font-semibold text-gray-900 uppercase tracking-wider" style="font-size: 1.1rem;">
                            تاريخ الميلاد
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($authors as $index => $author)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900" style="font-size: 1.3rem;">
                                {{ ($authors->currentPage() - 1) * $authors->perPage() + $index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900" style="font-size: 1.3rem;">
                                    <a href="{{ route('authors.details', $author->id) }}" 
                                       class="text-green-700 hover:text-green-900 hover:underline transition-colors duration-200">
                                        {!! $this->highlightText($author->full_name, $search) !!}
                                    </a>
                                </div>
                                @if($author->biography)
                                    <div class="text-gray-500 truncate max-w-xs" style="font-size: 1.1rem;">{!! $this->highlightText(Str::limit($author->biography, 50), $search) !!}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium bg-green-100 text-green-800" style="font-size: 1.1rem;">
                                    {{ $author->books_count }} كتاب
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900" style="font-size: 1.3rem;">
                                @if($author->madhhab)
                                    {!! $this->highlightText($author->madhhab, $search) !!}
                                @else
                                    <span class="text-gray-400">غير محدد</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900" style="font-size: 1.3rem;">
                                {{ $author->birth_date ? $author->birth_date->format('Y/m/d') : 'غير محدد' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center">
                                <div class="text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <p class="font-medium text-gray-900 mb-1" style="font-size: 1.3rem;">لا يوجد مؤلفون متوفرون</p>
                                    <p class="text-gray-500" style="font-size: 1.1rem;">
                                        {{ $search ? 'جرب البحث بكلمات أخرى' : 'سيتم إضافة المؤلفين قريباً' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if($showPagination && ($authors->hasPages() || $authors->count() > 0))
            <div class="px-6 py-4 flex items-center justify-center border-t border-gray-200 bg-gray-50">
                {{-- Navigation Buttons --}}
                <div class="flex items-center gap-2">
                    @if($authors->hasPages())
                        @if($authors->onFirstPage())
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
                        
                        <span class="text-gray-600 mx-2" style="font-size: 1.1rem;">
                            {{ $authors->firstItem() ?: 0 }}-{{ $authors->lastItem() ?: 0 }} من {{ $authors->total() }}
                        </span>

                        @if($authors->hasMorePages())
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
                        <span class="text-gray-600" style="font-size: 1.1rem;">نتيجة</span>
                        <select wire:model.live="perPage" 
                                class="border border-gray-300 rounded-md px-2 py-1 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" style="font-size: 1.1rem;">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>