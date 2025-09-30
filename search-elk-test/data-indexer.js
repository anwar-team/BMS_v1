/**
 * BMS Data Indexer for Elasticsearch
 * مفهرس البيانات من قاعدة البيانات إلى Elasticsearch
 */

const mysql = require('mysql2/promise');
const { ElasticsearchManager, INDICES } = require('./elasticsearch-config');
require('dotenv').config();

// إعدادات قاعدة البيانات
const DB_CONFIG = {
    host: process.env.DB_HOST || '145.223.98.97',
    port: process.env.DB_PORT || 3306,
    user: process.env.DB_USERNAME || 'bms',
    password: process.env.DB_PASSWORD || 'bms2025',
    database: process.env.DB_DATABASE || 'bms',
    charset: 'utf8mb4'
};

class DataIndexer {
    constructor() {
        this.esManager = new ElasticsearchManager();
        this.dbConnection = null;
        this.batchSize = 100; // حجم الدفعة للفهرسة
    }

    /**
     * الاتصال بقاعدة البيانات
     */
    async connectToDatabase() {
        try {
            this.dbConnection = await mysql.createConnection(DB_CONFIG);
            console.log('✅ Connected to MySQL database');
            return true;
        } catch (error) {
            console.error('❌ Database connection failed:', error.message);
            return false;
        }
    }

    /**
     * فهرسة جميع البيانات
     */
    async indexAllData() {
        try {
            console.log('🚀 Starting data indexing process...');
            
            // التحقق من الاتصالات
            const dbConnected = await this.connectToDatabase();
            const esConnected = await this.esManager.checkConnection();
            
            if (!dbConnected || !esConnected) {
                throw new Error('Failed to connect to database or Elasticsearch');
            }

            // إنشاء الفهارس
            await this.esManager.createIndices();

            // فهرسة البيانات
            await this.indexBooks();
            await this.indexAuthors();
            await this.indexPages();
            await this.indexCategories();

            console.log('✅ Data indexing completed successfully!');
            
        } catch (error) {
            console.error('❌ Data indexing failed:', error);
        } finally {
            if (this.dbConnection) {
                await this.dbConnection.end();
                console.log('🔌 Database connection closed');
            }
        }
    }

    /**
     * فهرسة الكتب
     */
    async indexBooks() {
        try {
            console.log('📚 Indexing books...');
            
            // استعلام الكتب (مبسط)
            const [books] = await this.dbConnection.execute(`
                SELECT 
                    id,
                    title,
                    description,
                    book_section_id,
                    publisher_id,
                    pages_count,
                    author_death_year,
                    author_role,
                    created_at,
                    updated_at
                FROM books
                WHERE status = 'published'
                ORDER BY id
                LIMIT 50
            `);

            console.log(`Found ${books.length} books to index`);

            // فهرسة الكتب في دفعات
            for (let i = 0; i < books.length; i += this.batchSize) {
                const batch = books.slice(i, i + this.batchSize);
                await this.indexBooksBatch(batch);
                
                const progress = Math.min(i + this.batchSize, books.length);
                console.log(`📚 Indexed ${progress}/${books.length} books`);
            }

            console.log('✅ Books indexing completed');
            
        } catch (error) {
            console.error('❌ Books indexing failed:', error);
        }
    }

    /**
     * فهرسة دفعة من الكتب
     */
    async indexBooksBatch(books) {
        const body = [];
        
        for (const book of books) {
            // إضافة معلومات الفهرسة
            body.push({
                index: {
                    _index: INDICES.BOOKS,
                    _id: book.id
                }
            });
            
            // إضافة بيانات الكتاب
            body.push({
                id: book.id,
                title: book.title,
                description: book.description || '',
                author_id: book.publisher_id || null,
                author_name: book.author_role || 'مؤلف غير محدد',
                category_id: book.book_section_id || null,
                category_name: 'قسم غير محدد',
                pages_count: book.pages_count || 0,
                views_count: 0,
                author_death_year: book.author_death_year,
                created_at: book.created_at,
                updated_at: book.updated_at
            });
        }

        if (body.length > 0) {
            await this.esManager.client.bulk({ body });
        }
    }

