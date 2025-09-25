<!-- Search Section - Independent Component -->
<div class="relative">
    <div class="pattern-top top-0"></div>
    <section class="relative z-10 bg-gradient-to-b from-green-50 to-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Search Title -->
                <div class="mb-12">
                    <h2 class="text-4xl text-green-800 font-bold mb-4">
                        ابحث في مكتبتنا الشاملة
                    </h2>
                    <p class="text-xl text-gray-600 mb-8">
                        اكتشف آلاف الكتب في الحديث، الفقه، الأدب، البلاغة، والتاريخ والأنساب وغيرها الكثير
                    </p>

                    <!-- Statistics Section -->
                    <div class="mb-8">
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-200">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Total Books -->
                            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                                <div class="relative">
                                    <img src="{{ asset('images/mask-group0.svg') }}" alt="Background" class="absolute left-0 top-0 w-32 h-32">
                                    <div class="p-4">
                                        <div class="flex justify-around items-center">
                                            <div class="bg-green-100 rounded-lg p-2">
                                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg text-green-800 font-bold mb-1">{{ number_format($stats['total_books']) }}</h3>
                                                <p class="text-xs text-gray-600">إجمالي الكتب</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Authors -->
                            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                                <div class="relative">
                                    <img src="{{ asset('images/mask-group0.svg') }}" alt="Background" class="absolute left-0 top-0 w-32 h-32">
                                    <div class="p-4">
                                        <div class="flex justify-around items-center">
                                            <div class="bg-blue-100 rounded-lg p-2">
                                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg text-green-800 font-bold mb-1">{{ number_format($stats['total_authors']) }}</h3>
                                                <p class="text-xs text-gray-600">إجمالي المؤلفين</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Pages -->
                            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                                <div class="relative">
                                    <img src="{{ asset('images/mask-group0.svg') }}" alt="Background" class="absolute left-0 top-0 w-32 h-32">
                                    <div class="p-4">
                                        <div class="flex justify-around items-center">
                                            <div class="bg-purple-100 rounded-lg p-2">
                                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg text-green-800 font-bold mb-1">{{ number_format($stats['total_pages']) }}</h3>
                                                <p class="text-xs text-gray-600">إجمالي الصفحات</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Sections -->
                            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                                <div class="relative">
                                    <img src="{{ asset('images/mask-group0.svg') }}" alt="Background" class="absolute left-0 top-0 w-32 h-32">
                                    <div class="p-4">
                                        <div class="flex justify-around items-center">
                                            <div class="bg-orange-100 rounded-lg p-2">
                                                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg text-green-800 font-bold mb-1">{{ number_format($stats['total_sections']) }}</h3>
                                                <p class="text-xs text-gray-600">إجمالي الأقسام</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Search Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 mb-10 justify-center">
                <button id="search-authors-btn" 
                        class="search-type-btn bg-white text-green-800 border-2 border-green-800 transition-all duration-300 hover:bg-green-800 hover:text-white hover:shadow-lg transform hover:-translate-y-1 px-8 py-3 rounded-3xl font-bold shadow-md text-center">
                    المؤلفين
                </button>
                <button id="search-books-btn" 
                        class="search-type-btn bg-green-700 text-white border-2 border-green-700 transition-all duration-300 hover:bg-green-800 hover:shadow-lg transform hover:-translate-y-1 px-8 py-3 rounded-3xl font-bold shadow-md text-center relative active">
                    <span class="absolute inset-0 border-2 border-green-900 rounded-3xl"></span>
                    عناوين الكتب
                </button>
                <a href="{{ route('search.ultra-fast') }}" 
                   class="bg-white text-green-800 border-2 border-green-800 transition-all duration-300 hover:bg-green-800 hover:text-white hover:shadow-lg transform hover:-translate-y-1 px-8 py-3 rounded-3xl font-bold shadow-md text-center">
                    محتوى الكتب
                </a>
            </div>

            <!-- Section Filter (for books only) -->
            <div id="section-filter-container" class="max-w-md mx-auto mb-6">
                <select id="section-filter" 
                        class="w-full bg-white border-2 border-green-200 rounded-full px-6 py-3 text-gray-700 focus:border-green-500 focus:ring-2 focus:ring-green-200 focus:outline-none">
                    <option value="">جميع الأقسام</option>
                </select>
            </div>

            <!-- Search Bar -->
            <div class="max-w-xl mx-auto relative z-[100]">
                <div class="relative bg-white rounded-full px-6 py-4 flex items-center gap-3 shadow-xl border border-gray-200 hover:shadow-2xl transition-shadow duration-300">
                    <img src="{{ asset('images/iconly-light-search0.svg') }}" alt="Search" class="w-6 h-6 text-gray-400">
                    <input
                        type="text"
                        id="search-input"
                        placeholder="إبحث في عناوين الكتب ..."
                        autocomplete="off"
                        class="w-full bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-500 text-lg focus:outline-none">
                    <button type="button"
                            id="search-btn"
                            class="absolute left-4 p-2 rounded-full bg-green-600 text-white transition-all duration-300 hover:bg-green-700 hover:scale-110 active:scale-95 shadow-md">
                        <img src="{{ asset('images/iconly-bold-send0.svg') }}" alt="Search icon" class="w-5 h-5 filter brightness-0 invert">
                    </button>
                </div>

                <!-- Dropdown Results -->
                <div id="search-dropdown" 
                     class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-2xl shadow-2xl mt-2 max-h-80 md:max-h-96 overflow-y-auto z-[99999] hidden backdrop-blur-sm"
                     style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);">
                    <div id="search-results" class="p-2 md:p-4">
                        <!-- Results will be populated here -->
                    </div>
                    <div id="search-loading" class="p-6 md:p-8 text-center text-gray-500 hidden">
                        <div class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            جاري البحث...
                        </div>
                    </div>
                    <div id="search-no-results" class="p-6 md:p-8 text-center text-gray-500 hidden">
                        <svg class="mx-auto h-10 w-10 md:h-12 md:w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-base md:text-lg font-medium text-gray-900 mb-2">لا توجد نتائج</p>
                        <p class="text-gray-500 text-sm md:text-base">جرب كلمات بحث مختلفة</p>
                    </div>
                </div>
            </div>

            <!-- Search Tips -->
            <div class="mt-8 text-sm text-gray-500">
                <p id="search-tips">💡 نصائح للبحث: استخدم كلمات مفتاحية واضحة، أو ابحث بعناوين الكتب</p>
            </div>
            
            <!-- Extra spacing to prevent dropdown overlap -->
            <div class="h-32"></div>
        </div>
    </div>
