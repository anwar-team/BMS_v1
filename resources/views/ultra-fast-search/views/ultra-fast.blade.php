<x-superduper.main>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>البحث الفوري المُحسَّن - {{ config('app.name') }}</title>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
        <style>
            .font-tajawal {
                font-family: 'Tajawal', sans-serif;
            }
            
            /* RTL Search Enhancements */
            .search-container {
                direction: rtl;
                text-align: right;
            }
            
            .search-input {
                text-align: right;
                direction: rtl;
            }
            
            /* Loading spinner */
            .spinner {
                border: 3px solid #f3f3f3;
                border-top: 3px solid #10b981;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            /* Search result highlights */
            mark {
                background-color: #fef3c7;
                padding: 0 2px;
                border-radius: 2px;
                font-weight: 600;
            }
            
            /* Search filters */
            .filter-select {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 9 4 4 4-4'/%3e%3c/svg%3e");
                background-position: left 0.5rem center;
                background-repeat: no-repeat;
                background-size: 1.5em 1.5em;
                padding-left: 2.5rem;
            }
            
            /* Result item hover effects */
            .result-item:hover {
                background-color: #f9fafb;
                border-color: #10b981;
            }
            
            /* Responsive design for mobile */
            @media (max-width: 768px) {
                .search-filters {
                    flex-direction: column;
                    gap: 0.5rem;
                }
                
                .search-input {
                    font-size: 16px; /* Prevent zoom on iOS */
                }
            }
        </style>
    </head>

    <div class="page-wrapper relative z-[1] search-container font-tajawal" dir="rtl">
        <main class="relative overflow-hidden main-wrapper bg-[#f8f5f0]">
            <div class="relative">
                <div class="pattern-top top-24"></div>
                
                <!-- Search Header (updated to requested design) -->
                <div class="bg-white shadow-sm border-b border-gray-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" style="padding-top: 7.5rem;">
                        <div class="text-center mb-6">
                            <div class="flex items-center gap-3 mb-8 justify-center">
                                <img src="{{ asset('images/group0.svg') }}" alt="البحث" class="w-16 h-16">
                                <h2 class="text-4xl text-green-800 font-bold">البحث المتقدم</h2>
                                <div class="hidden sm:block">
                                    <button onclick="showHelpModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors">
                                        ❓ شرح الخصائص
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Interface -->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <!-- Search Interface -->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    
                    <!-- Search Box -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        
                        <div class="space-y-4">
                            <!-- Main Search Input -->
                            <div class="relative">
                                <input 
                                    type="text"
                                    id="instantSearch" 
                                    placeholder="ابحث في المكتبة الكاملة... (من أول حرف)"
                                    value="{{ request('q', '') }}"
                                    class="search-input w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                                    dir="rtl"
                                    autocomplete="off"
                                />
                                
                                <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                    <!-- مؤشر التحميل -->
                                    <div id="searchSpinner" class="spinner hidden"></div>
                                    <!-- أيقونة البحث -->
                                    <svg id="searchIcon" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Search Filters -->
                            <div class="search-filters flex flex-wrap gap-4">
                                <!-- نوع البحث -->
                                <div class="flex-1 min-w-[200px]">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">نوع البحث</label>
                                    <select id="searchMode" class="filter-select w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">
                                        <option value="flexible">مرن (افتراضي) - أفضل النتائج</option>
                                        <option value="exact_phrase">مطابقة العبارة تماماً</option>
                                        <option value="phrase_proximity">عبارة مع تباعد مسموح</option>
                                        <option value="all_words">جميع الكلمات مطلوبة</option>
                                        <option value="any_word">أي كلمة من الكلمات</option>
                                    </select>
                                </div>

                                <!-- تباعد الكلمات -->
                                <div class="flex-1 min-w-[200px]">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">تباعد الكلمات</label>
                                    <select id="proximityMode" class="filter-select w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">
                                        <option value="any_order">أي ترتيب (افتراضي)</option>
                                        <option value="consecutive">متتالية (ورا بعض)</option>
                                        <option value="same_paragraph">نفس الفقرة</option>
                                    </select>
                                </div>

                                <!-- عدد النتائج -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">عدد النتائج لكل صفحة</label>
                                    <select id="perPageSelect" class="filter-select w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">
                                        <option value="10">10 نتائج</option>
                                        <option value="15" selected>15 نتيجة</option>
                                        <option value="25">25 نتيجة</option>
                                        <option value="50">50 نتيجة</option>
                                    </select>
                                </div>

                                <!-- الحالة (مُحذوف المحتوى) -->
                                <div class="flex-1 min-w-[120px]"></div>
                            </div>

                            <!-- شرح مبسط للخيارات -->
                            <div id="searchModeHelp" class="p-3 bg-blue-50 border-l-4 border-blue-400 text-sm text-blue-800 rounded">
                                <div class="font-medium mb-1">البحث المرن (المختار حالياً):</div>
                                <div>يبحث بأفضل النتائج مع مراعاة المعنى والسياق</div>
                            </div>

                            <!-- Search Stats -->
                            <div id="searchInfo" class="text-sm text-gray-600 hidden">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span id="resultCount"></span>
                                        <span id="searchTime">(خلال <span></span> ميلي ثانية)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div class="space-y-4">
                        <!-- Loading State -->
                        <div id="searchLoading" class="text-center py-8 hidden">
                            <div class="spinner mx-auto mb-4"></div>
                            <p class="text-gray-600">جاري البحث...</p>
                        </div>

                        <!-- Welcome State -->
                        <div id="welcomeMessage" class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">ابدأ البحث الآن</h3>
                            <p class="text-gray-500 mb-4">ابدأ الكتابة لرؤية النتائج فوراً</p>
                        </div>

                        <!-- No Results -->
                        <div id="noResults" class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200 hidden">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.467-.881-6.08-2.33"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد نتائج</h3>
                            <p class="text-gray-600">جرب استخدام كلمات مختلفة أو قم بتعديل المرشحات</p>
                        </div>

                        <!-- Error State -->
                        <div id="searchError" class="text-center py-12 bg-white rounded-lg shadow-sm border border-red-200 hidden">
                            <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-red-700 mb-2">خطأ في البحث</h3>
                            <p class="text-red-500">حدث خطأ أثناء البحث، يرجى المحاولة مرة أخرى</p>
                        </div>

                        <!-- Results List -->
                        <div id="searchResults" class="space-y-4 hidden">
                            <!-- Results will be inserted here -->
                        </div>

                        <!-- Pagination -->
                        <div id="paginationContainer" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hidden">
                            <!-- Pagination will be inserted here -->
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

        /* Selected result (keyboard navigation) */
        .result-selected {
            outline: 3px solid rgba(16,185,129,0.15);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.08);
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
                console.debug('UltraFastSearch.performSearch start', { query: query, page: this.currentPage });
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
                    console.debug('UltraFastSearch.performSearch response', data);
                    
                    const searchTime = Math.round(performance.now() - startTime);
                    
                    this.hideLoading();
                    
                    if (data.success && data.data && data.data.length > 0) {
                        console.debug('UltraFastSearch.performSearch will display results', data.data.length);
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
                console.debug('UltraFastSearch.displayResults', { resultsCount: results.length, pagination: pagination });
                this.welcomeMessage.classList.add('hidden');
                this.noResults.classList.add('hidden');
                this.searchError.classList.add('hidden');
                this.resultsContainer.classList.remove('hidden');
                this.searchInfo.classList.remove('hidden');
                // تحديث معلومات البحث
                if (this.searchTime) this.searchTime.textContent = `${searchTime}ms`;
                if (this.resultCount) this.resultCount.textContent = `${pagination.total} نتيجة`;
                // عرض النتائج (تصميم متوافق RTL)
                // globalIndex helps maintain unique index across pages
                let globalStart = ((pagination.current_page - 1) * pagination.per_page) || 0;
                this.resultsContainer.innerHTML = results.map((result, index) => `
                    <div id="result-${globalStart + index}" data-page-id="${result.id}" tabindex="0" data-result-index="${globalStart + index}" class="result-item result-card bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                    <a href="${result.url ? result.url : ('/book/' + result.book_id + '/' + result.page_number)}" class="hover:text-emerald-600 transition-colors">${result.book_title || 'كتاب غير محدد'}</a>
                                </h3>
                                ${result.section ? `<div class="mt-2"><span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">${result.section}</span></div>` : ''}
                                <div class="text-sm text-gray-600 space-y-1">
                                    ${result.author_name ? `<div><span class="font-medium">المؤلف:</span> ${result.author_name}</div>` : ''}
                                    <div class="flex flex-wrap gap-4 items-center">
                                        ${result.section ? `<span><span class="font-medium">القسم:</span> ${result.section}</span>` : ''}
                                        ${result.chapter_title ? `<span><span class="font-medium">الفصل:</span> ${result.chapter_title}</span>` : ''}
                                        ${result.volume_title ? `<span><span class="font-medium">المجلد:</span> ${result.volume_title}</span>` : ''}
                                        <span class="flex items-center gap-2">
                                            <span class="font-medium">الصفحة:</span>
                                            <span class="text-gray-700">${result.page_number || ''}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500" >
                                    ${result.score ? `الصلة: ${Math.round(result.score * 100)}%` : ''}
                                </div>
                                <div class="text-xs text-gray-500 mt-2">النتيجة ${index + 1}</div>
                            </div>
                        </div>

                        <div class="text-gray-700 leading-relaxed mb-3">
                            <div class="result-content-${result.id}">
                                <p class="mb-3">${result.highlight || result.content || result.content_preview || 'لا يوجد محتوى للعرض'}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center gap-3 flex-row-reverse">
                                <button onclick="toggleFullContent(${result.id})" class="toggle-btn toggle-btn-${result.id} px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">🔍 عرض الصفحة كاملة</button>
                                <button onclick="copyContent(${result.id})" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">نسخ</button>
                                <button onclick="shareResult(${result.id})" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">مشاركة</button>
                                <button onclick="printContent(${result.id})" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">طباعة</button>
                                <div class="relative inline-block">
                                    <button onclick="toggleMoreOptions(${result.id})" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">⋯</button>
                                    <div id="more-options-${result.id}" class="hidden more-options-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-20 border border-gray-200">
                                        <div class="py-1">
                                            <button onclick="showRelatedPages(${result.book_id}, ${result.page_number})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📄 صفحات مشابهة</button>
                                            <button onclick="copyContent(${result.id})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📋 نسخ النص</button>
                                            <button onclick="shareResult(${result.id})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🔗 مشاركة</button>
                                            <button onclick="printContent(${result.id})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🖨️ طباعة</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 text-left">ID: ${result.id}</div>
                        </div>

                        <div class="full-content-${result.id} hidden mt-4">
                            <!-- full content will load here -->
                        </div>
                    </div>
                `).join('');

                // re-init keyboard navigation
                initKeyboardNav();
                
                // إضافة زر "تحميل المزيد" إذا كان هناك المزيد من النتائج
                if (pagination.last_page && pagination.last_page > 1) {
                    // replace with requested pagination design (non-Alpine bindings)
                    this.resultsContainer.innerHTML += `
                        <div id="paginationBar" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mt-6">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-600">
                                    صفحة <span class="font-medium">${pagination.current_page}</span> من <span class="font-medium">${pagination.last_page}</span>
                                </div>
                                <div class="flex space-x-2 space-x-reverse">
                                    <button onclick="window.searchInstance.goToPage(${Math.max(1, pagination.current_page - 1)})" ${pagination.current_page === 1 ? 'disabled' : ''} class="px-3 py-2 rounded-md text-sm font-medium transition-colors ${pagination.current_page === 1 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-emerald-600 text-white hover:bg-emerald-700'}">السابق</button>
                                    <button onclick="window.searchInstance.goToPage(${Math.min(pagination.last_page, pagination.current_page + 1)})" ${pagination.current_page === pagination.last_page ? 'disabled' : ''} class="px-3 py-2 rounded-md text-sm font-medium transition-colors ${pagination.current_page === pagination.last_page ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-emerald-600 text-white hover:bg-emerald-700'}">التالي</button>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                // حفظ بيانات الصفحة الحالية
                this.currentPage = pagination.current_page;
                this.totalPages = pagination.last_page;
            }

            goToPage(page) {
                if (!page || page < 1) return;
                this.currentPage = page;
                const query = this.searchInput.value.trim();
                if (query.length >= 1) {
                    this.performSearch(query);
                }
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
                window.location.href = `/books/${bookId}/details`;
            } else {
                alert('معلومات الكتاب غير متاحة');
            }
        }
        
        // توسيع المحتوى للصفحة كاملة
        async function toggleFullContent(pageId) {
            // locate elements more robustly: by data-page-id wrapper, or by direct classes
            const wrapper = document.querySelector(`[data-page-id='${pageId}']`) || document.getElementById(`result-${pageId}`) || document.querySelector(`#result-${pageId}`);
            const toggleBtn = wrapper ? wrapper.querySelector(`.toggle-btn-${pageId}`) : document.querySelector(`.toggle-btn-${pageId}`);
            let shortContent = wrapper ? wrapper.querySelector(`.result-content-${pageId}`) : document.querySelector(`.result-content-${pageId}`);
            let fullContentDiv = wrapper ? wrapper.querySelector(`.full-content-${pageId}`) : document.querySelector(`.full-content-${pageId}`);

            // if fullContentDiv doesn't exist, create it at the end of wrapper
            if (!fullContentDiv && wrapper) {
                fullContentDiv = document.createElement('div');
                fullContentDiv.className = `full-content-${pageId} hidden mt-4`;
                wrapper.appendChild(fullContentDiv);
            }

            // if shortContent missing, try to find any child with 'result-content-' prefix
            if (!shortContent && wrapper) {
                shortContent = Array.from(wrapper.querySelectorAll('[class*="result-content-"]'))[0] || null;
            }

            if (!toggleBtn) {
                console.warn('toggleFullContent: button not found for pageId', pageId);
                return;
            }

            const isHidden = fullContentDiv ? fullContentDiv.classList.contains('hidden') : true;

            if (isHidden) {
                toggleBtn.textContent = '⏳ جاري التحميل...';
                toggleBtn.disabled = true;

                try {
                    const response = await fetch(`/api/page/${pageId}/full-content`);
                    const data = await response.json();


                        if (data && data.success) {
                        const currentQuery = document.getElementById('instantSearch').value || '';
                        // preserve original text and spacing, escape HTML to be safe
                        const raw = data.page.full_content || 'لا يوجد محتوى متاح';
                        const escaped = escapeHtml(raw);
                        const highlighted = currentQuery ? highlightTerms(escaped, currentQuery) : escaped;

                        fullContentDiv.innerHTML = `
                            <div class="bg-blue-50 p-4 rounded-lg mb-3">
                                <h4 class="font-semibold text-blue-800 mb-2">📄 المحتوى الكامل للصفحة ${data.page.page_number}</h4>
                                <div class="text-sm text-blue-600 mb-2">من كتاب: ${data.page.book_title}</div>
                                <div class="text-sm text-gray-700 mt-1">
                                    ${data.page.section ? `<span class="inline-block bg-gray-100 px-2 py-1 rounded mr-2">${data.page.section}</span>` : ''}
                                    ${data.page.chapter_title ? `<span class="inline-block bg-gray-100 px-2 py-1 rounded mr-2">${data.page.chapter_title}</span>` : ''}
                                    ${data.page.volume_title ? `<span class="inline-block bg-gray-100 px-2 py-1 rounded mr-2">${data.page.volume_title}</span>` : ''}
                                </div>
                            </div>
                            <div class="text-gray-700 leading-relaxed" dir="rtl">
                                <div style="white-space: pre-wrap; word-wrap: break-word;">${highlighted}</div>
                            </div>
                        `;

                        if (shortContent) shortContent.classList.add('hidden');
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
                if (shortContent) shortContent.classList.remove('hidden');
                if (fullContentDiv) fullContentDiv.classList.add('hidden');
                toggleBtn.textContent = '🔍 عرض الصفحة كاملة';
            }
        }

        // escape HTML to display raw page content safely
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // تضع علامة <mark> على مصطلحات البحث داخل النص (المحتوى مفترض أنه مُهَرب بالفعل)
        function highlightTerms(escapedText, query) {
            if (!escapedText || !query) return escapedText || '';
            try {
                const terms = query.split(/\s+/).filter(t => t.length > 0);
                if (terms.length === 0) return escapedText;

                // escape terms for regex
                const escaped = terms.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
                const pattern = new RegExp('(' + escaped.join('|') + ')', 'gi');

                // Replace matches while preserving existing HTML entities
                return escapedText.replace(pattern, '<mark>$1</mark>');
            } catch (e) {
                return escapedText;
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
                                            <p class="text-sm text-gray-600">تم تصميم النظام للحصول على استجابات سريعة ضمن مئات المللي ثواني.</p>
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
                                    نظام بحث متطور مع تقنية Elasticsearch
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
                    // compute starting index for newly appended results
                    const existingCount = document.querySelectorAll('#searchResults .result-item').length;
                    const newResults = data.data.map((result, index) => `
                        <div id="result-${existingCount + index}" data-page-id="${result.id}" tabindex="0" data-result-index="${existingCount + index}" class="result-item result-card bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow mb-4">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <div class="text-xs text-gray-500 mb-2 text-left">ID: ${result.id}</div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                        <a href="${result.url ? result.url : ('/book/' + result.book_id + '/' + result.page_number)}" class="hover:text-emerald-600 transition-colors">${result.book_title || 'كتاب غير محدد'}</a>
                                    </h3>
                                    ${result.section ? `<div class="mt-2"><span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">${result.section}</span></div>` : ''}
                                        <div class="text-sm text-gray-600 flex items-center gap-3">
                                        <span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-1 rounded text-xs">الصفحة ${result.page_number || ''}</span>
                                        ${result.author_name ? `<span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-1 rounded text-xs">✍️ ${result.author_name}</span>` : ''}
                                        ${result.section ? `<span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-1 rounded text-xs">${result.section}</span>` : ''}
                                        ${result.chapter_title ? `<span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-1 rounded text-xs">${result.chapter_title}</span>` : ''}
                                        ${result.volume_title ? `<span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-1 rounded text-xs">${result.volume_title}</span>` : ''}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded">
                                    النتيجة ${((nextPage - 1) * perPage) + index + 1}
                                </div>
                            </div>

                            <div class="text-gray-700 leading-relaxed mb-3">
                                <div class="result-content-${result.id}"><p>${result.highlight || result.content || 'لا يوجد محتوى للعرض'}</p></div>
                            </div>

                            <div class="flex items-center justify-between gap-3 pt-3 border-t border-gray-100">
                                <div class="flex items-center gap-3 flex-row-reverse">
                                    <button onclick="toggleFullContent(${result.id})" class="toggle-btn toggle-btn-${result.id} px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">🔍 عرض الصفحة كاملة</button>
                                    <button onclick="copyContent(${result.id})" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">نسخ</button>
                                    <button onclick="shareResult(${result.id})" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">مشاركة</button>
                                    <button onclick="printContent(${result.id})" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">طباعة</button>
                                    <div class="relative inline-block">
                                        <button onclick="toggleMoreOptions(${result.id})" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm">⋯</button>
                                        <div id="more-options-${result.id}" class="hidden more-options-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-20 border border-gray-200">
                                            <div class="py-1">
                                                <button onclick="showRelatedPages(${result.book_id}, ${result.page_number})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📄 صفحات مشابهة</button>
                                                <button onclick="copyContent(${result.id})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📋 نسخ النص</button>
                                                <button onclick="shareResult(${result.id})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🔗 مشاركة</button>
                                                <button onclick="printContent(${result.id})" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🖨️ طباعة</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">ID: ${result.id}</div>
                            </div>

                            <div class="full-content-${result.id} hidden mt-4"><!-- will be inserted when requested --></div>
                        </div>
                    `).join('');
                    
                    // إضافة النتائج الجديدة للصفحة
                    document.getElementById('searchResults').insertAdjacentHTML('beforeend', newResults);

                    // re-init keyboard navigation for appended items
                    initKeyboardNav();
                    
                    // إضافة شريط الترقيم السابق/التالي بدلًا من زر "تحميل المزيد"
                    if (data.pagination && data.pagination.last_page && data.pagination.last_page > 1) {
                        document.getElementById('searchResults').insertAdjacentHTML('beforeend', `
                            <div id="paginationBar" class="flex items-center justify-center gap-4 mt-6">
                                <button onclick="window.searchInstance.goToPage(${Math.max(1, data.pagination.current_page - 1)})" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm" ${data.pagination.current_page === 1 ? 'disabled' : ''}>السابق</button>
                                <div class="text-sm text-gray-700">الصفحة ${data.pagination.current_page} من ${data.pagination.last_page} • إجمالي ${data.pagination.total} نتيجة</div>
                                <button onclick="window.searchInstance.goToPage(${Math.min(data.pagination.last_page, data.pagination.current_page + 1)})" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm" ${data.pagination.current_page === data.pagination.last_page ? 'disabled' : ''}>التالي</button>
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

        // ---- Keyboard navigation between results ----
        let keyboardNav = {
            focusedIndex: -1,
        };

        function initKeyboardNav() {
            const items = Array.from(document.querySelectorAll('#searchResults .result-item'));
            items.forEach((el, idx) => {
                el.setAttribute('data-result-index', idx);
                el.tabIndex = 0;

                // click/focus handlers to keep track
                el.addEventListener('focus', () => {
                    setFocusedIndex(idx);
                });
            });

            // if none focused, set first as focused when items exist
            if (items.length && keyboardNav.focusedIndex === -1) {
                setFocusedIndex(0);
            }
        }

        function setFocusedIndex(idx) {
            const items = Array.from(document.querySelectorAll('#searchResults .result-item'));
            if (!items.length) return;
            if (idx < 0) idx = 0;
            if (idx >= items.length) idx = items.length - 1;

            // remove previous
            items.forEach(i => i.classList.remove('result-selected'));
            const el = items[idx];
            el.classList.add('result-selected');
                    el.focus({ preventScroll: true });
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            keyboardNav.focusedIndex = idx;
        }

        document.addEventListener('keydown', function(e) {
            const items = Array.from(document.querySelectorAll('#searchResults .result-item'));
            if (!items.length) return;

            if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
                e.preventDefault();
                setFocusedIndex((keyboardNav.focusedIndex === -1 ? 0 : keyboardNav.focusedIndex + 1));
            } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
                e.preventDefault();
                setFocusedIndex((keyboardNav.focusedIndex === -1 ? 0 : keyboardNav.focusedIndex - 1));
            } else if (e.key === 'Enter') {
                // trigger full page view for focused item
                const idx = keyboardNav.focusedIndex;
                if (idx >= 0 && items[idx]) {
                    // find the result id from the element's inner toggle button class
                    const el = items[idx];
                    const toggleBtn = el.querySelector('[class*="toggle-btn-"]');
                    if (toggleBtn) {
                        // extract id from class name
                        const cls = Array.from(toggleBtn.classList).find(c => c.startsWith('toggle-btn-'));
                        if (cls) {
                            const id = cls.replace('toggle-btn-','');
                            toggleFullContent(id);
                        }
                    }
                }
            }
        });
        
        // تهيئة البحث عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            window.searchInstance = new UltraFastSearch();
            
            // إذا جاء المستخدم من صفحة الـ home مع نص بحث، قم بالبحث تلقائياً
            const urlParams = new URLSearchParams(window.location.search);
            const query = urlParams.get('q');
            const searchType = urlParams.get('search_type');
            
            if (query && query.trim()) {
                // إذا كان هناك نوع بحث محدد، قم بتحديث الإعداد
                if (searchType === 'authors') {
                    document.getElementById('searchMode').value = 'all_words';
                    window.searchInstance.updateSearchModeHelp();
                } else if (searchType === 'books') {
                    document.getElementById('searchMode').value = 'phrase_proximity';
                    window.searchInstance.updateSearchModeHelp();
                }
                
                // تأكد من أن النص في مربع البحث ثم قم بالبحث
                setTimeout(() => {
                    if (window.searchInstance.searchInput.value.trim()) {
                        window.searchInstance.performSearch(window.searchInstance.searchInput.value.trim());
                    }
                }, 500);
            }

            // init keyboard navigation handlers (if results present later)
            initKeyboardNav();
        });
    </script>
</x-superduper.main>