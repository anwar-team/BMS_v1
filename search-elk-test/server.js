/**
 * BMS Library Elasticsearch API Server
 * خادم API لربط مكتبة BMS مع Elasticsearch
 */

const express = require('express');
const cors = require('cors');
const path = require('path');
require('dotenv').config();

const { ElasticsearchManager } = require('./elasticsearch-config');

const app = express();
const PORT = process.env.PORT || 3001;

// إعداد الوسطاء (Middleware)
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname)));

// إنشاء مدير Elasticsearch
const esManager = new ElasticsearchManager();

// متغير لحفظ حالة الاتصال
let isElasticsearchConnected = false;

/**
 * فحص الاتصال مع Elasticsearch عند بدء الخادم
 */
async function initializeElasticsearch() {
    try {
        isElasticsearchConnected = await esManager.checkConnection();
        
        if (isElasticsearchConnected) {
            console.log('🚀 Elasticsearch connected successfully');
            await esManager.createIndices();
            console.log('📚 Indices setup completed');
        } else {
            console.log('⚠️  Elasticsearch not available - using mock data');
        }
    } catch (error) {
        console.error('❌ Elasticsearch initialization failed:', error.message);
        isElasticsearchConnected = false;
    }
}

/**
 * الصفحة الرئيسية - عرض واجهة البحث
 */
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'search-form-concept.html'));
});

/**
 * API endpoint للبحث
 */
app.post('/api/search', async (req, res) => {
    try {
        const {
            query,
            type = 'content',
            page = 1,
            limit = 10,
            category = '',
            era = '',
            sortBy = 'relevance'
        } = req.body;

        // التحقق من وجود استعلام البحث
        if (!query || query.trim().length < 2) {
            return res.status(400).json({
                success: false,
                error: 'يجب أن يكون استعلام البحث أطول من حرفين'
            });
        }

        let results;

        if (isElasticsearchConnected) {
            // البحث باستخدام Elasticsearch
            try {
                results = await esManager.searchContent(query.trim(), {
                    type,
                    page: parseInt(page),
                    limit: parseInt(limit),
                    filters: {
                        category,
                        era,
                        sortBy
                    }
                });
            } catch (error) {
                console.error('Elasticsearch search failed, falling back to mock data:', error.message);
                results = generateMockResults(query, type, page, limit);
                results.source = 'fallback';
                results.message = 'تم استخدام البيانات الاحتياطية بسبب خطأ في البحث';
            }
        } else {
            // البحث باستخدام البيانات الوهمية
            results = generateMockResults(query, type, page, limit);
            results.source = 'mock';
            results.message = 'Elasticsearch غير متصل - يتم استخدام بيانات تجريبية';
        }

        res.json(results);

    } catch (error) {
        console.error('Search API error:', error);
        res.status(500).json({
            success: false,
            error: 'حدث خطأ أثناء البحث. يرجى المحاولة مرة أخرى.'
        });
    }
});

/**
 * API endpoint للحصول على اقتراحات البحث
 */
app.get('/api/suggestions', async (req, res) => {
    try {
        const { q: query, limit = 5 } = req.query;

        if (!query || query.trim().length < 2) {
            return res.json({ suggestions: [] });
        }

        let suggestions = [];

        if (isElasticsearchConnected) {
            suggestions = await esManager.getSuggestions(query.trim(), parseInt(limit));
        } else {
            // اقتراحات وهمية
            suggestions = generateMockSuggestions(query);
        }

        res.json({ suggestions });

    } catch (error) {
        console.error('Suggestions API error:', error);
        res.json({ suggestions: [] });
    }
});

/**
 * API endpoint لفحص حالة Elasticsearch
 */
app.get('/api/status', async (req, res) => {
    try {
        const isConnected = await esManager.checkConnection();
        
        res.json({
            elasticsearch: {
                connected: isConnected,
                status: isConnected ? 'healthy' : 'disconnected'
            },
            server: {
                status: 'running',
                timestamp: new Date().toISOString()
            }
        });
    } catch (error) {
        res.json({
            elasticsearch: {
                connected: false,
                status: 'error',
                error: error.message
            },
            server: {
                status: 'running',
                timestamp: new Date().toISOString()
            }
        });
    }
});

/**
 * API endpoint لإعادة تهيئة Elasticsearch
 */
app.post('/api/reinitialize', async (req, res) => {
    try {
        await initializeElasticsearch();
        
        res.json({
            success: true,
            message: 'تم إعادة تهيئة Elasticsearch بنجاح',
            connected: isElasticsearchConnected
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: 'فشل في إعادة تهيئة Elasticsearch',
            details: error.message
        });
    }
});

/**
 * توليد نتائج وهمية للاختبار
 */
