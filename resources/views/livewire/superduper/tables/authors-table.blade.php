<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <!-- Loading Indicator -->
    <div wire:loading.delay class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
        <div class="flex items-center space-x-2">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-600">جاري التحميل...</span>
        </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search Input -->
            <div class="flex-1">
                <div class="relative">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="البحث في المؤلفين..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Madhhab Filter -->
                <div class="min-w-[200px]">
                    <select 
                        wire:model.live="selectedMadhhab"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">جميع المذاهب</option>
                        @foreach($madhhabOptions as $madhhab)
                            <option value="{{ $madhhab }}">{{ $madhhab ?: 'غير محدد' }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Books Count Filter -->
                <div class="min-w-[200px]">
                    <select 
                        wire:model.live="minBooksCount"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="0">جميع المؤلفين</option>
                        <option value="1">لديه كتاب واحد على الأقل</option>
                        <option value="5">لديه 5 كتب على الأقل</option>
                        <option value="10">لديه 10 كتب على الأقل</option>
                        <option value="20">لديه 20 كتاب على الأقل</option>
                    </select>
                </div>

                <!-- Sort Options -->
                <div class="min-w-[200px]">
                    <select 
                        wire:model.live="sortBy"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="books_count_desc">الأكثر كتباً</option>
                        <option value="books_count_asc">الأقل كتباً</option>
                        <option value="name_asc">الاسم (أ-ي)</option>
                        <option value="name_desc">الاسم (ي-أ)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Results Info -->
        <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
            <div>
                عرض {{ $authors->firstItem() ?? 0 }} إلى {{ $authors->lastItem() ?? 0 }} من أصل {{ $authors->total() }} مؤلف
            </div>
            @if($search || $selectedMadhhab || $minBooksCount > 0)
                <button 
                    wire:click="resetFilters"
                    class="text-blue-600 hover:text-blue-800 font-medium"
                >
                    إعادة تعيين الفلاتر
                </button>
            @endif
        </div>
    </div>

    <!-- Authors Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button 
                            wire:click="sortBy('full_name')"
                            class="flex items-center space-x-1 hover:text-gray-700"
                        >
                            <span>اسم المؤلف</span>
                            @if(str_contains($sortBy, 'name'))
                                <svg class="w-4 h-4 {{ str_contains($sortBy, 'desc') ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button 
                            wire:click="sortBy('books_count')"
                            class="flex items-center space-x-1 hover:text-gray-700"
                        >
                            <span>عدد الكتب</span>
                            @if(str_contains($sortBy, 'books_count'))
                                <svg class="w-4 h-4 {{ str_contains($sortBy, 'asc') ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المذهب</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($authors as $index => $author)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $authors->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="flex flex-col">
                                <div class="font-medium text-gray-900">{{ $author->full_name }}</div>
                                @if($author->biography)
                                    <div class="text-gray-500 text-xs mt-1 line-clamp-2">
                                        {{ Str::limit($author->biography, 100) }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $author->books_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $author->madhhab ?: 'غير محدد' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">لا توجد مؤلفين</h3>
                                <p class="text-gray-500">لم يتم العثور على أي مؤلفين يطابقون معايير البحث.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($authors->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $authors->links() }}
        </div>
    @endif
</div>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>