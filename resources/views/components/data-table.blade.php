@props([
    'data',
    'type' => 'books',
    'pageName' => null,
    'showPagination' => true,
    'emptyMessage' => 'لا توجد بيانات متوفرة',
    'showPerPage' => false
])

<div class="bg-white rounded-lg shadow overflow-hidden transition-opacity duration-300" 
     wire:loading.class="opacity-50" 
     wire:target="previousPage,nextPage">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-green-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        #
                    </th>
                    @if($type === 'books')
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            عنوان الكتاب
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المؤلف
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            القسم
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            سنة النشر
                        </th>
                    @else
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
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($data as $item)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                        </td>
                        @if($type === 'books')
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium">
                                    <a href="{{ route('book.read', ['bookId' => $item->id]) }}" 
                                       class="text-green-700 hover:text-green-900 hover:underline transition-colors duration-200">
                                        {{ $item->title }}
                                    </a>
                                </div>
                                @if($item->description)
                                    <div class="text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($item->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->authors->pluck('full_name')->implode(', ') ?: 'غير محدد' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($item->bookSection)
                                    <a href="{{ route('show-all', ['type' => 'books', 'section' => $item->bookSection->slug]) }}" 
                                       class="text-green-600 hover:text-green-800 hover:underline">
                                        {{ $item->bookSection->name }}
                                    </a>
                                @else
                                    <span class="text-gray-400">غير محدد</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->published_year ?: 'غير محدد' }}
                            </td>
                        @else
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item->full_name }}</div>
                                @if($item->biography)
                                    <div class="text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($item->biography, 60) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $item->books_count }} كتاب
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->madhhab ?: 'غير محدد' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->birth_date ? $item->birth_date->format('Y/m/d') : 'غير محدد' }}
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $type === 'books' ? '5' : '5' }}" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-lg font-medium text-gray-900 mb-1">
                                    {{ $type === 'books' ? 'لا توجد كتب متوفرة' : 'لا يوجد مؤلفون متوفرون' }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $emptyMessage }}
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showPerPage)
        {{-- شريط عدد العناصر --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-end">
                {{-- اختيار عدد العناصر في الصفحة --}}
                <div class="flex items-center space-x-2 space-x-reverse">
                    <label class="text-sm text-gray-600">عرض:</label>
                    <select 
                        wire:model.live="perPage"
                        class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <label class="text-sm text-gray-600">عنصر</label>
                </div>
            </div>
        </div>
    @endif

    {{-- Pagination Footer --}}
    @if($showPagination && ($data->hasPages() || $data->count() > 0))
        <div class="px-6 py-4 flex items-center justify-center border-t border-gray-200 bg-white">
            {{-- Navigation Buttons with centered pagination info --}}
            <div class="flex items-center gap-2">
                @if($data->hasPages())
                    @if($data->onFirstPage())
                        <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed transform transition-all duration-200">
                            <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @else
                        <button wire:click="previousPage{{ $pageName ? "('{$pageName}')" : '' }}" class="p-2 rounded-full hover:bg-green-100 text-gray-600 hover:text-green-700 transition-all duration-200 transform hover:scale-110 hover:shadow-md active:scale-95">
                            <svg class="w-5 h-5 transition-transform duration-200 hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @endif
                @endif
                
                <span class="text-sm text-gray-600 mx-4">
                    ({{ $data->firstItem() ?: 0 }}-{{ $data->lastItem() ?: 0 }} من {{ $data->total() }})
                </span>
                
                @if($data->hasPages())
                    @if($data->hasMorePages())
                        <button wire:click="nextPage{{ $pageName ? "('{$pageName}')" : '' }}" class="p-2 rounded-full hover:bg-green-100 text-gray-600 hover:text-green-700 transition-all duration-200 transform hover:scale-110 hover:shadow-md active:scale-95">
                            <svg class="w-5 h-5 transition-transform duration-200 hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                    @else
                        <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed transform transition-all duration-200">
                            <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>
