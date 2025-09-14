<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">
            <div class="relative">
                <div class="pattern-top top-24"></div>
                
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    <!-- Publisher Title Section -->
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                            <h1 class="text-4xl text-green-800 font-bold">تفاصيل الناشر</h1>
                        </div>
                    </div>

                    <!-- Main Publisher Details Section -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8 mb-8">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Publisher Image -->
                            <div class="lg:col-span-1">
                                @if($publisher->image)
                                    <img src="{{ asset('storage/' . $publisher->image) }}" 
                                         alt="{{ $publisher->name }}" 
                                         class="w-full h-auto rounded-lg shadow-md">
                                @else
                                    <div class="w-full h-80 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Publisher Information Card -->
                            <div class="lg:col-span-2">
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <!-- Card Title with Icon -->
                                    <div class="flex items-center gap-3 mb-6">
                                        <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-6 h-6">
                                        <h3 class="text-xl font-semibold text-green-800">{{ $publisher->name }}</h3>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-700">عدد الكتب:</span>
                                            <span class="text-gray-600">{{ $publisher->books->count() }} كتاب</span>
                                        </div>
                                        
                                        @if($publisher->address)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700">العنوان:</span>
                                                <span class="text-gray-600">{{ $publisher->address }}</span>
                                            </div>
                                        @endif
                                        
                                        @if($publisher->phone)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700">الهاتف:</span>
                                                <span class="text-gray-600">{{ $publisher->phone }}</span>
                                            </div>
                                        @endif
                                        
                                        @if($publisher->email)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700">البريد الإلكتروني:</span>
                                                <span class="text-gray-600">{{ $publisher->email }}</span>
                                            </div>
                                        @endif
                                        
                                        @if($publisher->website_url)
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700">الموقع الإلكتروني:</span>
                                                <a href="{{ $publisher->website_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">{{ $publisher->website_url }}</a>
                                            </div>
                                        @endif
                                        
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-700">الحالة:</span>
                                            <span class="px-2 py-1 rounded text-sm
                                                @if($publisher->is_active) bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800
                                                @endif
                                            ">
                                                @if($publisher->is_active) نشط
                                                @else غير نشط
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Publisher Description Section -->
                    @if($publisher->description)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8 mb-8">
                            <div class="flex items-center gap-3 mb-6">
                                <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-8 h-8">
                                <h3 class="text-2xl font-semibold text-[#5D6019]">نبذة عن الناشر</h3>
                            </div>
                            <div class="text-lg text-gray-700 leading-relaxed whitespace-pre-line">
                                {{ $publisher->description }}
                            </div>
                        </div>
                    @endif

                    <!-- Publisher Books Section -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-8 h-8">
                                <h3 class="text-2xl font-semibold text-[#5D6019]">كتب الناشر</h3>
                            </div>
                            
                            @if($books->count() > 0)
                                <!-- View Toggle Buttons -->
                                <div class="flex bg-gray-100 rounded-lg p-1">
                                    <button id="tableViewBtn" class="px-4 py-2 text-sm font-medium rounded-md bg-white text-green-700 shadow-sm transition-all duration-200">
                                        <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18m-9 8h9"></path>
                                        </svg>
                                        عرض جدول
                                    </button>
                                    <button id="cardViewBtn" class="px-4 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-green-700 transition-all duration-200">
                                        <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"></path>
                                        </svg>
                                        عرض بطاقات
                                    </button>
                                </div>
                            @endif
                        </div>
                        
                        {{-- استخدام Livewire Component للكتب --}}
                        @if($publisher->books()->count() > 0)
                            @livewire('books-table', [
                                'publisher_id' => $publisher->id,
                                'showSearch' => false,
                                'showFilters' => false,
                                'title' => 'كتب الناشر',
                                'perPage' => 10,
                                'showPagination' => true,
                                'showPerPageSelector' => true
                            ])
                        @else
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-2">
                                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500">لا توجد كتب منشورة لهذا الناشر حالياً</p>
                                <p class="text-sm text-gray-400 mt-2">سيتم إضافة الكتب عند نشرها</p>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- JavaScript for View Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableViewBtn = document.getElementById('tableViewBtn');
            const cardViewBtn = document.getElementById('cardViewBtn');
            const tableView = document.getElementById('tableView');
            const cardView = document.getElementById('cardView');

            if (tableViewBtn && cardViewBtn && tableView && cardView) {
                // Table View Button Click
                tableViewBtn.addEventListener('click', function() {
                    // Show table view
                    tableView.classList.remove('hidden');
                    cardView.classList.add('hidden');
                    
                    // Update button styles
                    tableViewBtn.classList.add('bg-white', 'text-green-700', 'shadow-sm');
                    tableViewBtn.classList.remove('text-gray-600');
                    cardViewBtn.classList.remove('bg-white', 'text-green-700', 'shadow-sm');
                    cardViewBtn.classList.add('text-gray-600');
                });

                // Card View Button Click
                cardViewBtn.addEventListener('click', function() {
                    // Show card view
                    cardView.classList.remove('hidden');
                    tableView.classList.add('hidden');
                    
                    // Update button styles
                    cardViewBtn.classList.add('bg-white', 'text-green-700', 'shadow-sm');
                    cardViewBtn.classList.remove('text-gray-600');
                    tableViewBtn.classList.remove('bg-white', 'text-green-700', 'shadow-sm');
                    tableViewBtn.classList.add('text-gray-600');
                });
            }
        });
    </script>

    <!-- Custom Styles -->
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</x-superduper.main>