</section>
<div class="pattern-bottom bottom-0"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // عناصر DOM
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    const searchDropdown = document.getElementById('search-dropdown');
    const searchResults = document.getElementById('search-results');
    const searchLoading = document.getElementById('search-loading');
    const searchNoResults = document.getElementById('search-no-results');
    const searchTips = document.getElementById('search-tips');
    
    // أزرار نوع البحث
    const authorsBtn = document.getElementById('search-authors-btn');
    const booksBtn = document.getElementById('search-books-btn');
    const searchTypeBtns = document.querySelectorAll('.search-type-btn');
    
    // فلتر الأقسام
    const sectionFilterContainer = document.getElementById('section-filter-container');
    const sectionFilter = document.getElementById('section-filter');
    
    // متغيرات
    let currentSearchType = 'books'; // الافتراضي: عناوين الكتب
    let searchTimeout;
    let currentSectionId = '';
    let sectionsData = [];
    
    // تحميل الأقسام عند بدء التشغيل
    loadBookSections();
    
    // تغيير نوع البحث
    authorsBtn.addEventListener('click', () => setSearchType('authors'));
    booksBtn.addEventListener('click', () => setSearchType('books'));
    
    // البحث عند الكتابة
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideDropdown();
            return;
        }
        
        showLoading();
        
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    // البحث عند الضغط على الزر
    searchBtn.addEventListener('click', function() {
        const query = searchInput.value.trim();
        if (query.length >= 2) {
            performSearch(query);
        }
    });
    
    // البحث عند الضغط على Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query.length >= 2) {
                performSearch(query);
            }
        }
    });
    
    // تغيير فلتر القسم
    sectionFilter.addEventListener('change', function() {
        currentSectionId = this.value;
        const query = searchInput.value.trim();
        if (query.length >= 2 && currentSearchType === 'books') {
            performSearch(query);
        }
    });
    
    // إخفاء القائمة عند النقر خارجها
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.max-w-xl')) {
            hideDropdown();
        }
    });
    
    // إخفاء القائمة عند التمرير
    window.addEventListener('scroll', function() {
        hideDropdown();
    });
    
    // إخفاء القائمة عند الضغط على Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideDropdown();
        }
    });
    
    // تحديد نوع البحث
    function setSearchType(type) {
        currentSearchType = type;
        
        // تحديث أزرار نوع البحث
        searchTypeBtns.forEach(btn => {
            btn.classList.remove('active', 'bg-green-700', 'text-white');
            btn.classList.add('bg-white', 'text-green-800');
        });
        
        if (type === 'authors') {
            authorsBtn.classList.add('active', 'bg-green-700', 'text-white');
            authorsBtn.classList.remove('bg-white', 'text-green-800');
            searchInput.placeholder = 'إبحث في أسماء المؤلفين ...';
            searchTips.innerHTML = '💡 نصائح للبحث: استخدم أسماء المؤلفين أو أجزاء منها';
            sectionFilterContainer.style.display = 'none';
        } else {
            booksBtn.classList.add('active', 'bg-green-700', 'text-white');
            booksBtn.classList.remove('bg-white', 'text-green-800');
            searchInput.placeholder = 'إبحث في عناوين الكتب ...';
            searchTips.innerHTML = '💡 نصائح للبحث: استخدم كلمات مفتاحية واضحة، أو ابحث بعناوين الكتب';
            sectionFilterContainer.style.display = 'block';
        }
        
        // إعادة البحث إذا كان هناك نص
        const query = searchInput.value.trim();
        if (query.length >= 2) {
            performSearch(query);
        } else {
            hideDropdown();
        }
    }
    
    // تحميل أقسام الكتب
    async function loadBookSections() {
        try {
            const response = await fetch('/api/search-all/sections');
            const data = await response.json();
            
            if (data.success) {
                sectionsData = data.data;
                populateSectionFilter(data.data);
            }
        } catch (error) {
            console.error('خطأ في تحميل الأقسام:', error);
        }
    }
    
    // ملء قائمة الأقسام
    function populateSectionFilter(sections) {
        sectionFilter.innerHTML = '<option value="">جميع الأقسام</option>';
        
        sections.forEach(section => {
            const option = document.createElement('option');
            option.value = section.id;
            option.textContent = `${section.name} (${section.books_count})`;
            sectionFilter.appendChild(option);
        });
    }
    
    // تنفيذ البحث
    async function performSearch(query) {
        try {
            let url = '';
            const params = new URLSearchParams({
                q: query,
                limit: 15
            });
            
            if (currentSearchType === 'authors') {
                url = '/api/search-all/authors';
            } else {
                url = '/api/search-all/books';
                if (currentSectionId) {
                    params.append('section_id', currentSectionId);
                }
            }
            
            const response = await fetch(`${url}?${params}`);
            const data = await response.json();
            
            hideLoading();
            
            if (data.success && data.data.length > 0) {
                displayResults(data.data);
            } else {
                showNoResults();
            }
        } catch (error) {
            console.error('خطأ في البحث:', error);
            hideLoading();
            showNoResults();
        }
    }
    
    // عرض النتائج
    function displayResults(results) {
        searchResults.innerHTML = '';
        
        results.forEach(result => {
            const resultElement = createResultElement(result);
            searchResults.appendChild(resultElement);
        });
        
        showDropdown();
    }
    
    // إنشاء عنصر نتيجة
    function createResultElement(result) {
        const div = document.createElement('div');
        div.className = 'p-2 md:p-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 cursor-pointer transition-colors duration-200';
        
        if (result.type === 'author') {
            div.innerHTML = `
                <div class="flex items-center space-x-3 space-x-reverse">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 md:w-5 md:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xs md:text-sm font-medium text-gray-900 truncate">${result.name}</h3>
                        ${result.years ? `<p class="text-xs text-gray-500">${result.years}</p>` : ''}
                        ${result.madhhab ? `<p class="text-xs text-blue-600">${result.madhhab}</p>` : ''}
                        <p class="text-xs text-gray-400">${result.books_count} كتاب</p>
                    </div>
                </div>
            `;
            
            div.addEventListener('click', () => {
                window.location.href = `/authors/${result.id}/details`;
            });
        } else {
            div.innerHTML = `
                <div class="flex items-center space-x-3 space-x-reverse">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 md:w-5 md:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xs md:text-sm font-medium text-gray-900 truncate">${result.title}</h3>
                        ${result.authors_text ? `<p class="text-xs text-gray-500">بقلم: ${result.authors_text}</p>` : ''}
                        <p class="text-xs text-blue-600">${result.section.name}</p>
                        <div class="flex items-center space-x-2 space-x-reverse text-xs text-gray-400 mt-1">
                            ${result.pages_count ? `<span>${result.pages_count} صفحة</span>` : ''}
                            ${result.volumes_count > 1 ? `<span>• ${result.volumes_count} مجلد</span>` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            div.addEventListener('click', () => {
                window.location.href = `/books/${result.id}/details`;
            });
        }
        
        return div;
    }
    
    // إظهار القائمة المنسدلة
    function showDropdown() {
        searchResults.classList.remove('hidden');
        searchLoading.classList.add('hidden');
        searchNoResults.classList.add('hidden');
        searchDropdown.classList.remove('hidden');
    }
    
    // إخفاء القائمة المنسدلة
    function hideDropdown() {
        searchDropdown.classList.add('hidden');
    }
    
    // إظهار التحميل
    function showLoading() {
        searchResults.classList.add('hidden');
        searchLoading.classList.remove('hidden');
        searchNoResults.classList.add('hidden');
        searchDropdown.classList.remove('hidden');
    }
    
    // إخفاء التحميل
    function hideLoading() {
        searchLoading.classList.add('hidden');
    }
    
    // إظهار عدم وجود نتائج
    function showNoResults() {
        searchResults.classList.add('hidden');
        searchLoading.classList.add('hidden');
        searchNoResults.classList.remove('hidden');
        searchDropdown.classList.remove('hidden');
    }
});
</script>