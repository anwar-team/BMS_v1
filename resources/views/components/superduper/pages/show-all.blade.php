<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper ">
            <!-- background pattern-->
            <div class="relative">
                <div class="pattern-top top-24"></div>
                <div class="pattern-top top-80"></div>
                <!-- end of background pattern-->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    
                    {{-- Header Section --}}
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                            <h2 class="text-4xl text-green-800 font-bold">{{ $title }}</h2>
                        </div>

                        {{-- Search Box --}}
                        <div class="relative max-w-md mx-auto">
                            <form method="GET" action="{{ request()->url() }}">
                                {{-- الحفاظ على المعاملات الأخرى --}}
                                @foreach(request()->except(['search', 'page']) as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       placeholder="{{ $type === 'authors' ? 'ابحث في المؤلفين...' : 'ابحث في الكتب...' }}"
                                       class="w-full px-4 py-3 pr-12 text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400 hover:text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Filter Buttons --}}
                    <div class="flex flex-wrap gap-4 mb-8">
                        @if($type === 'books')
                            <a href="{{ route('show-all', ['type' => 'books']) }}" 
                               class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white {{ !request('section') ? 'bg-green-800 text-white' : '' }}">
                                جميع الكتب
                            </a>
                            {{-- عرض رابط للعودة إلى القسم إذا كان المستخدم يتصفح قسماً معيناً --}}
                            @if($currentSection)
                                <span class="bg-green-100 text-green-800 px-5 py-2 rounded-full">
                                    قسم: {{ $currentSection->name }}
                                </span>
                            @endif
                        @else
                            <a href="{{ route('show-all', ['type' => 'authors']) }}" 
                               class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white bg-green-800 text-white">
                                جميع المؤلفين
                            </a>
                        @endif
                    </div>

                    {{-- Data Table --}}
                    <div class="bg-white rounded-lg shadow overflow-hidden">
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
                                                {{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}
                                            </td>
                                            @if($type === 'books')
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->title }}</div>
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
                                                        {{ request('search') ? 'جرب البحث بكلمات أخرى' : 'سيتم إضافة المحتوى قريباً' }}
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Footer --}}
                        @if($data->hasPages() || $data->count() > 0)
                            <div class="px-6 py-4 flex items-center justify-between border-t border-gray-200 bg-gray-50">
                                {{-- Navigation Buttons --}}
                                <div class="flex items-center gap-2">
                                    @if($data->hasPages())
                                        @if($data->onFirstPage())
                                            <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                                </svg>
                                            </button>
                                        @else
                                            <a href="{{ $data->previousPageUrl() }}" class="p-2 rounded-full hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                                </svg>
                                            </a>
                                        @endif

                                        @if($data->hasMorePages())
                                            <a href="{{ $data->nextPageUrl() }}" class="p-2 rounded-full hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        @else
                                            <button disabled class="p-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    @endif
                                    
                                    <span class="text-sm text-gray-600 mx-2">
                                        {{ $data->firstItem() ?: 0 }}-{{ $data->lastItem() ?: 0 }} من {{ $data->total() }}
                                    </span>
                                </div>

                                {{-- Items per page selector --}}
                                <div class="flex items-center gap-2">
                                    <form method="GET" class="flex items-center gap-2">
                                        {{-- الحفاظ على جميع المعاملات الأخرى --}}
                                        @foreach(request()->except(['per_page', 'page']) as $key => $value)
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endforeach
                                        
                                        <select name="per_page" 
                                                onchange="this.form.submit()" 
                                                class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                            <option value="25" {{ request('per_page', 50) == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page', 50) == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                        <span class="text-sm text-gray-600">عنصر في الصفحة</span>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>

                </section>
            </div>
        </main>
    </div>
</x-superduper.main>