{{-- Header Section --}}
@if ($title || $showSearch)
    <div class="mb-12 z-10">
        @if ($title)
            <div class="flex items-center gap-3 mb-8">
                <img src="{{ asset($headerIcon) }}" alt="Icon" class="w-16 h-16">
                <h2 class="text-4xl text-green-800 font-bold">{{ $title }}</h2>
            </div>
        @endif

        {{-- Search Box --}}
        @if ($showSearch)
            <div class="relative max-w-md mx-auto">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ $searchPlaceholder }}"
                    class="w-full px-4 py-3 pr-12 text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <button class="absolute right-3 top-1/2 transform -translate-y-1/2">
                    <svg class="w-5 h-5 text-gray-400 hover:text-green-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </div>
        @endif
    </div>
@endif

{{-- Filter Buttons --}}
@if ($showFilters && count($filterButtons) > 0)
    <div class="flex flex-wrap gap-4 mb-8">
        @foreach ($filterButtons as $filter)
            <button wire:click="setFilter('{{ $filter['value'] }}')"
                class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white 
                          {{ $activeFilter === $filter['value'] ? 'bg-green-800 text-white' : '' }}">
                {{ $filter['label'] }}
            </button>
        @endforeach
    </div>
@endif

{{-- Data Table --}}
<div class="bg-white rounded-lg shadow overflow-hidden" wire:loading.class="opacity-50">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-green-50">
                <tr>
                    @foreach ($columns as $column)
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @if ($data && $data->count() > 0)
                    @foreach ($data as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            @foreach ($columns as $column)
                                <td
                                    class="px-6 py-4 {{ $column['wrap'] ?? true ? '' : 'whitespace-nowrap' }} {{ $column['class'] ?? 'text-sm text-gray-900' }}">
                                    @if (isset($column['render']))
                                        {!! $column['render']($item, $loop) !!}
                                    @elseif(isset($column['field']))
                                        {{ data_get($item, $column['field']) }}
                                    @else
                                        {{ $column['value'] ?? '' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="{{ count($columns) }}" class="px-6 py-8 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-lg font-medium text-gray-900 mb-1">{{ $emptyMessage }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $search ? $emptySearchMessage : 'سيتم إضافة المحتوى قريباً' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Pagination Footer --}}
    @if ($data && ($data->hasPages() || $data->count() > 0))
        <div class="px-6 py-4 flex items-center justify-between border-t border-gray-200 bg-gray-50">
            {{-- Navigation Buttons --}}
            <div class="flex items-center gap-2">
                @if ($data->hasPages())
                    @if ($data->onFirstPage())
                        <button disabled
                            class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed transform transition-all duration-200">
                            <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </button>
                    @else
                        <button wire:click="previousPage"
                            class="p-2 rounded-full hover:bg-green-100 text-gray-600 hover:text-green-700 transition-all duration-200 transform hover:scale-110 hover:shadow-md active:scale-95">
                            <svg class="w-5 h-5 transition-transform duration-200 hover:-translate-x-0.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </button>
                    @endif
                @endif

                {{-- Pagination Numbers --}}
                @if ($data->hasPages())
                    <div class="flex items-center gap-1">
                        @foreach ($data->getUrlRange(max(1, $data->currentPage() - 2), min($data->lastPage(), $data->currentPage() + 2)) as $page => $url)
                            @if ($page == $data->currentPage())
                                <span
                                    class="px-3 py-1 bg-green-600 text-white rounded-md text-sm font-medium">{{ $page }}</span>
                            @else
                                <button wire:click="gotoPage({{ $page }})"
                                    class="px-3 py-1 bg-white text-gray-700 rounded-md text-sm font-medium hover:bg-green-50 hover:text-green-600 transition-colors duration-200">{{ $page }}</button>
                            @endif
                        @endforeach
                    </div>
                @endif

                @if ($data->hasPages())
                    @if ($data->hasMorePages())
                        <button wire:click="nextPage"
                            class="p-2 rounded-full hover:bg-green-100 text-gray-600 hover:text-green-700 transition-all duration-200 transform hover:scale-110 hover:shadow-md active:scale-95">
                            <svg class="w-5 h-5 transition-transform duration-200 hover:translate-x-0.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                    @else
                        <button disabled
                            class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed transform transition-all duration-200">
                            <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                    @endif
                @endif

                <span class="text-sm text-gray-600 mx-2">
                    ({{ $data->firstItem() ?: 0 }}-{{ $data->lastItem() ?: 0 }} من {{ $data->total() }})
                </span>
            </div>

            {{-- Items per page selector --}}
            @if ($showPerPageSelector)
                <div class="flex items-center gap-2">
                    <select wire:model.live="perPage"
                        class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-sm text-gray-600">عنصر في الصفحة</span>
                </div>
            @endif
        </div>
    @endif
</div>
