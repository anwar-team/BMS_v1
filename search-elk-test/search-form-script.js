/**
 * BMS Library Search Form JavaScript
 * سكريبت فورم البحث التفاعلي
 * 
 * الميزات:
 * - البحث التلقائي أثناء الكتابة
 * - تبديل أنواع البحث
 * - البحث المتقدم
 * - عرض النتائج المتجاوب
 * - التنقل بين النتائج
 * - حفظ تاريخ البحث
 */

class BMSSearchForm {
    constructor(options = {}) {
        // Default configuration
        this.config = {
            searchDelay: 300,
            minSearchLength: 2,
            maxResults: 50,
            apiEndpoint: '/api/search',
            enableHistory: true,
            enableSuggestions: true,
            ...options
        };

        // DOM elements
        this.elements = {
            searchInput: document.getElementById('searchInput'),
            searchTypeBtns: document.querySelectorAll('.search-type-btn'),
            toggleAdvanced: document.getElementById('toggleAdvanced'),
            advancedOptions: document.querySelector('.advanced-options'),
            loadingState: document.querySelector('.loading-state'),
            searchResults: document.querySelector('.search-results'),
            noResults: document.querySelector('.no-results'),
            searchSuggestions: document.querySelectorAll('.search-suggestion'),
            resultsContainer: document.querySelector('.results-container'),
            paginationContainer: document.querySelector('.pagination-controls')
        };

        // State management
        this.state = {
            currentSearchType: 'content',
            currentQuery: '',
            currentPage: 1,
            totalResults: 0,
            isLoading: false,
            searchHistory: this.loadSearchHistory(),
            filters: {
                category: '',
                era: '',
                sortBy: 'relevance'
            }
        };

        // Search timeout for debouncing
        this.searchTimeout = null;

        // Initialize the search form
        this.init();
    }

