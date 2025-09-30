/**
 * Elasticsearch Configuration for BMS Library
 * إعدادات Elasticsearch لمكتبة BMS
 * 
 * هذا الملف يحتوي على إعدادات الاتصال مع Elasticsearch المحلي
 * والوظائف المطلوبة للبحث والفهرسة
 */

const { Client } = require('@elastic/elasticsearch');

// إعدادات Elasticsearch
const ELASTICSEARCH_CONFIG = {
    // عنوان Elasticsearch المحلي (Docker)
    node: 'http://localhost:9200',
    
    // إعدادات الاتصال
    requestTimeout: 30000,
    pingTimeout: 3000,
    
    // إعدادات الأمان (مطلوبة لأن ELK stack لديك مفعل الأمان)
    auth: {
        username: 'elastic',
        password: 'bms2025' // كلمة المرور المحدثة
    },
    
    // تجاهل شهادات SSL في البيئة التطويرية
    ssl: {
        rejectUnauthorized: false
    }
};

// إعدادات بديلة بدون أمان (في حالة تعطيل الأمان)
const ELASTICSEARCH_CONFIG_NO_AUTH = {
    node: 'http://localhost:9200',
    requestTimeout: 30000,
    pingTimeout: 3000,
    ssl: {
        rejectUnauthorized: false
    }
};

// إنشاء عميل Elasticsearch
const client = new Client(ELASTICSEARCH_CONFIG);

// أسماء الفهارس
const INDICES = {
    BOOKS: 'bms_books',
    AUTHORS: 'bms_authors',
    PAGES: 'book-pages-2025.09', // الفهرس الأكبر مع 13000 وثيقة
    CATEGORIES: 'bms_categories',
    MAIN_PAGES: 'book-pages-2025.09' // الفهرس الرئيسي للبحث
};

/**
 * فئة إدارة Elasticsearch
 */
class ElasticsearchManager {
    constructor() {
        this.client = client;
        this.indices = INDICES;
    }

    /**
     * فحص الاتصال مع Elasticsearch
     */
    async checkConnection() {
        try {
            const response = await this.client.ping();
            console.log('✅ Elasticsearch connection successful');
            return true;
        } catch (error) {
            console.error('❌ Elasticsearch connection failed:', error.message);
            return false;
        }
    }

    /**
     * إنشاء الفهارس المطلوبة
     */
    async createIndices() {
        try {
            // فهرس الكتب
            await this.createBooksIndex();
            
            // فهرس المؤلفين
            await this.createAuthorsIndex();
            
            // فهرس الصفحات
            await this.createPagesIndex();
            
            // فهرس الأقسام
            await this.createCategoriesIndex();
            
            console.log('✅ All indices created successfully');
        } catch (error) {
            console.error('❌ Error creating indices:', error);
        }
    }

