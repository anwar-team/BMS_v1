<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">
            <div class="relative">
                <div class="pattern-top top-24"></div>
                <!-- محتوى البحث المُحسَّن -->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="البحث" class="w-8 h-8">
                            <h2 class="text-4xl text-green-800 font-bold">البحث الفوري المُحسَّن</h2>
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">Ultra-Fast</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-3xl font-bold text-gray-800">البحث الفوري في 769,521 صفحة</h1>
                            <button onclick="showHelpModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors">
                                ❓ شرح الخصائص
                            </button>
                        </div>
                        
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
                            
                            <!-- إعدادات البحث المتقدمة -->
                            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">⚙️ إعدادات البحث المتقدمة</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- نوع البحث -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">نوع البحث</label>
                                        <select id="searchMode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="flexible">مرن (افتراضي) - أفضل النتائج</option>
                                            <option value="exact_phrase">مطابقة العبارة تماماً</option>
                                            <option value="phrase_proximity">عبارة مع تباعد مسموح</option>
                                            <option value="all_words">جميع الكلمات مطلوبة</option>
                                            <option value="any_word">أي كلمة من الكلمات</option>
                                        </select>
                                        <div class="text-xs text-gray-500 mt-1">يحدد كيفية البحث في النصوص</div>
                                    </div>
                                    
                                    <!-- تباعد الكلمات -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">تباعد الكلمات</label>
                                        <select id="proximityMode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="any_order">أي ترتيب (افتراضي)</option>
                                            <option value="consecutive">متتالية (ورا بعض)</option>
                                            <option value="same_paragraph">نفس الفقرة</option>
                                        </select>
                                        <div class="text-xs text-gray-500 mt-1">يحدد المسافة المسموحة بين الكلمات</div>
                                    </div>
                                </div>
                                
                                <!-- شرح مبسط للخيارات -->
                                <div id="searchModeHelp" class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-400 text-sm text-blue-800">
                                    <div class="font-medium mb-1">البحث المرن (المختار حالياً):</div>
                                    <div>يبحث بأفضل النتائج مع مراعاة المعنى والسياق</div>
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
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 500;
        }
        
        .result-card {
            transition: box-shadow 0.2s ease;
        }
        
        .result-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modal-overlay {
            backdrop-filter: blur(3px);
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .more-options-menu {
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .toast {
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
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
                this.searchMode = document.getElementById('searchMode');
                this.proximityMode = document.getElementById('proximityMode');
                this.searchModeHelp = document.getElementById('searchModeHelp');
                
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
                
                // تغيير نوع البحث
                this.searchMode.addEventListener('change', () => {
                    this.updateSearchModeHelp();
                    const query = this.searchInput.value.trim();
                    if (query.length >= 1) {
                        this.performSearch(query);
                    }
                });
                
                // تغيير تباعد الكلمات
                this.proximityMode.addEventListener('change', () => {
                    const query = this.searchInput.value.trim();
                    if (query.length >= 1) {
                        this.performSearch(query);
                    }
                });
                
                this.updateSearchModeHelp();
            }
            
            updateSearchModeHelp() {
                const mode = this.searchMode.value;
                const helpTexts = {
                    'flexible': {
                        title: 'البحث المرن (المختار حالياً):',
                        desc: 'يبحث بأفضل النتائج مع مراعاة المعنى والسياق'
                    },
                    'exact_phrase': {
                        title: 'مطابقة العبارة تماماً:',
                        desc: 'يبحث عن العبارة كما هي بنفس الترتيب والتتابع'
                    },
                    'phrase_proximity': {
                        title: 'عبارة مع تباعد مسموح:',
                        desc: 'يبحث عن الكلمات قريبة من بعض حسب إعداد التباعد'
                    },
                    'all_words': {
                        title: 'جميع الكلمات مطلوبة:',
                        desc: 'يجب أن تكون جميع الكلمات موجودة في النص'
                    },
                    'any_word': {
                        title: 'أي كلمة من الكلمات:',
                        desc: 'يكفي وجود كلمة واحدة من كلمات البحث'
                    }
                };
                
                const help = helpTexts[mode];
                this.searchModeHelp.innerHTML = `
                    <div class="font-medium mb-1">${help.title}</div>
                    <div>${help.desc}</div>
                `;
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
                const searchMode = this.searchMode.value;
                const proximityMode = this.proximityMode.value;
                const startTime = performance.now();
                
                try {
                    const params = new URLSearchParams({
                        q: query,
                        per_page: perPage,
                        page: this.currentPage,
                        search_mode: searchMode,
                        proximity: proximityMode
                    });
                    
                    const response = await fetch(`/api/ultra-search?${params}`);
                    const data = await response.json();
                    
                    const searchTime = Math.round(performance.now() - startTime);
                    
                    this.hideLoading();
                    
                    if (data.success && data.data && data.data.length > 0) {
                        this.displayResults(data.data, data.pagination, searchTime, searchMode);
                    } else {
                        this.showNoResults();
                    }
                    
                } catch (error) {
                    console.error('Search error:', error);
                    this.hideLoading();
                    this.showError();
                }
            }
            
            displayResults(results, pagination, searchTime, searchMode) {
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
                    <div class="result-card bg-white rounded-lg p-6 border border-gray-200 hover:border-blue-300 mb-4">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="book-info text-white rounded-lg px-3 py-2 mb-3 shadow-sm">
                                    <h3 class="text-lg font-bold text-gray-800 mb-1">
                                        📚 ${result.book_title || 'كتاب غير محدد'}
                                    </h3>
                                    <div class="text-sm text-gray-600 flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                            � صفحة ${result.page_number || 'غير محدد'}
                                        </span>
                                        ${result.author_name ? `<span class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">✍️ ${result.author_name}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <div class="search-type-badge text-xs text-gray-700 px-3 py-1 rounded-full font-medium">
                                    ${this.getSearchModeLabel(searchMode)}
                                </div>
                                <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                                    النتيجة ${index + 1}
                                </div>
                            </div>
                        </div>
                        
                        <div class="content-preview text-white rounded-lg p-4 mb-4 shadow-sm">
                            <div class="text-gray-800 leading-relaxed text-right" dir="rtl">
                                <div class="result-content-${result.id} text-gray-700">
                                    ${result.content || 'لا يوجد محتوى للعرض'}
                                </div>
                                <div class="full-content-${result.id} hidden full-content-container">
                                    <!-- المحتوى الكامل سيتم تحميله هنا -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex gap-2 flex-wrap">
                                <button onclick="goToPage(${result.book_id}, ${result.page_number})" 
                                        class="action-btn px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow-md">
                                    📖 انتقال للصفحة
                                </button>
                                <button onclick="goToBook(${result.book_id})" 
                                        class="action-btn px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow-md">
                                    📚 عرض الكتاب
                                </button>
                                <button onclick="toggleFullContent(${result.id})" 
                                        class="toggle-btn toggle-btn-${result.id} action-btn px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow-md">
                                    🔍 عرض الصفحة كاملة
                                </button>
                                <div class="relative inline-block">
                                    <button onclick="toggleMoreOptions(${result.id})" 
                                            class="action-btn px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow-md">
                                        ⋯ المزيد
                                    </button>
                                    <div id="more-options-${result.id}" class="hidden more-options-menu absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl z-20 border border-gray-200 overflow-hidden">
                                        <div class="py-2">
                                            <button onclick="showRelatedPages(${result.book_id}, ${result.page_number})" class="action-btn block w-full text-right px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-all duration-200">
                                                📄 صفحات مشابهة
                                            </button>
                                            <button onclick="copyContent(${result.id})" class="action-btn block w-full text-right px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition-all duration-200">
                                                📋 نسخ النص
                                            </button>
                                            <button onclick="shareResult(${result.id})" class="action-btn block w-full text-right px-4 py-3 text-sm text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 transition-all duration-200">
                                                🔗 مشاركة النتيجة
                                            </button>
                                            <button onclick="printContent(${result.id})" class="action-btn block w-full text-right px-4 py-3 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-all duration-200">
                                                🖨️ طباعة
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 bg-gray-50 px-3 py-2 rounded-lg">
                                <div class="search-performance px-2 py-1 rounded text-white text-center">
                                    Score: ${result.score ? result.score.toFixed(2) : 'N/A'}
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
                
                // إضافة زر "تحميل المزيد" إذا كان هناك المزيد من النتائج
                if (pagination.current_page < pagination.last_page) {
                    this.resultsContainer.innerHTML += `
                        <div class="text-center mt-6 mb-4">
                            <button onclick="loadMoreResults()" 
                                    id="loadMoreBtn"
                                    class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow-md">
                                📄 تحميل المزيد من النتائج (الصفحة ${pagination.current_page + 1} من ${pagination.last_page})
                            </button>
                            <div class="text-xs text-gray-500 mt-2">
                                عرض ${pagination.from}-${pagination.to} من ${pagination.total} نتيجة
                            </div>
                        </div>
                    `;
                }
                
                // حفظ بيانات الصفحة الحالية
                this.currentPage = pagination.current_page;
                this.totalPages = pagination.last_page;
            }
            
            getSearchModeLabel(mode) {
                const labels = {
                    'flexible': 'مرن',
                    'exact_phrase': 'مطابق تماماً',
                    'phrase_proximity': 'مع تباعد',
                    'all_words': 'جميع الكلمات',
                    'any_word': 'أي كلمة'
                };
                return labels[mode] || 'مرن';
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
        
        // وظائف الانتقال والتفاعل مع النتائج
        function goToPage(bookId, pageNumber) {
            if (bookId && pageNumber) {
                window.location.href = `/book/${bookId}/${pageNumber}`;
            } else {
                alert('معلومات الصفحة غير متاحة');
            }
        }
        
        function goToBook(bookId) {
            if (bookId) {
                window.location.href = `/book/${bookId}`;
            } else {
                alert('معلومات الكتاب غير متاحة');
            }
        }
        
        // توسيع المحتوى للصفحة كاملة
        async function toggleFullContent(pageId) {
            const toggleBtn = document.querySelector(`.toggle-btn-${pageId}`);
            const shortContent = document.querySelector(`.result-content-${pageId}`);
            const fullContentDiv = document.querySelector(`.full-content-${pageId}`);
            
            if (fullContentDiv.classList.contains('hidden')) {
                // عرض المحتوى الكامل
                toggleBtn.textContent = '⏳ جاري التحميل...';
                toggleBtn.disabled = true;
                
                try {
                    const response = await fetch(`/api/page/${pageId}/full-content`);
                    const data = await response.json();
                    
                    if (data.success) {
                        fullContentDiv.innerHTML = `
                            <div class="bg-blue-50 p-4 rounded-lg mb-3">
                                <h4 class="font-semibold text-blue-800 mb-2">📄 المحتوى الكامل للصفحة ${data.page.page_number}</h4>
                                <div class="text-sm text-blue-600 mb-2">من كتاب: ${data.page.book_title}</div>
                            </div>
                            <div class="text-gray-700 leading-relaxed" dir="rtl">
                                ${data.page.full_content || 'لا يوجد محتوى متاح'}
                            </div>
                        `;
                        
                        shortContent.classList.add('hidden');
                        fullContentDiv.classList.remove('hidden');
                        toggleBtn.textContent = '📝 عرض مختصر';
                    } else {
                        alert('فشل في تحميل المحتوى الكامل');
                    }
                } catch (error) {
                    alert('خطأ في تحميل المحتوى');
                    console.error(error);
                }
                
                toggleBtn.disabled = false;
            } else {
                // العودة للعرض المختصر
                shortContent.classList.remove('hidden');
                fullContentDiv.classList.add('hidden');
                toggleBtn.textContent = '🔍 عرض الصفحة كاملة';
            }
        }
        
        // تبديل خيارات "المزيد"
        function toggleMoreOptions(resultId) {
            const optionsDiv = document.getElementById(`more-options-${resultId}`);
            
            // إخفاء كل القوائم الأخرى
            document.querySelectorAll('[id^="more-options-"]').forEach(div => {
                if (div.id !== `more-options-${resultId}`) {
                    div.classList.add('hidden');
                }
            });
            
            // تبديل القائمة الحالية
            optionsDiv.classList.toggle('hidden');
        }
        
        // إظهار الصفحات المشابهة
        async function showRelatedPages(bookId, currentPage) {
            try {
                const response = await fetch(`/api/book/${bookId}/pages?per_page=10`);
                const data = await response.json();
                
                if (data.success && data.pages.length > 0) {
                    const pagesHtml = data.pages.map(page => `
                        <div class="border-b border-gray-200 pb-2 mb-2">
                            <button onclick="goToPage(${bookId}, ${page.page_number})" 
                                    class="text-blue-600 hover:text-blue-800 font-medium">
                                صفحة ${page.page_number}
                            </button>
                            <div class="text-sm text-gray-600 mt-1">${page.content_preview}</div>
                        </div>
                    `).join('');
                    
                    const modal = `
                        <div id="pages-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                            <div class="modal-content bg-white rounded-lg max-w-2xl w-full max-h-96 overflow-hidden shadow-lg">
                                <div class="bg-blue-600 text-white p-6">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-lg font-bold">📚 صفحات من نفس الكتاب</h3>
                                        <button onclick="closeModal('pages-modal')" class="text-white hover:text-gray-200 text-xl font-bold">✕</button>
                                    </div>
                                    <p class="text-blue-100 mt-2">اختر صفحة للانتقال إليها</p>
                                </div>
                                <div class="p-6 overflow-y-auto max-h-80">
                                    <div class="space-y-3">
                                        ${data.pages.map(page => `
                                            <div class="border border-gray-200 rounded-lg p-3 hover:border-blue-300 hover:bg-gray-50 transition-colors">
                                                <button onclick="goToPage(${bookId}, ${page.page_number})" 
                                                        class="w-full text-right">
                                                    <div class="font-medium text-blue-600 mb-1">
                                                        📄 صفحة ${page.page_number}
                                                    </div>
                                                    <div class="text-sm text-gray-600">
                                                        ${page.content_preview || 'لا يوجد معاينة متاحة'}
                                                    </div>
                                                </button>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-6 py-3 border-t">
                                    <div class="text-sm text-gray-600 text-center">
                                        إجمالي ${data.pagination.total} صفحة في هذا الكتاب
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.body.insertAdjacentHTML('beforeend', modal);
                }
            } catch (error) {
                alert('خطأ في تحميل الصفحات المشابهة');
            }
        }
        
        // نسخ المحتوى
        async function copyContent(resultId) {
            const contentDiv = document.querySelector(`.result-content-${resultId}`);
            const text = contentDiv.textContent;
            
            try {
                await navigator.clipboard.writeText(text);
                showToast('تم نسخ المحتوى بنجاح! 📋');
            } catch (error) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showToast('تم نسخ المحتوى! 📋');
            }
        }
        
        // مشاركة النتيجة
        function shareResult(resultId) {
            const contentDiv = document.querySelector(`.result-content-${resultId}`);
            const text = contentDiv.textContent;
            const url = window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: 'نتيجة بحث من المكتبة',
                    text: text.substring(0, 100) + '...',
                    url: url
                });
            } else {
                const shareText = `${text.substring(0, 100)}...\n\nمن: ${url}`;
                copyContent(resultId);
                showToast('تم نسخ رابط المشاركة! 🔗');
            }
        }
        
        // طباعة المحتوى
        function printContent(resultId) {
            const contentDiv = document.querySelector(`.result-content-${resultId}`);
            const content = contentDiv.innerHTML;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html dir="rtl">
                <head>
                    <title>طباعة نتيجة البحث</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
                        .highlight { background-color: #fef08a; padding: 0 2px; }
                    </style>
                </head>
                <body>
                    <h2>نتيجة بحث من المكتبة</h2>
                    <div>${content}</div>
                    <hr style="margin: 20px 0;">
                    <p style="color: #666; font-size: 12px;">طُبع من: ${window.location.href}</p>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
        
        // إغلاق النوافذ المنبثقة
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.remove();
            }
        }
        
        // عرض رسائل التأكيد
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            // إزالة الرسالة بعد 3 ثواني
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out forwards';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }, 3000);
        }
        
        // إضافة رسوم متحركة للخروج
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
        `;
        document.head.appendChild(style);
        
        // إظهار نافذة الشرح
        function showHelpModal() {
            const helpModal = `
                <div id="help-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="modal-content bg-white rounded-lg max-w-4xl w-full max-h-96 overflow-hidden shadow-lg">
                        <div class="bg-blue-600 text-white p-6">
                            <div class="flex justify-between items-center">
                                <h3 class="text-xl font-bold">📚 شرح خصائص البحث الفوري</h3>
                                <button onclick="closeModal('help-modal')" class="text-white hover:text-gray-200 text-2xl font-bold">✕</button>
                            </div>
                            <p class="text-blue-100 mt-2">دليل شامل لاستخدام نظام البحث المتقدم</p>
                        </div>
                        <div class="p-6 overflow-y-auto max-h-80">
                            <div class="space-y-6">
                                <div class="border-b border-gray-200 pb-4">
                                    <h4 class="text-lg font-semibold text-gray-800 mb-3">🔍 أنواع البحث المتاحة</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <h5 class="font-medium text-blue-600 mb-2">البحث المرن (افتراضي)</h5>
                                            <p class="text-sm text-gray-600">يبحث بأفضل النتائج مع مراعاة المعنى والسياق. يفهم المترادفات والمعاني المختلفة للكلمات.</p>
                                        </div>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <h5 class="font-medium text-green-600 mb-2">مطابقة العبارة تماماً</h5>
                                            <p class="text-sm text-gray-600">يبحث عن العبارة كما هي بنفس الترتيب والتتابع. مفيد للبحث عن نصوص محددة أو اقتباسات.</p>
                                        </div>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <h5 class="font-medium text-purple-600 mb-2">عبارة مع تباعد مسموح</h5>
                                            <p class="text-sm text-gray-600">يبحث عن الكلمات قريبة من بعض مع السماح بكلمات أخرى بينها حسب إعداد التباعد.</p>
                                        </div>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <h5 class="font-medium text-orange-600 mb-2">جميع الكلمات مطلوبة</h5>
                                            <p class="text-sm text-gray-600">يجب وجود جميع الكلمات في النص، لكن يمكن أن تكون في أي ترتيب أو مكان.</p>
                                        </div>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <h5 class="font-medium text-red-600 mb-2">أي كلمة من الكلمات</h5>
                                            <p class="text-sm text-gray-600">يكفي وجود كلمة واحدة من كلمات البحث. مفيد للبحث الواسع والاستكشافي.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="border-b border-gray-200 pb-4">
                                    <h4 class="text-lg font-semibold text-gray-800 mb-3">⚙️ إعدادات التباعد والمسافة</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-start gap-3">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">أي ترتيب</span>
                                            <p class="text-sm text-gray-600">الكلمات يمكن أن تكون في أي مكان من النص، بأي ترتيب وبأي مسافة بينها.</p>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">متتالية (ورا بعض)</span>
                                            <p class="text-sm text-gray-600">الكلمات يجب أن تكون متتالية بلا فواصل أو كلمات أخرى بينها.</p>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-medium">نفس الفقرة</span>
                                            <p class="text-sm text-gray-600">الكلمات يجب أن تكون في نفس الفقرة من النص، مما يحافظ على السياق.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="border-b border-gray-200 pb-4">
                                    <h4 class="text-lg font-semibold text-gray-800 mb-3">🔗 خيارات المزيد لكل نتيجة</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <h5 class="font-medium text-gray-700">📄 صفحات مشابهة</h5>
                                            <p class="text-sm text-gray-600">عرض صفحات أخرى من نفس الكتاب للانتقال المباشر إليها وتصفح المحتوى المرتبط.</p>
                                        </div>
                                        <div class="space-y-2">
                                            <h5 class="font-medium text-gray-700">📋 نسخ النص</h5>
                                            <p class="text-sm text-gray-600">نسخ محتوى النتيجة إلى الحافظة للاستخدام في تطبيقات أخرى.</p>
                                        </div>
                                        <div class="space-y-2">
                                            <h5 class="font-medium text-gray-700">🔗 مشاركة النتيجة</h5>
                                            <p class="text-sm text-gray-600">مشاركة النتيجة مع الآخرين عبر وسائل التواصل أو التطبيقات المختلفة.</p>
                                        </div>
                                        <div class="space-y-2">
                                            <h5 class="font-medium text-gray-700">🖨️ طباعة المحتوى</h5>
                                            <p class="text-sm text-gray-600">طباعة النتيجة بتنسيق مناسب للقراءة والمراجعة الورقية.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800 mb-3">✨ الخصائص والميزات المتقدمة</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <h5 class="font-medium text-gray-700">🔍 البحث الفوري</h5>
                                            <p class="text-sm text-gray-600">النتائج تظهر فوراً أثناء الكتابة من أول حرف مع تحديث مستمر للنتائج.</p>
                                        </div>
                                        <div class="space-y-2">
                                            <h5 class="font-medium text-gray-700">📄 عرض المحتوى الكامل</h5>
                                            <p class="text-sm text-gray-600">إمكانية عرض النص الكامل للصفحة بدلاً من المقطع المختصر.</p>
                                        </div>
                                        <div class="space-y-2">
                                            <h5 class="font-medium text-gray-700">📚 تحميل المزيد</h5>
                                            <p class="text-sm text-gray-600">زر "تحميل المزيد" في نهاية النتائج لعرض صفحات إضافية من النتائج.</p>
                                        </div>
                                        <div class="space-y-2">
                                            <h5 class="font-medium text-gray-700">⚡ سرعة فائقة</h5>
                                            <p class="text-sm text-gray-600">البحث في أكثر من 769,521 صفحة بسرعة تقل عن 350 مللي ثانية.</p>
                                        </div>
                                        <div class="space-y-2">
                                            <h5 class="font-medium text-gray-700">🎯 دقة عالية</h5>
                                            <p class="text-sm text-gray-600">نظام تسجيل النقاط المتقدم لترتيب النتائج حسب الصلة والأهمية.</p>
                                        </div>
                                        <div class="space-y-2">
                                            <h5 class="font-medium text-gray-700">📱 متوافق مع الأجهزة</h5>
                                            <p class="text-sm text-gray-600">يعمل بكفاءة على الحاسوب والجوال والتابلت مع واجهة متجاوبة.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 border-t">
                            <div class="text-sm text-gray-600 text-center">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-medium">
                                    نظام بحث متطور مع تقنية Elasticsearch • أكثر من 769,521 صفحة
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', helpModal);
        }
        
        // تحميل المزيد من النتائج
        async function loadMoreResults() {
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            const query = document.getElementById('instantSearch').value.trim();
            
            if (!query) return;
            
            // تحديث زر التحميل
            loadMoreBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> جاري تحميل المزيد...';
            loadMoreBtn.disabled = true;
            
            // الحصول على الصفحة التالية من زر التحميل
            const currentPageMatch = loadMoreBtn.textContent.match(/الصفحة (\d+)/);
            const nextPage = currentPageMatch ? parseInt(currentPageMatch[1]) : 2;
            
            const perPage = document.getElementById('perPageSelect').value;
            const searchMode = document.getElementById('searchMode').value;
            const proximityMode = document.getElementById('proximityMode').value;
            
            try {
                const params = new URLSearchParams({
                    q: query,
                    per_page: perPage,
                    page: nextPage,
                    search_mode: searchMode,
                    proximity: proximityMode
                });
                
                const response = await fetch(`/api/ultra-search?${params}`);
                const data = await response.json();
                
                if (data.success && data.data && data.data.length > 0) {
                    // إزالة زر "تحميل المزيد" الحالي
                    loadMoreBtn.parentElement.remove();
                    
                    // إضافة النتائج الجديدة
                    const newResults = data.data.map((result, index) => `
                        <div class="result-card bg-white rounded-lg p-6 border border-gray-200 hover:border-blue-300 mb-4">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                        📚 ${result.book_title || 'كتاب غير محدد'}
                                    </h3>
                                    <div class="text-sm text-gray-600 mb-2">
                                        <span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-1 rounded text-xs">
                                            📄 صفحة ${result.page_number || 'غير محدد'}
                                        </span>
                                        ${result.author_name ? `<span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-1 rounded text-xs ml-2">✍️ ${result.author_name}</span>` : ''}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded">
                                    النتيجة ${((nextPage - 1) * perPage) + index + 1}
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <div class="text-gray-700 leading-relaxed text-right" dir="rtl">
                                    <div class="result-content-${result.id}">
                                        ${result.content || 'لا يوجد محتوى للعرض'}
                                    </div>
                                    <div class="full-content-${result.id} hidden">
                                        <!-- المحتوى الكامل سيتم تحميله هنا -->
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div class="flex gap-2 flex-wrap">
                                    <button onclick="goToPage(${result.book_id}, ${result.page_number})" 
                                            class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm transition-colors">
                                        📖 انتقال للصفحة
                                    </button>
                                    <button onclick="goToBook(${result.book_id})" 
                                            class="px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-sm transition-colors">
                                        📚 عرض الكتاب
                                    </button>
                                    <button onclick="toggleFullContent(${result.id})" 
                                            class="toggle-btn-${result.id} px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded text-sm transition-colors">
                                        🔍 عرض كامل
                                    </button>
                                    <div class="relative inline-block">
                                        <button onclick="toggleMoreOptions(${result.id})" 
                                                class="px-3 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded text-sm transition-colors">
                                            ⋯ المزيد
                                        </button>
                                        <div id="more-options-${result.id}" class="hidden more-options-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-20 border border-gray-200">
                                            <div class="py-1">
                                                <button onclick="showRelatedPages(${result.book_id}, ${result.page_number})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    📄 صفحات مشابهة
                                                </button>
                                                <button onclick="copyContent(${result.id})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    📋 نسخ النص
                                                </button>
                                                <button onclick="shareResult(${result.id})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    🔗 مشاركة
                                                </button>
                                                <button onclick="printContent(${result.id})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    🖨️ طباعة
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <span class="bg-gray-100 px-2 py-1 rounded">${getSearchModeLabel(searchMode)}</span>
                                    • Score: ${result.score ? result.score.toFixed(2) : 'N/A'}
                                </div>
                            </div>
                        </div>
                    `).join('');
                    
                    // إضافة النتائج الجديدة للصفحة
                    document.getElementById('searchResults').insertAdjacentHTML('beforeend', newResults);
                    
                    // إضافة زر "تحميل المزيد" الجديد إذا كان هناك المزيد
                    if (data.pagination.current_page < data.pagination.last_page) {
                        document.getElementById('searchResults').insertAdjacentHTML('beforeend', `
                            <div class="text-center mt-6 mb-4">
                                <button onclick="loadMoreResults()" 
                                        id="loadMoreBtn"
                                        class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow-md">
                                    📄 تحميل المزيد من النتائج (الصفحة ${data.pagination.current_page + 1} من ${data.pagination.last_page})
                                </button>
                                <div class="text-xs text-gray-500 mt-2">
                                    عرض ${data.pagination.from}-${data.pagination.to} من ${data.pagination.total} نتيجة
                                </div>
                            </div>
                        `);
                    }
                    
                    showToast(`تم تحميل ${data.data.length} نتيجة إضافية! 📄`);
                } else {
                    loadMoreBtn.disabled = false;
                    loadMoreBtn.innerHTML = '📄 لا توجد نتائج إضافية';
                    showToast('لا توجد نتائج إضافية للعرض');
                }
            } catch (error) {
                console.error('Error loading more results:', error);
                showToast('خطأ في تحميل المزيد من النتائج');
                loadMoreBtn.disabled = false;
                loadMoreBtn.innerHTML = '📄 تحميل المزيد من النتائج';
            }
        }
        
        // وظيفة مساعدة لتسميات أنواع البحث
        function getSearchModeLabel(mode) {
            const labels = {
                'flexible': 'مرن',
                'exact_phrase': 'مطابق تماماً',
                'phrase_proximity': 'مع تباعد',
                'all_words': 'جميع الكلمات',
                'any_word': 'أي كلمة'
            };
            return labels[mode] || 'مرن';
        }
        
        // إخفاء القوائم عند النقر خارجها
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[onclick*="toggleMoreOptions"]') && !event.target.closest('[id^="more-options-"]')) {
                document.querySelectorAll('[id^="more-options-"]').forEach(div => {
                    div.classList.add('hidden');
                });
            }
        });
        
        // تهيئة البحث عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            new UltraFastSearch();
        });
    </script>
</x-superduper.main>