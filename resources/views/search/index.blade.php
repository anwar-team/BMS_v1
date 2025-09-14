<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">
            <div class="relative">
                <div class="pattern-top top-24"></div>
                <!-- محتوى البحث -->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="البحث" class="w-16 h-16">
                            <h2 class="text-4xl text-green-800 font-bold">البحث في المكتبة الكاملة</h2>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">محرك البحث المتقدم</h1>
                        
                        <form action="{{ route('search.results') }}" method="GET" class="space-y-6" id="searchForm">
                            <!-- Search Query -->
                            <div>
                                <label for="q" class="block text-sm font-medium text-gray-700 mb-2">كلمات البحث</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="q" 
                                           name="q" 
                                           value="{{ old('q') }}"
                                           placeholder="ادخل كلمات البحث... (البحث الفوري)"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                                           dir="rtl"
                                           autocomplete="off">
                                    <div id="searchLoading" class="absolute left-3 top-1/2 transform -translate-y-1/2 hidden">
                                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Filters Row -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Author Filter -->
                                <div>
                                    <label for="author_id" class="block text-sm font-medium text-gray-700 mb-2">المؤلف</label>
                                    <select id="author_id" 
                                            name="author_id" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">جميع المؤلفين</option>
                                        @foreach($authors as $author)
                                            <option value="{{ $author->id }}" {{ old('author_id') == $author->id ? 'selected' : '' }}>
                                                {{ $author->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Book Section Filter -->
                                <div>
                                    <label for="section_id" class="block text-sm font-medium text-gray-700 mb-2">القسم</label>
                                    <select id="section_id" 
                                            name="section_id" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">جميع الأقسام</option>
                                        @foreach($bookSections as $section)
                                            <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                {{ $section->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Search Button -->
                            <div class="text-center">
                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200 transform hover:scale-105">
                                    <i class="fas fa-search ml-2"></i>
                                    بحث
                                </button>
                            </div>
                        </form>
                        
                        <!-- Instant Search Results -->
                        <div id="instantResults" class="hidden mt-6">
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">النتائج الفورية:</h3>
                                <div id="resultCount" class="text-sm text-gray-600 mb-3"></div>
                            </div>
                            
                            <div id="searchResults" class="space-y-4 max-h-96 overflow-y-auto">
                                <!-- سيتم إدراج النتائج هنا بواسطة JavaScript -->
                            </div>
                            
                            <div class="text-center mt-4">
                                <button id="showMoreResults" class="text-blue-600 hover:text-blue-800 font-medium hidden">
                                    عرض المزيد من النتائج
                                </button>
                            </div>
                        </div>
                        
                        <!-- Search Tips -->
                        <div class="mt-8 bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">نصائح للبحث:</h3>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• يمكنك البحث في محتوى الصفحات وعناوين الكتب وأسماء المؤلفين</li>
                                <li>• استخدم المرشحات لتضييق نطاق البحث</li>
                                <li>• يمكنك البحث بدون كتابة نص والاعتماد على المرشحات فقط</li>
                                <li>• البحث يدعم النصوص العربية بشكل محسن</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <x-slot name="head">
        <style>
            .container {
                direction: rtl;
            }
            
            input[type="text"] {
                text-align: right;
            }
            
            .search-result-item {
                transition: all 0.2s ease;
            }
            
            .search-result-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            
            .highlight {
                background-color: #fef3c7;
                padding: 1px 2px;
                border-radius: 2px;
                font-weight: 600;
            }
        </style>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('q');
                const authorSelect = document.getElementById('author_id');
                const sectionSelect = document.getElementById('section_id');
                const instantResults = document.getElementById('instantResults');
                const searchResults = document.getElementById('searchResults');
                const resultCount = document.getElementById('resultCount');
                const searchLoading = document.getElementById('searchLoading');
                const showMoreButton = document.getElementById('showMoreResults');
                
                let searchTimeout;
                let currentPage = 1;
                let isLoading = false;
                
                // البحث الفوري عند الكتابة
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();
                    
                    if (query.length >= 2) {
                        searchTimeout = setTimeout(() => {
                            performInstantSearch(query, 1, true);
                        }, 500);
                    } else {
                        hideResults();
                    }
                });
                
                // البحث عند تغيير المرشحات
                authorSelect.addEventListener('change', function() {
                    const query = searchInput.value.trim();
                    if (query.length >= 2) {
                        performInstantSearch(query, 1, true);
                    }
                });
                
                sectionSelect.addEventListener('change', function() {
                    const query = searchInput.value.trim();
                    if (query.length >= 2) {
                        performInstantSearch(query, 1, true);
                    }
                });
                
                // عرض المزيد من النتائج
                showMoreButton.addEventListener('click', function() {
                    const query = searchInput.value.trim();
                    if (query.length >= 2) {
                        performInstantSearch(query, currentPage + 1, false);
                    }
                });
                
                function performInstantSearch(query, page = 1, resetResults = true) {
                    if (isLoading) return;
                    
                    isLoading = true;
                    searchLoading.classList.remove('hidden');
                    
                    if (resetResults) {
                        currentPage = 1;
                        searchResults.innerHTML = '';
                        showMoreButton.classList.add('hidden');
                    }
                    
                    const params = new URLSearchParams({
                        q: query,
                        page: page,
                        per_page: 10
                    });
                    
                    const authorId = authorSelect.value;
                    const sectionId = sectionSelect.value;
                    
                    if (authorId) params.append('author_id', authorId);
                    if (sectionId) params.append('section_id', sectionId);
                    
                    fetch(`/api/search?${params.toString()}`)
                        .then(response => response.json())
                        .then(data => {
                            isLoading = false;
                            searchLoading.classList.add('hidden');
                            
                            if (data.success && data.data) {
                                displayResults(data, resetResults);
                                instantResults.classList.remove('hidden');
                            } else {
                                if (resetResults) {
                                    showNoResults();
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            isLoading = false;
                            searchLoading.classList.add('hidden');
                            if (resetResults) {
                                showError();
                            }
                        });
                }
                
                function displayResults(data, resetResults) {
                    const results = data.data;
                    const pagination = data.pagination;
                    
                    if (resetResults) {
                        resultCount.textContent = `تم العثور على ${pagination.total} نتيجة`;
                        searchResults.innerHTML = '';
                    }
                    
                    results.forEach(page => {
                        const resultItem = createResultItem(page);
                        searchResults.appendChild(resultItem);
                    });
                    
                    currentPage = pagination.current_page;
                    
                    if (currentPage < pagination.last_page) {
                        showMoreButton.classList.remove('hidden');
                    } else {
                        showMoreButton.classList.add('hidden');
                    }
                }
                
                function createResultItem(page) {
                    const div = document.createElement('div');
                    div.className = 'search-result-item bg-white rounded-lg shadow-md p-4 border border-gray-200';
                    
                    const bookTitle = page.book_title || 'كتاب غير محدد';
                    const authorName = page.author_name || 'مؤلف غير محدد';
                    const content = page.content || '';
                    const pageNumber = page.page_number || 1;
                    
                    // تمييز النص المطابق
                    const query = searchInput.value.trim();
                    const highlightedContent = highlightText(content.substring(0, 200), query);
                    
                    div.innerHTML = `
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="text-lg font-semibold text-gray-800 hover:text-blue-600 cursor-pointer">
                                ${bookTitle}
                            </h4>
                            <span class="text-sm text-gray-500">صفحة ${pageNumber}</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">المؤلف: ${authorName}</p>
                        <div class="text-gray-700 leading-relaxed">
                            ${highlightedContent}...
                        </div>
                    `;
                    
                    return div;
                }
                
                function highlightText(text, query) {
                    if (!query) return text;
                    
                    const regex = new RegExp(`(${escapeRegExp(query)})`, 'gi');
                    return text.replace(regex, '<span class="highlight">$1</span>');
                }
                
                function escapeRegExp(string) {
                    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                }
                
                function showNoResults() {
                    resultCount.textContent = 'لم يتم العثور على نتائج';
                    searchResults.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <div class="text-6xl mb-4">🔍</div>
                            <h3 class="text-xl font-semibold mb-2">لم يتم العثور على نتائج</h3>
                            <p>جرب استخدام كلمات مختلفة أو تحقق من الإملاء</p>
                        </div>
                    `;
                    instantResults.classList.remove('hidden');
                }
                
                function showError() {
                    resultCount.textContent = 'حدث خطأ في البحث';
                    searchResults.innerHTML = `
                        <div class="text-center py-8 text-red-500">
                            <div class="text-6xl mb-4">⚠️</div>
                            <h3 class="text-xl font-semibold mb-2">حدث خطأ في البحث</h3>
                            <p>يرجى المحاولة مرة أخرى</p>
                        </div>
                    `;
                    instantResults.classList.remove('hidden');
                }
                
                function hideResults() {
                    instantResults.classList.add('hidden');
                    searchResults.innerHTML = '';
                }
            });
        </script>
    </x-slot>
</x-superduper.main>