    /**
     * فهرسة المؤلفين
     */
    async indexAuthors() {
        try {
            console.log('👤 Indexing authors...');
            
            const [authors] = await this.dbConnection.execute(`
                SELECT 
                    a.id,
                    a.name,
                    a.full_name,
                    a.biography,
                    a.birth_year,
                    a.death_year,
                    a.created_at,
                    a.updated_at,
                    COUNT(b.id) as books_count
                FROM authors a
                LEFT JOIN books b ON a.id = b.author_id AND b.deleted_at IS NULL
                WHERE a.deleted_at IS NULL
                GROUP BY a.id
                ORDER BY a.id
            `);

            console.log(`Found ${authors.length} authors to index`);

            for (let i = 0; i < authors.length; i += this.batchSize) {
                const batch = authors.slice(i, i + this.batchSize);
                await this.indexAuthorsBatch(batch);
                
                const progress = Math.min(i + this.batchSize, authors.length);
                console.log(`👤 Indexed ${progress}/${authors.length} authors`);
            }

            console.log('✅ Authors indexing completed');
            
        } catch (error) {
            console.error('❌ Authors indexing failed:', error);
        }
    }

    /**
     * فهرسة دفعة من المؤلفين
     */
    async indexAuthorsBatch(authors) {
        const body = [];
        
        for (const author of authors) {
            body.push({
                index: {
                    _index: INDICES.AUTHORS,
                    _id: author.id
                }
            });
            
            body.push({
                id: author.id,
                name: author.name,
                full_name: author.full_name,
                biography: author.biography,
                birth_year: author.birth_year,
                death_year: author.death_year,
                books_count: author.books_count,
                created_at: author.created_at,
                updated_at: author.updated_at
            });
        }

        if (body.length > 0) {
            await this.esManager.client.bulk({ body });
        }
    }

    /**
     * فهرسة الصفحات
     */
    async indexPages() {
        try {
            console.log('📄 Indexing pages...');
            
            const [pages] = await this.dbConnection.execute(`
                SELECT 
                    p.id,
                    p.book_id,
                    p.page_number,
                    p.content,
                    p.volume_id,
                    p.created_at,
                    p.updated_at,
                    b.title as book_title
                FROM pages p
                LEFT JOIN books b ON p.book_id = b.id
                WHERE p.deleted_at IS NULL 
                AND b.deleted_at IS NULL
                AND p.content IS NOT NULL 
                AND p.content != ''
                ORDER BY p.id
                LIMIT 10000
            `);

            console.log(`Found ${pages.length} pages to index`);

            for (let i = 0; i < pages.length; i += this.batchSize) {
                const batch = pages.slice(i, i + this.batchSize);
                await this.indexPagesBatch(batch);
                
                const progress = Math.min(i + this.batchSize, pages.length);
                console.log(`📄 Indexed ${progress}/${pages.length} pages`);
            }

            console.log('✅ Pages indexing completed');
            
        } catch (error) {
            console.error('❌ Pages indexing failed:', error);
        }
    }

    /**
     * فهرسة دفعة من الصفحات
     */
    async indexPagesBatch(pages) {
        const body = [];
        
        for (const page of pages) {
            body.push({
                index: {
                    _index: INDICES.PAGES,
                    _id: page.id
                }
            });
            
            body.push({
                id: page.id,
                book_id: page.book_id,
                book_title: page.book_title,
                page_number: page.page_number,
                content: page.content,
                volume_id: page.volume_id,
                created_at: page.created_at,
                updated_at: page.updated_at
            });
        }

        if (body.length > 0) {
            await this.esManager.client.bulk({ body });
        }
    }

    /**
     * فهرسة الأقسام
     */
    async indexCategories() {
        try {
            console.log('📂 Indexing categories...');
            
            const [categories] = await this.dbConnection.execute(`
                SELECT 
                    c.id,
                    c.name,
                    c.description,
                    c.parent_id,
                    c.created_at,
                    c.updated_at,
                    COUNT(b.id) as books_count
                FROM categories c
                LEFT JOIN books b ON c.id = b.category_id AND b.deleted_at IS NULL
                WHERE c.deleted_at IS NULL
                GROUP BY c.id
                ORDER BY c.id
            `);

            console.log(`Found ${categories.length} categories to index`);

            for (let i = 0; i < categories.length; i += this.batchSize) {
                const batch = categories.slice(i, i + this.batchSize);
                await this.indexCategoriesBatch(batch);
                
                const progress = Math.min(i + this.batchSize, categories.length);
                console.log(`📂 Indexed ${progress}/${categories.length} categories`);
            }

            console.log('✅ Categories indexing completed');
            
        } catch (error) {
            console.error('❌ Categories indexing failed:', error);
        }
    }