    /**
     * إنشاء فهرس الكتب
     */
    async createBooksIndex() {
        const indexExists = await this.client.indices.exists({ index: this.indices.BOOKS });
        
        if (!indexExists) {
            await this.client.indices.create({
                index: this.indices.BOOKS,
                body: {
                    settings: {
                        number_of_shards: 1,
                        number_of_replicas: 0,
                        analysis: {
                            analyzer: {
                                arabic_analyzer: {
                                    type: 'custom',
                                    tokenizer: 'standard',
                                    filter: ['lowercase', 'arabic_normalization', 'arabic_stem']
                                }
                            }
                        }
                    },
                    mappings: {
                        properties: {
                            id: { type: 'integer' },
                            title: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer',
                                fields: {
                                    keyword: { type: 'keyword' }
                                }
                            },
                            author_id: { type: 'integer' },
                            author_name: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer'
                            },
                            category_id: { type: 'integer' },
                            category_name: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer'
                            },
                            description: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer'
                            },
                            content: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer'
                            },
                            pages_count: { type: 'integer' },
                            views_count: { type: 'integer' },
                            created_at: { type: 'date' },
                            updated_at: { type: 'date' }
                        }
                    }
                }
            });
            console.log('📚 Books index created');
        }
    }

    /**
     * إنشاء فهرس المؤلفين
     */
    async createAuthorsIndex() {
        const indexExists = await this.client.indices.exists({ index: this.indices.AUTHORS });
        
        if (!indexExists) {
            await this.client.indices.create({
                index: this.indices.AUTHORS,
                body: {
                    settings: {
                        number_of_shards: 1,
                        number_of_replicas: 0,
                        analysis: {
                            analyzer: {
                                arabic_analyzer: {
                                    type: 'custom',
                                    tokenizer: 'standard',
                                    filter: ['lowercase', 'arabic_normalization', 'arabic_stem']
                                }
                            }
                        }
                    },
                    mappings: {
                        properties: {
                            id: { type: 'integer' },
                            name: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer',
                                fields: {
                                    keyword: { type: 'keyword' }
                                }
                            },
                            full_name: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer'
                            },
                            biography: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer'
                            },
                            birth_year: { type: 'integer' },
                            death_year: { type: 'integer' },
                            books_count: { type: 'integer' },
                            created_at: { type: 'date' },
                            updated_at: { type: 'date' }
                        }
                    }
                }
            });
            console.log('👤 Authors index created');
        }
    }

    /**
     * إنشاء فهرس الصفحات
     */
    async createPagesIndex() {
        const indexExists = await this.client.indices.exists({ index: this.indices.PAGES });
        
        if (!indexExists) {
            await this.client.indices.create({
                index: this.indices.PAGES,
                body: {
                    settings: {
                        number_of_shards: 1,
                        number_of_replicas: 0,
                        analysis: {
                            analyzer: {
                                arabic_analyzer: {
                                    type: 'custom',
                                    tokenizer: 'standard',
                                    filter: ['lowercase', 'arabic_normalization', 'arabic_stem']
                                }
                            }
                        }
                    },
                    mappings: {
                        properties: {
                            id: { type: 'integer' },
                            book_id: { type: 'integer' },
                            book_title: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer'
                            },
                            page_number: { type: 'integer' },
                            content: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer'
                            },
                            volume_id: { type: 'integer' },
                            created_at: { type: 'date' },
                            updated_at: { type: 'date' }
                        }
                    }
                }
            });
            console.log('📄 Pages index created');
        }
    }

    /**
     * إنشاء فهرس الأقسام
     */
    async createCategoriesIndex() {
        const indexExists = await this.client.indices.exists({ index: this.indices.CATEGORIES });
        
        if (!indexExists) {
            await this.client.indices.create({
                index: this.indices.CATEGORIES,
                body: {
                    settings: {
                        number_of_shards: 1,
                        number_of_replicas: 0,
                        analysis: {
                            analyzer: {
                                arabic_analyzer: {
                                    type: 'custom',
                                    tokenizer: 'standard',
                                    filter: ['lowercase', 'arabic_normalization', 'arabic_stem']
                                }
                            }
                        }
                    },
                    mappings: {
                        properties: {
                            id: { type: 'integer' },
                            name: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer',
                                fields: {
                                    keyword: { type: 'keyword' }
                                }
                            },
                            description: { 
                                type: 'text',
                                analyzer: 'arabic_analyzer'
                            },
                            books_count: { type: 'integer' },
                            parent_id: { type: 'integer' },
                            created_at: { type: 'date' },
                            updated_at: { type: 'date' }
                        }
                    }
                }
            });
            console.log('📂 Categories index created');
        }
    }

    /**
     * البحث في المحتوى
     */
    async searchContent(query, options = {}) {
        const {
            type = 'content',
            page = 1,
            limit = 10,
            filters = {}
        } = options;

        try {
            let searchBody = {};
            let indices = [];

            // تحديد نوع البحث والفهارس - استخدام الفهرس الأكبر أولاً
            switch (type) {
                case 'content':
                    indices = [this.indices.MAIN_PAGES, this.indices.BOOKS]; // البحث في الفهرس الأكبر أولاً
                    searchBody = this.buildContentQuery(query, filters);
                    break;
                case 'titles':
                    indices = [this.indices.MAIN_PAGES, this.indices.BOOKS]; // البحث في العناوين من الفهرس الأكبر
                    searchBody = this.buildTitlesQuery(query, filters);
                    break;
                case 'authors':
                    indices = [this.indices.MAIN_PAGES, this.indices.AUTHORS]; // البحث في المؤلفين من الفهرس الأكبر
                    searchBody = this.buildAuthorsQuery(query, filters);
                    break;
                case 'categories':
                    indices = [this.indices.MAIN_PAGES, this.indices.CATEGORIES]; // البحث في الأقسام من الفهرس الأكبر
                    searchBody = this.buildCategoriesQuery(query, filters);
                    break;
                default:
                    indices = [this.indices.MAIN_PAGES]; // استخدام الفهرس الأكبر كافتراضي
                    searchBody = this.buildMultiQuery(query, filters);
            }

            const response = await this.client.search({
                index: indices.join(','),
                body: {
                    ...searchBody,
                    from: (page - 1) * limit,
                    size: limit,
                    highlight: {
                        fields: {
                            title: {},
                            content: {},
                            name: {},
                            description: {}
                        },
                        pre_tags: ['<mark>'],
                        post_tags: ['</mark>']
                    }
                }
            });

            return this.formatSearchResults(response);
        } catch (error) {
            console.error('Search error:', error);
            throw error;
        }
    }

    /**
     * بناء استعلام البحث في المحتوى
     */
    buildContentQuery(query, filters) {
        // استخدام wildcard للبحث في النصوص العربية المُرمزة
        const should = [
            {
                wildcard: {
                    'content': `*${query}*`
                }
            },
            {
                wildcard: {
                    'search_text': `*${query}*`
                }
            },
            {
                wildcard: {
                    'book_title': `*${query}*`
                }
            },
            {
                wildcard: {
                    'document_title': `*${query}*`
                }
            },
            {
                wildcard: {
                    'author_name': `*${query}*`
                }
            }
        ];

        const must = [
            {
                bool: {
                    should: should,
                    minimum_should_match: 1
                }
            }
        ];

        // إضافة الفلاتر
        if (filters.category) {
            must.push({
                term: { 'book_id': filters.category }
            });
        }

        if (filters.author) {
            must.push({
                wildcard: { 'author_name': `*${filters.author}*` }
            });
        }

        return {
            query: {
                bool: { must }
            },
            sort: this.buildSortOptions(filters.sortBy),
            highlight: {
                fields: {
                    'content': {},
                    'search_text': {},
                    'book_title': {},
                    'document_title': {}
                }
            }
        };
    }

    /**
     * بناء استعلام البحث في العناوين
     */
    buildTitlesQuery(query, filters) {
        return {
            query: {
                bool: {
                    must: [
                        {
                            match: {
                                title: {
                                    query: query,
                                    fuzziness: 'AUTO',
                                    boost: 2
                                }
                            }
                        }
                    ]
                }
            },
            sort: this.buildSortOptions(filters.sortBy)
        };
    }

    /**
     * بناء استعلام البحث في المؤلفين
     */
    buildAuthorsQuery(query, filters) {
        return {
            query: {
                bool: {
                    must: [
                        {
                            multi_match: {
                                query: query,
                                fields: ['name^2', 'full_name', 'biography'],
                                fuzziness: 'AUTO'
                            }
                        }
                    ]
                }
            },
            sort: this.buildSortOptions(filters.sortBy)
        };
    }

    /**
     * بناء استعلام البحث في الأقسام
     */
    buildCategoriesQuery(query, filters) {
        return {
            query: {
                bool: {
                    must: [
                        {
                            multi_match: {
                                query: query,
                                fields: ['name^2', 'description'],
                                fuzziness: 'AUTO'
                            }
                        }
                    ]
                }
            },
            sort: this.buildSortOptions(filters.sortBy)
        };
    }

    /**
     * بناء استعلام البحث المتعدد
     */
    buildMultiQuery(query, filters) {
        return {
            query: {
                bool: {
                    should: [
                        {
                            multi_match: {
                                query: query,
                                fields: ['title^3', 'content^2'],
                                type: 'best_fields'
                            }
                        },
                        {
                            multi_match: {
                                query: query,
                                fields: ['name^2', 'full_name'],
                                type: 'best_fields'
                            }
                        }
                    ],
                    minimum_should_match: 1
                }
            },
            sort: this.buildSortOptions(filters.sortBy)
        };
    }

    /**
     * بناء خيارات الترتيب
     */
    buildSortOptions(sortBy) {
        switch (sortBy) {
            case 'date':
                return [{ 'created_at': { order: 'desc' } }];
            case 'title':
                return [{ 'title.keyword': { order: 'asc' } }];
            case 'views':
                return [{ 'views_count': { order: 'desc' } }];
            case 'relevance':
            default:
                return ['_score'];
        }
    }

    /**
     * تنسيق نتائج البحث
     */
    formatSearchResults(response) {
        // التعامل مع الاستجابة سواء كانت تحتوي على body أم لا
        const responseData = response.body || response;
        const hits = responseData.hits;
        
        if (!hits) {
            return {
                success: false,
                data: {
                    results: [],
                    total: 0,
                    page: 1,
                    totalPages: 0
                }
            };
        }
        
        return {
            success: true,
            data: {
                results: hits.hits.map(hit => ({
                    id: hit._source.page_id || hit._source.id,
                    type: this.getDocumentType(hit._index),
                    title: this.decodeArabicText(hit._source.book_title || hit._source.document_title || hit._source.title),
                    displayTitle: this.decodeArabicText(hit._source.book_title || hit._source.document_title || hit._source.title),
                    author: this.decodeArabicText(hit._source.author_name || 'مؤلف غير معروف'),
                    excerpt: this.extractExcerpt(hit),
                    displayExcerpt: this.extractExcerpt(hit),
                    category: this.decodeArabicText(hit._source.category_name || 'قسم غير محدد'),
                    views: hit._source.views_count || 0,
                    page: hit._source.page_number,
                    bookId: hit._source.book_id,
                    highlight: hit.highlight,
                    score: hit._score
                })),
                total: hits.total.value || hits.total,
                page: Math.floor((responseData.from || 0) / (responseData.size || 10)) + 1,
                totalPages: Math.ceil((hits.total.value || hits.total) / (responseData.size || 10))
            }
        };
    }
    
    /**
     * فك ترميز النص العربي المُرمز بـ UTF-8
     */
    decodeArabicText(text) {
        if (!text) return '';
        
        try {
            // محاولة فك الترميز إذا كان النص مُرمز
            if (text.includes('\u')) {
                return JSON.parse('"' + text + '"');
            }
            return text;
        } catch (error) {
            // إذا فشل فك الترميز، إرجاع النص كما هو
            return text;
        }
    }

    /**
     * تحديد نوع الوثيقة
     */
    getDocumentType(index) {
        if (index.includes('books')) return 'book';
        if (index.includes('authors')) return 'author';
        if (index.includes('pages')) return 'page';
        if (index.includes('categories')) return 'category';
        return 'unknown';
    }

    /**
     * استخراج مقطع من النص
     */
    extractExcerpt(hit) {
        if (hit.highlight) {
            // إذا كان هناك تمييز، استخدمه
            const highlighted = Object.values(hit.highlight)[0];
            if (highlighted && highlighted[0]) {
                return this.decodeArabicText(highlighted[0]);
            }
        }
        
        // إذا لم يكن هناك تمييز، استخرج من المحتوى
        const content = hit._source.content || hit._source.search_text || hit._source.description || hit._source.biography;
        const decodedContent = this.decodeArabicText(content || '');
        
        if (decodedContent.length > 200) {
            return decodedContent.substring(0, 200) + '...';
        }
        
        return decodedContent;
    }

    /**
     * الحصول على اقتراحات البحث
     */
    async getSuggestions(query, limit = 5) {
        try {
            const response = await this.client.search({
                index: [this.indices.BOOKS, this.indices.AUTHORS].join(','),
                body: {
                    suggest: {
                        title_suggest: {
                            prefix: query,
                            completion: {
                                field: 'title.suggest',
                                size: limit
                            }
                        },
                        author_suggest: {
                            prefix: query,
                            completion: {
                                field: 'name.suggest',
                                size: limit
                            }
                        }
                    }
                }
            });

            const suggestions = [];
            
            // استخراج اقتراحات العناوين
            if (response.body.suggest.title_suggest) {
                response.body.suggest.title_suggest.forEach(suggest => {
                    suggest.options.forEach(option => {
                        suggestions.push({
                            text: option.text,
                            type: 'title'
                        });
                    });
                });
            }

            // استخراج اقتراحات المؤلفين
            if (response.body.suggest.author_suggest) {
                response.body.suggest.author_suggest.forEach(suggest => {
                    suggest.options.forEach(option => {
                        suggestions.push({
                            text: option.text,
                            type: 'author'
                        });
                    });
                });
            }

            return suggestions;
        } catch (error) {
            console.error('Suggestions error:', error);
            return [];
        }
    }
}

// تصدير الفئة والإعدادات
module.exports = {
    ElasticsearchManager,
    ELASTICSEARCH_CONFIG,
    INDICES,
    client
};

// مثال على الاستخدام
if (require.main === module) {
    async function testConnection() {
        const esManager = new ElasticsearchManager();
        
        // فحص الاتصال
        const isConnected = await esManager.checkConnection();
        
        if (isConnected) {
            // إنشاء الفهارس
            await esManager.createIndices();
            
            // تجربة البحث
            try {
                const results = await esManager.searchContent('الصلاة', {
                    type: 'content',
                    page: 1,
                    limit: 5
                });
                
                console.log('Search results:', JSON.stringify(results, null, 2));
            } catch (error) {
                console.log('Search test skipped (no data indexed yet)');
            }
        }
    }
    
    testConnection().catch(console.error);
}