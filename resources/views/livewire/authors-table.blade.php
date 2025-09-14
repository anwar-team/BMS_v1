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
        <div class="flex flex-wrap gap-4 mb-8">
            <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                المؤلفون المشهورون
            </button>
            <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                الأكثر كتابة
            </button>
            <a href="{{ route('show-all', ['type' => 'authors']) }}" class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                جميع المؤلفين
            </a>
        </div>
    @endif

    {{-- Authors Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden" wire:loading.class="opacity-50" wire:target="search,perPage,previousPage,nextPage">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-base">
                <thead class="bg-green-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            #
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            اسم المؤلف
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            عدد الكتب
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المذهب
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            تاريخ الميلاد
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($authors as $author)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                {{ $loop->iteration + ($authors->currentPage() - 1) * $authors->perPage() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('authors.details', $author->id) }}" 
                                           class="text-green-700 hover:text-green-900 hover:underline transition-colors duration-200">
                                            {!! $this->highlightText($author->full_name, $search) !!}
                                        </a>
                                    </div>
                                    @if($author->biography)
                                        <div class="text-sm text-gray-500 truncate max-w-xs">{!! $this->highlightText(Str::limit($author->biography, 50), $search) !!}</div>
                                    @endif
                                </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $author->books_count }} كتاب
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($author->madhhab)
                                    {!! $this->highlightText($author->madhhab, $search) !!}
                                @else
                                    <span class="text-gray-400">غير محدد</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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
                                    <p class="text-lg font-medium text-gray-900 mb-1">لا يوجد مؤلفون متوفرون</p>
                                    <p class="text-sm text-gray-500">
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
            <div class="px-6 py-4 flex items-center justify-between border-t border-gray-200 bg-gray-50">
                {{-- Navigation Buttons --}}
                <div class="flex items-center gap-2">
                    @if($authors->hasPages())
                        @if($authors->hasMorePages())
                            <button wire:click="nextPage" class="p-2 rounded-full hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @else
                            <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @endif
                        
                        <span class="text-sm text-gray-600 mx-2">
                            {{ $authors->firstItem() ?: 0 }}-{{ $authors->lastItem() ?: 0 }} من {{ $authors->total() }}
                        </span>

                        @if($authors->onFirstPage())
                            <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                        @else
                            <button wire:click="previousPage" class="p-2 rounded-full hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                        @endif
                    @endif
                </div>

                {{-- Items per page selector --}}
                @if($showPerPageSelector)
                    <div class="flex items-center gap-2">
                        <select wire:model.live="perPage" 
                                class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-600">مؤلف في الصفحة</span>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>