function generateMockResults(query, type, page = 1, limit = 10) {
    const mockData = {
        content: [
            {
                id: 1,
                type: 'book',
                title: `صحيح البخاري - كتاب ${query}`,
                author: 'الإمام محمد بن إسماعيل البخاري',
                excerpt: `...باب وجوب ${query} في الجماعة وفضلها، وقال النبي صلى الله عليه وسلم: "${query} في الجماعة تفضل صلاة الفذ بسبع وعشرين درجة"...`,
                category: 'الحديث',
                views: 1245,
                page: 45,
                highlight: {
                    content: [`...باب وجوب <mark>${query}</mark> في الجماعة وفضلها...`]
                }
            },
            {
                id: 2,
                type: 'book',
                title: `فتح الباري شرح صحيح البخاري - ${query}`,
                author: 'ابن حجر العسقلاني',
                excerpt: `شرح مفصل لأحاديث ${query} وما يتعلق بها من أحكام فقهية وآداب شرعية...`,
                category: 'الحديث',
                views: 892,
                page: 123,
                highlight: {
                    title: [`فتح الباري شرح صحيح البخاري - <mark>${query}</mark>`]
                }
            },
            {
                id: 3,
                type: 'book',
                title: `المغني في فقه ${query}`,
                author: 'ابن قدامة المقدسي',
                excerpt: `كتاب شامل في فقه ${query} وأحكامها المختلفة حسب المذاهب الفقهية...`,
                category: 'الفقه',
                views: 567,
                page: 78,
                highlight: {
                    content: [`كتاب شامل في فقه <mark>${query}</mark> وأحكامها المختلفة...`]
                }
            }
        ],
        authors: [
            {
                id: 1,
                type: 'author',
                title: `الإمام ${query}`,
                author: `محمد بن إسماعيل ${query}`,
                excerpt: 'إمام المحدثين وصاحب أصح كتاب بعد كتاب الله، ولد سنة 194 هـ وتوفي سنة 256 هـ',
                category: 'علماء الحديث',
                views: 2340,
                page: null,
                highlight: {
                    name: [`الإمام <mark>${query}</mark>`]
                }
            }
        ],
        categories: [
            {
                id: 1,
                type: 'category',
                title: `قسم ${query}`,
                author: null,
                excerpt: `يحتوي على كتب ${query} والمسائل المتعلقة بها في جميع أبواب الفقه`,
                category: 'الأقسام الرئيسية',
                views: 12450,
                page: null,
                highlight: {
                    name: [`قسم <mark>${query}</mark>`]
                }
            }
        ]
    };

    const results = mockData[type] || mockData.content;
    const total = results.length * 8; // محاكاة وجود نتائج أكثر
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    const paginatedResults = results.slice(startIndex, endIndex);

    return {
        success: true,
        data: {
            results: paginatedResults,
            total: total,
            page: parseInt(page),
            totalPages: Math.ceil(total / limit)
        },
        source: 'mock' // للإشارة إلى أن البيانات وهمية
    };
}

/**
 * توليد اقتراحات وهمية
 */
function generateMockSuggestions(query) {
    const suggestions = [
        { text: `${query} في الجماعة`, type: 'title' },
        { text: `أحكام ${query}`, type: 'title' },
        { text: `فقه ${query}`, type: 'title' },
        { text: `${query} والطهارة`, type: 'title' },
        { text: `الإمام ${query}`, type: 'author' }
    ];

    return suggestions.filter(s => 
        s.text.toLowerCase().includes(query.toLowerCase())
    ).slice(0, 5);
}

/**
 * معالج الأخطاء العام
 */
app.use((error, req, res, next) => {
    console.error('Server error:', error);
    res.status(500).json({
        success: false,
        error: 'حدث خطأ في الخادم'
    });
});

/**
 * معالج الصفحات غير الموجودة
 */
app.use((req, res) => {
    res.status(404).json({
        success: false,
        error: 'الصفحة غير موجودة'
    });
});

/**
 * بدء الخادم
 */
async function startServer() {
    try {
        // تهيئة Elasticsearch
        await initializeElasticsearch();
        
        // بدء الخادم
        app.listen(PORT, () => {
            console.log(`\n🚀 BMS Search Server is running!`);
            console.log(`📍 Local: http://localhost:${PORT}`);
            console.log(`🔍 Search API: http://localhost:${PORT}/api/search`);
            console.log(`💡 Suggestions API: http://localhost:${PORT}/api/suggestions`);
            console.log(`📊 Status API: http://localhost:${PORT}/api/status`);
            console.log(`\n${isElasticsearchConnected ? '✅' : '⚠️'} Elasticsearch: ${isElasticsearchConnected ? 'Connected' : 'Using Mock Data'}`);
            console.log(`\n🎯 Ready to search!\n`);
        });
    } catch (error) {
        console.error('❌ Failed to start server:', error);
        process.exit(1);
    }
}

// بدء الخادم
startServer();

// معالجة إيقاف الخادم بشكل صحيح
process.on('SIGINT', async () => {
    console.log('\n🛑 Shutting down server...');
    
    try {
        if (esManager && esManager.client) {
            await esManager.client.close();
            console.log('✅ Elasticsearch connection closed');
        }
    } catch (error) {
        console.error('❌ Error closing Elasticsearch connection:', error);
    }
    
    console.log('👋 Server stopped');
    process.exit(0);
});