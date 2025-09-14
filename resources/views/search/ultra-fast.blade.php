<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">
            <div class="relative">
                <div class="pattern-top top-24"></div>
                <!-- محتوى البحث المُحسَّن -->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="البحث" class="w-16 h-16">
                            <h2 class="text-4xl text-green-800 font-bold">البحث الفوري المُحسَّن</h2>
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">Ultra-Fast</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">البحث الفوري في 769,521 صفحة</h1>
                        
                        <!-- صندوق البحث الفوري -->
                        <div class="space-y-6">
                            <div>
                                <label for="instantSearch" class="block text-sm font-medium text-gray-700 mb-2">
                                    البحث الفوري (النتائج تظهر أثناء الكتابة)
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           id="instantSearch" 
                                           placeholder="ابحث في المكتبة الكاملة... (من أول حرف)"
                                           class="w-full px-4 py-4 text-lg border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-right transition-all"
                                           dir="rtl"
                                           autocomplete="off">
                                    
                                    <!-- مؤشر التحميل -->
                                    <div id="searchSpinner" class="absolute left-4 top-1/2 transform -translate-y-1/2 hidden">
                                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                    </div>
                                    
                                    <!-- أيقونة البحث -->
                                    <div id="searchIcon" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                
                                <!-- معلومات البحث -->
                                <div id="searchInfo" class="mt-2 text-sm text-gray-600 hidden">
                                    <span id="searchTime"></span> • <span id="resultCount"></span>
                                </div>
                            </div>
                            
                            <!-- فلاتر سريعة -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">عدد النتائج لكل صفحة</label>
                                    <select id="perPageSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="10">10 نتائج</option>
                                        <option value="15" selected>15 نتيجة</option>
                                        <option value="25">25 نتيجة</option>
                                        <option value="50">50 نتيجة</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                                    <div id="connectionStatus" class="px-3 py-2 rounded-lg bg-green-100 text-green-800 text-sm">
                                        🟢 متصل بالبحث المُحسَّن
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- منطقة النتائج -->
                        <div id="resultsContainer" class="mt-8">
                            <!-- رسالة البداية -->
                            <div id="welcomeMessage" class="text-center py-12">
                                <div class="text-6xl mb-4">🔍</div>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">البحث الفوري جاهز</h3>
                                <p class="text-gray-500">ابدأ الكتابة لرؤية النتائج فوراً من 769,521 صفحة</p>
                                <div class="mt-4 text-sm text-gray-400">
                                    مدعوم بـ Elasticsearch 7.17.6 + Context7 MCP Best Practices
                                </div>
                            </div>
                            
                            <!-- النتائج -->
                            <div id="searchResults" class="hidden space-y-4">
                                <!-- النتائج ستظهر هنا -->
                            </div>
                            
                            <!-- رسالة "لا توجد نتائج" -->
                            <div id="noResults" class="hidden text-center py-8">
                                <div class="text-4xl mb-3">😔</div>
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">لا توجد نتائج</h3>
                                <p class="text-gray-500">جرب كلمات بحث أخرى</p>
                            </div>
                            
                            <!-- رسالة خطأ -->
                            <div id="searchError" class="hidden text-center py-8">
                                <div class="text-4xl mb-3">⚠️</div>
                                <h3 class="text-lg font-semibold text-red-700 mb-2">خطأ في البحث</h3>
                                <p class="text-red-500">حدث خطأ أثناء البحث، يرجى المحاولة مرة أخرى</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <style>
        .highlight {
            background-color: #fef08a;
            padding: 0 2px;
            border-radius: 2px;
            font-weight: 600;
        }
        
        .result-card {
            transition: all 0.2s ease;
        }
        
        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>

    <script>
        // البحث الفوري المُحسَّن
        class UltraFastSearch {
            constructor() {
                this.searchInput = document.getElementById('instantSearch');
                this.searchSpinner = document.getElementById('searchSpinner');
                this.searchIcon = document.getElementById('searchIcon');
                this.searchInfo = document.getElementById('searchInfo');
                this.searchTime = document.getElementById('searchTime');
                this.resultCount = document.getElementById('resultCount');
                this.resultsContainer = document.getElementById('searchResults');
                this.welcomeMessage = document.getElementById('welcomeMessage');
                this.noResults = document.getElementById('noResults');
                this.searchError = document.getElementById('searchError');
                this.perPageSelect = document.getElementById('perPageSelect');
                
                this.searchTimeout = null;
                this.currentPage = 1;
                
                this.init();
            }
            
            init() {
                // البحث أثناء الكتابة
                this.searchInput.addEventListener('input', (e) => {
                    clearTimeout(this.searchTimeout);
                    const query = e.target.value.trim();
                    
                    if (query.length === 0) {
                        this.showWelcome();
                        return;
                    }
                    
                    if (query.length >= 1) { // بحث من أول حرف
                        this.searchTimeout = setTimeout(() => {
                            this.performSearch(query);
                        }, 300); // تأخير 300ms للسرعة
                    }
                });
                
                // تغيير عدد النتائج
                this.perPageSelect.addEventListener('change', () => {
                    const query = this.searchInput.value.trim();
                    if (query.length >= 1) {
                        this.performSearch(query);
                    }
                });
            }
            
            showWelcome() {
                this.welcomeMessage.classList.remove('hidden');
                this.resultsContainer.classList.add('hidden');
                this.noResults.classList.add('hidden');
                this.searchError.classList.add('hidden');
                this.searchInfo.classList.add('hidden');
            }
            
            showLoading() {
                this.searchSpinner.classList.remove('hidden');
                this.searchIcon.classList.add('hidden');
            }
            
            hideLoading() {
                this.searchSpinner.classList.add('hidden');
                this.searchIcon.classList.remove('hidden');
            }
            
            async performSearch(query) {
                this.showLoading();
                
                const perPage = this.perPageSelect.value;
                const startTime = performance.now();
                
                try {
                    const response = await fetch(`/api/ultra-search?q=${encodeURIComponent(query)}&per_page=${perPage}&page=${this.currentPage}`);
                    const data = await response.json();
                    
                    const searchTime = Math.round(performance.now() - startTime);
                    
                    this.hideLoading();
                    
                    if (data.success && data.data && data.data.length > 0) {
                        this.displayResults(data.data, data.pagination, searchTime);
                    } else {
                        this.showNoResults();
                    }
                    
                } catch (error) {
                    console.error('Search error:', error);
                    this.hideLoading();
                    this.showError();
                }
            }
            
            displayResults(results, pagination, searchTime) {
                this.welcomeMessage.classList.add('hidden');
                this.noResults.classList.add('hidden');
                this.searchError.classList.add('hidden');
                this.resultsContainer.classList.remove('hidden');
                this.searchInfo.classList.remove('hidden');
                
                // تحديث معلومات البحث
                this.searchTime.textContent = `${searchTime}ms`;
                this.resultCount.textContent = `${pagination.total} نتيجة`;
                
                // عرض النتائج
                this.resultsContainer.innerHTML = results.map((result, index) => `
                    <div class="result-card bg-gray-50 rounded-lg p-6 border border-gray-200 hover:border-blue-300">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                    ${result.book_title || 'كتاب غير محدد'}
                                </h3>
                                <div class="text-sm text-gray-600 mb-2">
                                    <span class="inline-flex items-center gap-1">
                                        📖 صفحة ${result.page_number || 'غير محدد'}
                                    </span>
                                    ${result.author_name ? `• بقلم: ${result.author_name}` : ''}
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 bg-white px-2 py-1 rounded">
                                النتيجة ${index + 1}
                            </div>
                        </div>
                        
                        <div class="text-gray-700 leading-relaxed text-right" dir="rtl">
                            ${result.content || 'لا يوجد محتوى للعرض'}
                        </div>
                        
                        <div class="mt-4 flex justify-between items-center">
                            <div class="flex gap-2">
                                <button class="px-3 py-1 bg-blue-100 text-blue-700 rounded text-sm hover:bg-blue-200 transition-colors">
                                    عرض الصفحة
                                </button>
                                <button class="px-3 py-1 bg-green-100 text-green-700 rounded text-sm hover:bg-green-200 transition-colors">
                                    عرض الكتاب
                                </button>
                            </div>
                            <div class="text-xs text-gray-400">
                                Score: ${result.score ? result.score.toFixed(2) : 'N/A'}
                            </div>
                        </div>
                    </div>
                `).join('');
            }
            
            showNoResults() {
                this.welcomeMessage.classList.add('hidden');
                this.resultsContainer.classList.add('hidden');
                this.searchError.classList.add('hidden');
                this.noResults.classList.remove('hidden');
                this.searchInfo.classList.add('hidden');
            }
            
            showError() {
                this.welcomeMessage.classList.add('hidden');
                this.resultsContainer.classList.add('hidden');
                this.noResults.classList.add('hidden');
                this.searchError.classList.remove('hidden');
                this.searchInfo.classList.add('hidden');
            }
        }
        
        // تهيئة البحث عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            new UltraFastSearch();
        });
    </script>
</x-superduper.main>