    /**
     * فهرسة دفعة من الأقسام
     */
    async indexCategoriesBatch(categories) {
        const body = [];
        
        for (const category of categories) {
            body.push({
                index: {
                    _index: INDICES.CATEGORIES,
                    _id: category.id
                }
            });
            
            body.push({
                id: category.id,
                name: category.name,
                description: category.description,
                parent_id: category.parent_id,
                books_count: category.books_count,
                created_at: category.created_at,
                updated_at: category.updated_at
            });
        }

        if (body.length > 0) {
            await this.esManager.client.bulk({ body });
        }
    }

    /**
     * حذف جميع الفهارس
     */
    async deleteAllIndices() {
        try {
            console.log('🗑️  Deleting all indices...');
            
            for (const indexName of Object.values(INDICES)) {
                try {
                    const exists = await this.esManager.client.indices.exists({ index: indexName });
                    if (exists) {
                        await this.esManager.client.indices.delete({ index: indexName });
                        console.log(`✅ Deleted index: ${indexName}`);
                    }
                } catch (error) {
                    console.log(`⚠️  Index ${indexName} not found or already deleted`);
                }
            }
            
            console.log('✅ All indices deleted');
        } catch (error) {
            console.error('❌ Error deleting indices:', error);
        }
    }

    /**
     * إعادة فهرسة جميع البيانات
     */
    async reindexAllData() {
        console.log('🔄 Starting complete reindexing...');
        
        // حذف الفهارس الموجودة
        await this.deleteAllIndices();
        
        // إعادة فهرسة البيانات
        await this.indexAllData();
        
        console.log('✅ Complete reindexing finished!');
    }

    /**
     * عرض إحصائيات الفهارس
     */
    async showIndexStats() {
        try {
            console.log('📊 Index Statistics:');
            console.log('==================');
            
            for (const [name, indexName] of Object.entries(INDICES)) {
                try {
                    const stats = await this.esManager.client.count({ index: indexName });
                    console.log(`${name}: ${stats.body.count.toLocaleString()} documents`);
                } catch (error) {
                    console.log(`${name}: Index not found`);
                }
            }
            
            console.log('==================');
        } catch (error) {
            console.error('❌ Error getting index stats:', error);
        }
    }
}

// تشغيل المفهرس إذا تم استدعاؤه مباشرة
if (require.main === module) {
    const indexer = new DataIndexer();
    
    // معالجة معاملات سطر الأوامر
    const args = process.argv.slice(2);
    const command = args[0] || 'index';
    
    async function runCommand() {
        switch (command) {
            case 'index':
                await indexer.indexAllData();
                break;
            case 'reindex':
                await indexer.reindexAllData();
                break;
            case 'delete':
                await indexer.deleteAllIndices();
                break;
            case 'stats':
                await indexer.showIndexStats();
                break;
            case 'books':
                await indexer.connectToDatabase();
                await indexer.esManager.checkConnection();
                await indexer.esManager.createIndices();
                await indexer.indexBooks();
                break;
            case 'authors':
                await indexer.connectToDatabase();
                await indexer.esManager.checkConnection();
                await indexer.esManager.createIndices();
                await indexer.indexAuthors();
                break;
            case 'pages':
                await indexer.connectToDatabase();
                await indexer.esManager.checkConnection();
                await indexer.esManager.createIndices();
                await indexer.indexPages();
                break;
            case 'categories':
                await indexer.connectToDatabase();
                await indexer.esManager.checkConnection();
                await indexer.esManager.createIndices();
                await indexer.indexCategories();
                break;
            default:
                console.log('Available commands:');
                console.log('  index     - Index all data');
                console.log('  reindex   - Delete and reindex all data');
                console.log('  delete    - Delete all indices');
                console.log('  stats     - Show index statistics');
                console.log('  books     - Index only books');
                console.log('  authors   - Index only authors');
                console.log('  pages     - Index only pages');
                console.log('  categories - Index only categories');
        }
    }
    
    runCommand().catch(console.error);
}

module.exports = DataIndexer;