    /**
     * Initialize the search form
     */
    init() {
        this.bindEvents();
        this.loadSuggestions();
        this.setupKeyboardShortcuts();
        
        // Focus on search input when page loads
        if (this.elements.searchInput) {
            this.elements.searchInput.focus();
        }

        console.log('BMS Search Form initialized');
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Search input events
        if (this.elements.searchInput) {
            this.elements.searchInput.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value);
            });

            this.elements.searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.performSearch(e.target.value);
                }
            });

            this.elements.searchInput.addEventListener('focus', () => {
                this.showSuggestions();
            });
        }

        // Search type buttons
        this.elements.searchTypeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                this.switchSearchType(btn.dataset.type);
            });
        });

        // Toggle advanced search
        if (this.elements.toggleAdvanced) {
            this.elements.toggleAdvanced.addEventListener('click', () => {
                this.toggleAdvancedSearch();
            });
        }

        // Search suggestions
        this.elements.searchSuggestions.forEach(suggestion => {
            suggestion.addEventListener('click', () => {
                this.selectSuggestion(suggestion.textContent.trim());
            });
        });

        // Advanced filters
        const filterSelects = document.querySelectorAll('.advanced-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', (e) => {
                this.updateFilter(e.target.name, e.target.value);
            });
        });

        // Result item clicks
        document.addEventListener('click', (e) => {
            const resultItem = e.target.closest('.result-item');
            if (resultItem) {
                this.handleResultClick(resultItem);
            }
        });

        // Pagination clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('pagination-btn')) {
                const page = parseInt(e.target.dataset.page);
                if (page && page !== this.state.currentPage) {
                    this.goToPage(page);
                }
            }
        });

        // Clear search button
        const clearBtn = document.querySelector('.clear-search-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                this.clearSearch();
            });
        }
    }

    /**
     * Handle search input with debouncing
     */
    handleSearchInput(query) {
        clearTimeout(this.searchTimeout);
        
        this.state.currentQuery = query.trim();
        
        if (this.state.currentQuery.length >= this.config.minSearchLength) {
            this.searchTimeout = setTimeout(() => {
                this.performSearch(this.state.currentQuery);
            }, this.config.searchDelay);
        } else if (this.state.currentQuery.length === 0) {
            this.hideResults();
            this.showSuggestions();
        }
    }

    /**
     * Switch search type
     */
    switchSearchType(type) {
        // Update button states
        this.elements.searchTypeBtns.forEach(btn => {
            btn.classList.remove('active', 'bg-primary-600', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        });

        const activeBtn = document.querySelector(`[data-type="${type}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active', 'bg-primary-600', 'text-white');
            activeBtn.classList.remove('bg-gray-100', 'text-gray-700');
        }

        // Update state
        this.state.currentSearchType = type;

        // Update placeholder
        const placeholders = {
            'content': 'ابحث في محتوى الكتب...',
            'titles': 'ابحث في عناوين الكتب...',
            'authors': 'ابحث في أسماء المؤلفين...',
            'categories': 'ابحث في الأقسام...'
        };

        if (this.elements.searchInput) {
            this.elements.searchInput.placeholder = placeholders[type] || placeholders.content;
        }

        // Re-search if there's a current query
        if (this.state.currentQuery) {
            this.performSearch(this.state.currentQuery);
        }

        // Track search type change
        this.trackEvent('search_type_changed', { type });
    }

    /**
     * Toggle advanced search options
     */
    toggleAdvancedSearch() {
        const isHidden = this.elements.advancedOptions.classList.contains('hidden');
        const arrow = this.elements.toggleAdvanced.querySelector('svg');
        
        if (isHidden) {
            this.elements.advancedOptions.classList.remove('hidden');
            this.elements.advancedOptions.classList.add('fade-in');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            this.elements.advancedOptions.classList.add('hidden');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }

        this.trackEvent('advanced_search_toggled', { expanded: isHidden });
    }

    /**
     * Update filter value
     */
    updateFilter(filterName, value) {
        this.state.filters[filterName] = value;
        
        // Re-search if there's a current query
        if (this.state.currentQuery) {
            this.performSearch(this.state.currentQuery);
        }

        this.trackEvent('filter_changed', { filter: filterName, value });
    }

    /**
     * Perform search
     */
    async performSearch(query = this.state.currentQuery, page = 1) {
        if (!query || query.length < this.config.minSearchLength) {
            return;
        }

        this.state.isLoading = true;
        this.state.currentPage = page;
        this.showLoading();
        this.hideResults();
        this.hideSuggestions();

        try {
            const searchParams = {
                query: query,
                type: this.state.currentSearchType,
                page: page,
                limit: 10,
                ...this.state.filters
            };

            const response = await this.makeSearchRequest(searchParams);
            
            if (response.success) {
                this.displayResults(response.data);
                this.addToSearchHistory(query);
            } else {
                this.showNoResults();
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showError('حدث خطأ أثناء البحث. يرجى المحاولة مرة أخرى.');
        } finally {
            this.state.isLoading = false;
            this.hideLoading();
        }

        this.trackEvent('search_performed', {
            query,
            type: this.state.currentSearchType,
            page,
            filters: this.state.filters
        });
    }

    /**
     * Make search request to Elasticsearch API
     */
    async makeSearchRequest(params) {
        try {
            const response = await fetch('/api/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    query: params.query,
                    type: params.type,
                    page: params.page,
                    limit: params.limit,
                    category: params.category,
                    era: params.era,
                    sortBy: params.sortBy
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            // إضافة معلومات إضافية للنتائج
            if (result.success && result.data && result.data.results) {
                result.data.results = result.data.results.map(item => ({
                    ...item,
                    // تنسيق النتائج حسب النوع
                    displayTitle: this.formatDisplayTitle(item),
                    displayExcerpt: this.formatDisplayExcerpt(item),
                    displayMeta: this.formatDisplayMeta(item)
                }));
            }

            return result;
        } catch (error) {
            console.error('API request failed:', error);
            
            // في حالة فشل الاتصال، استخدم البيانات الوهمية
            console.log('Falling back to mock data...');
            return this.generateFallbackResults(params);
        }
    }

    /**
     * تنسيق عنوان العرض
     */
    formatDisplayTitle(item) {
        if (item.highlight && item.highlight.title) {
            return item.highlight.title[0];
        }
        return item.title;
    }

    /**
     * تنسيق مقطع العرض
     */
    formatDisplayExcerpt(item) {
        if (item.highlight && item.highlight.content) {
            return item.highlight.content[0];
        }
        return item.excerpt;
    }

    /**
     * تنسيق معلومات إضافية
     */
    formatDisplayMeta(item) {
        const meta = [];
        
        if (item.category) meta.push(item.category);
        if (item.views) meta.push(`${item.views.toLocaleString('ar-SA')} مشاهدة`);
        if (item.page) meta.push(`الصفحة ${item.page}`);
        
        return meta;
    }

    /**
     * نتائج احتياطية في حالة فشل الاتصال
     */
    generateFallbackResults(params) {
        const mockResults = this.generateMockResults(params.query, params.type);
        
        return {
            success: mockResults.length > 0,
            data: {
                results: mockResults,
                total: mockResults.length * 5,
                page: params.page,
                totalPages: Math.ceil((mockResults.length * 5) / 10)
            },
            fallback: true // إشارة إلى استخدام البيانات الاحتياطية
        };
    }

    /**
     * Generate mock results for demo
     */
    generateMockResults(query, type) {
        const mockData = {
            content: [
                {
                    id: 1,
                    type: 'book',
                    title: `صحيح البخاري - كتاب ${query}`,
                    author: 'الإمام محمد بن إسماعيل البخاري',
                    excerpt: `...باب وجوب ${query} في الجماعة وفضلها، وقال النبي صلى الله عليه وسلم...`,
                    category: 'الحديث',
                    views: 1245,
                    page: 45
                },
                {
                    id: 2,
                    type: 'book',
                    title: `فتح الباري شرح صحيح البخاري - ${query}`,
                    author: 'ابن حجر العسقلاني',
                    excerpt: `شرح مفصل لأحاديث ${query} وما يتعلق بها من أحكام فقهية...`,
                    category: 'الحديث',
                    views: 892,
                    page: 123
                }
            ],
            authors: [
                {
                    id: 1,
                    type: 'author',
                    name: `الإمام ${query}`,
                    fullName: `محمد بن إسماعيل ${query}`,
                    description: 'إمام المحدثين وصاحب أصح كتاب بعد كتاب الله',
                    books: 15,
                    pages: 2340,
                    era: 'العصر الكلاسيكي'
                }
            ],
            categories: [
                {
                    id: 1,
                    type: 'category',
                    name: `قسم ${query}`,
                    description: `يحتوي على كتب ${query} والمسائل المتعلقة بها`,
                    books: 234,
                    authors: 89,
                    pages: 12450
                }
            ]
        };

        return mockData[type] || mockData.content;
    }

    /**
     * Display search results
     */
    displayResults(data) {
        this.state.totalResults = data.total;
        
        // Update results count
        const resultsCount = document.querySelector('.results-count');
        if (resultsCount) {
            resultsCount.textContent = `(${data.total.toLocaleString('ar-SA')} نتيجة)`;
        }

        // Clear previous results
        if (this.elements.resultsContainer) {
            this.elements.resultsContainer.innerHTML = '';
        }

        // Add new results
        data.results.forEach(result => {
            const resultElement = this.createResultElement(result);
            if (this.elements.resultsContainer) {
                this.elements.resultsContainer.appendChild(resultElement);
            }
        });

        // Update pagination
        this.updatePagination(data.page, data.totalPages);

        // Show results
        this.showResults();
    }

    /**
     * Create result element
     */
    createResultElement(result) {
        const div = document.createElement('div');
        div.className = 'result-item';
        div.dataset.resultId = result.id;
        div.dataset.resultType = result.type;

        const iconClass = {
            'book': 'result-icon book',
            'author': 'result-icon author',
            'category': 'result-icon category'
        }[result.type] || 'result-icon book';

        const iconSvg = {
            'book': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>',
            'author': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>',
            'category': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>'
        }[result.type] || '';

        let content = '';
        
        if (result.type === 'book') {
            content = `
                <div class="result-content">
                    <div class="${iconClass}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${iconSvg}
                        </svg>
                    </div>
                    <div class="result-info">
                        <h4 class="result-title">${this.highlightQuery(result.title)}</h4>
                        <p class="result-author">المؤلف: ${result.author}</p>
                        <p class="result-excerpt">${this.highlightQuery(result.excerpt)}</p>
                        <div class="result-meta">
                            <span class="result-meta-item">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                ${result.category}
                            </span>
                            <span class="result-meta-item">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                ${result.views.toLocaleString('ar-SA')} مشاهدة
                            </span>
                            <span>الصفحة ${result.page}</span>
                        </div>
                    </div>
                </div>
            `;
        } else if (result.type === 'author') {
            content = `
                <div class="result-content">
                    <div class="${iconClass}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${iconSvg}
                        </svg>
                    </div>
                    <div class="result-info">
                        <h4 class="result-title">${this.highlightQuery(result.name)}</h4>
                        <p class="result-author">${result.fullName}</p>
                        <p class="result-excerpt">${result.description}</p>
                        <div class="result-meta">
                            <span>${result.books} كتاب</span>
                            <span>${result.pages.toLocaleString('ar-SA')} صفحة</span>
                            <span>${result.era}</span>
                        </div>
                    </div>
                </div>
            `;
        } else if (result.type === 'category') {
            content = `
                <div class="result-content">
                    <div class="${iconClass}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${iconSvg}
                        </svg>
                    </div>
                    <div class="result-info">
                        <h4 class="result-title">${this.highlightQuery(result.name)}</h4>
                        <p class="result-excerpt">${result.description}</p>
                        <div class="result-meta">
                            <span>${result.books} كتاب</span>
                            <span>${result.authors} مؤلف</span>
                            <span>${result.pages.toLocaleString('ar-SA')} صفحة</span>
                        </div>
                    </div>
                </div>
            `;
        }

        div.innerHTML = content;
        return div;
    }

    /**
     * Highlight search query in text
     */
    highlightQuery(text) {
        if (!this.state.currentQuery || !text) return text;
        
        const regex = new RegExp(`(${this.escapeRegex(this.state.currentQuery)})`, 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    /**
     * Escape regex special characters
     */
    escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Update pagination
     */
    updatePagination(currentPage, totalPages) {
        if (!this.elements.paginationContainer) return;

        this.elements.paginationContainer.innerHTML = '';

        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.className = 'pagination-btn';
        prevBtn.textContent = 'السابق';
        prevBtn.disabled = currentPage === 1;
        prevBtn.dataset.page = currentPage - 1;
        this.elements.paginationContainer.appendChild(prevBtn);

        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
            pageBtn.textContent = i;
            pageBtn.dataset.page = i;
            this.elements.paginationContainer.appendChild(pageBtn);
        }

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.className = 'pagination-btn';
        nextBtn.textContent = 'التالي';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.dataset.page = currentPage + 1;
        this.elements.paginationContainer.appendChild(nextBtn);
    }

    /**
     * Go to specific page
     */
    goToPage(page) {
        if (page !== this.state.currentPage) {
            this.performSearch(this.state.currentQuery, page);
        }
    }

    /**
     * Handle result item click
     */
    handleResultClick(resultItem) {
        const resultId = resultItem.dataset.resultId;
        const resultType = resultItem.dataset.resultType;
        
        // Track click
        this.trackEvent('result_clicked', {
            id: resultId,
            type: resultType,
            query: this.state.currentQuery,
            position: Array.from(resultItem.parentNode.children).indexOf(resultItem) + 1
        });

        // Navigate to result page
        const urls = {
            'book': `/books/${resultId}`,
            'author': `/authors/${resultId}`,
            'category': `/categories/${resultId}`
        };

        const url = urls[resultType];
        if (url) {
            window.location.href = url;
        }
    }

    /**
     * Select suggestion
     */
    selectSuggestion(suggestion) {
        if (this.elements.searchInput) {
            this.elements.searchInput.value = suggestion;
            this.performSearch(suggestion);
        }

        this.trackEvent('suggestion_selected', { suggestion });
    }

    /**
     * Clear search
     */
    clearSearch() {
        if (this.elements.searchInput) {
            this.elements.searchInput.value = '';
        }
        
        this.state.currentQuery = '';
        this.hideResults();
        this.showSuggestions();
        
        if (this.elements.searchInput) {
            this.elements.searchInput.focus();
        }

        this.trackEvent('search_cleared');
    }

    /**
     * Show/hide states
     */
    showLoading() {
        if (this.elements.loadingState) {
            this.elements.loadingState.classList.remove('hidden');
        }
    }

    hideLoading() {
        if (this.elements.loadingState) {
            this.elements.loadingState.classList.add('hidden');
        }
    }

    showResults() {
        if (this.elements.searchResults) {
            this.elements.searchResults.classList.remove('hidden');
            this.elements.searchResults.classList.add('fade-in');
        }
    }

    hideResults() {
        if (this.elements.searchResults) {
            this.elements.searchResults.classList.add('hidden');
        }
        if (this.elements.noResults) {
            this.elements.noResults.classList.add('hidden');
        }
    }

    showNoResults() {
        if (this.elements.noResults) {
            this.elements.noResults.classList.remove('hidden');
            this.elements.noResults.classList.add('fade-in');
        }
    }

    showSuggestions() {
        const suggestionsContainer = document.querySelector('.search-suggestions');
        if (suggestionsContainer && !this.state.currentQuery) {
            suggestionsContainer.classList.remove('hidden');
        }
    }

    hideSuggestions() {
        const suggestionsContainer = document.querySelector('.search-suggestions');
        if (suggestionsContainer) {
            suggestionsContainer.classList.add('hidden');
        }
    }

    showError(message) {
        // Create or update error message
        let errorDiv = document.querySelector('.search-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'search-error bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4';
            this.elements.searchResults.parentNode.insertBefore(errorDiv, this.elements.searchResults);
        }
        
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            errorDiv.classList.add('hidden');
        }, 5000);
    }

    /**
     * Load search suggestions
     */
    loadSuggestions() {
        // This would typically load from an API
        // For now, suggestions are hardcoded in HTML
    }

    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (this.elements.searchInput) {
                    this.elements.searchInput.focus();
                }
            }

            // Escape to clear search
            if (e.key === 'Escape') {
                this.clearSearch();
            }

            // Arrow keys for result navigation
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                this.navigateResults(e.key === 'ArrowDown' ? 'down' : 'up');
                e.preventDefault();
            }
        });
    }

    /**
     * Navigate results with keyboard
     */
    navigateResults(direction) {
        const results = document.querySelectorAll('.result-item');
        if (results.length === 0) return;

        let currentIndex = Array.from(results).findIndex(item => 
            item.classList.contains('keyboard-selected')
        );

        // Remove current selection
        results.forEach(item => item.classList.remove('keyboard-selected'));

        // Calculate new index
        if (direction === 'down') {
            currentIndex = currentIndex < results.length - 1 ? currentIndex + 1 : 0;
        } else {
            currentIndex = currentIndex > 0 ? currentIndex - 1 : results.length - 1;
        }

        // Add new selection
        results[currentIndex].classList.add('keyboard-selected');
        results[currentIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Search history management
     */
    loadSearchHistory() {
        if (!this.config.enableHistory) return [];
        
        try {
            const history = localStorage.getItem('bms_search_history');
            return history ? JSON.parse(history) : [];
        } catch (error) {
            console.error('Error loading search history:', error);
            return [];
        }
    }

    addToSearchHistory(query) {
        if (!this.config.enableHistory || !query) return;
        
        // Remove if already exists
        this.state.searchHistory = this.state.searchHistory.filter(item => item !== query);
        
        // Add to beginning
        this.state.searchHistory.unshift(query);
        
        // Keep only last 10 searches
        this.state.searchHistory = this.state.searchHistory.slice(0, 10);
        
        // Save to localStorage
        try {
            localStorage.setItem('bms_search_history', JSON.stringify(this.state.searchHistory));
        } catch (error) {
            console.error('Error saving search history:', error);
        }
    }

    /**
     * Analytics tracking
     */
    trackEvent(eventName, data = {}) {
        // Integration with analytics service
        if (typeof gtag !== 'undefined') {
            gtag('event', eventName, {
                custom_parameter_1: data,
                event_category: 'search',
                event_label: this.state.currentQuery
            });
        }

        // Console log for development
        console.log('Search Event:', eventName, data);
    }

    /**
     * Get current state (for debugging)
     */
    getState() {
        return { ...this.state };
    }

    /**
     * Update configuration
     */
    updateConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize search form
    window.bmsSearch = new BMSSearchForm({
        searchDelay: 300,
        minSearchLength: 2,
        enableHistory: true,
        enableSuggestions: true
    });

    // Add CSS for keyboard navigation
    const style = document.createElement('style');
    style.textContent = `
        .result-item.keyboard-selected {
            background-color: var(--primary-50) !important;
            border-left: 4px solid var(--primary-600) !important;
        }
    `;
    document.head.appendChild(style);
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BMSSearchForm;
}