-- =====================================================
-- سكريبت التحسينات السريعة لقاعدة البيانات
-- BMS - نظام إدارة المكتبة
-- التاريخ: 4 أكتوبر 2025
-- =====================================================

-- تنبيه: قم بعمل نسخة احتياطية قبل تنفيذ هذا السكريبت!
-- mysqldump -u username -p bms > backup_before_optimization_$(date +%Y%m%d).sql

-- =====================================================
-- الجزء 1: إضافة الفهارس للبحث السريع
-- =====================================================

-- 1.1 فهارس البحث النصي الكامل (FULLTEXT)
-- هذه الفهارس تحسن البحث في المحتوى النصي بشكل كبير

-- فهرس البحث في محتوى الصفحات
ALTER TABLE pages ADD FULLTEXT INDEX ft_content (content);

-- فهرس البحث في المحتوى مع الجزء
ALTER TABLE pages ADD FULLTEXT INDEX ft_content_part (content, part);

-- فهرس البحث في عناوين ووصف الكتب
ALTER TABLE books ADD FULLTEXT INDEX ft_title_desc (title, description);

-- فهرس البحث في أسماء وسير المؤلفين
ALTER TABLE authors ADD FULLTEXT INDEX ft_author_bio (full_name, biography);

-- فهرس البحث في عناوين ووصف الفصول
ALTER TABLE chapters ADD FULLTEXT INDEX ft_chapter_title_desc (title, description);

-- 1.2 فهارس مركبة محسنة للاستعلامات المتكررة

-- فهرس محسن للبحث في صفحات كتاب معين
CREATE INDEX idx_pages_book_page_word ON pages(book_id, page_number, word_count);

-- فهرس للبحث حسب طول المحتوى
CREATE INDEX idx_pages_content_length ON pages((CHAR_LENGTH(content)));

-- فهرس لتواريخ الإنشاء والتحديث
CREATE INDEX idx_pages_timestamps ON pages(created_at, updated_at);

-- فهرس محسن للكتب حسب الحالة والرؤية
CREATE INDEX idx_books_status_visibility ON books(status, visibility, created_at);

-- فهرس لترتيب الكتب حسب عدد الصفحات
CREATE INDEX idx_books_pages_count ON books(pages_count DESC);

-- فهرس للبحث حسب الناشر والحالة
CREATE INDEX idx_books_publisher_status ON books(publisher_id, status);

-- فهرس للبحث في الفصول حسب الكتاب والترتيب
CREATE INDEX idx_chapters_book_level_order ON chapters(book_id, level, `order`);

-- فهرس للبحث في المؤلفين حسب المذهب
CREATE INDEX idx_authors_madhhab ON authors(madhhab);

-- فهرس لتواريخ ميلاد ووفاة المؤلفين
CREATE INDEX idx_authors_dates ON authors(birth_date, death_date);

-- =====================================================
-- الجزء 2: تنظيف وتصحيح البيانات
-- =====================================================

-- 2.1 تصحيح الصفحات الفارغة
UPDATE pages 
SET content = '[محتوى غير متوفر]' 
WHERE content IS NULL OR TRIM(content) = '';

-- 2.2 تحديث عدد الكلمات للصفحات
UPDATE pages 
SET word_count = (
    LENGTH(TRIM(content)) - LENGTH(REPLACE(TRIM(content), ' ', '')) + 1
)
WHERE word_count IS NULL 
   OR word_count = 0 
   OR content IS NOT NULL;

-- 2.3 تصحيح عدد الصفحات في جدول الكتب
UPDATE books b 
SET pages_count = (
    SELECT COUNT(*) 
    FROM pages p 
    WHERE p.book_id = b.id
);

-- 2.4 تصحيح عدد المجلدات في جدول الكتب
UPDATE books b 
SET volumes_count = (
    SELECT COUNT(*) 
    FROM volumes v 
    WHERE v.book_id = b.id
);

-- 2.5 تحديث نطاق الصفحات في الفصول
UPDATE chapters c
SET page_start = (
    SELECT MIN(page_number)
    FROM pages p
    WHERE p.chapter_id = c.id
),
page_end = (
    SELECT MAX(page_number)
    FROM pages p
    WHERE p.chapter_id = c.id
)
WHERE EXISTS (
    SELECT 1 FROM pages p WHERE p.chapter_id = c.id
);

-- =====================================================
-- الجزء 3: إنشاء جداول التحليلات (اختياري)
-- =====================================================

-- 3.1 جدول إحصائيات البحث
CREATE TABLE IF NOT EXISTS search_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    search_term VARCHAR(500) NOT NULL,
    results_count INT UNSIGNED DEFAULT 0,
    search_time_ms INT UNSIGNED DEFAULT 0,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    search_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_search_term (search_term(191)),
    INDEX idx_search_date (search_date),
    INDEX idx_user_id (user_id),
    INDEX idx_results_count (results_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.2 جدول الكلمات الأكثر بحثاً
CREATE TABLE IF NOT EXISTS popular_search_terms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    term VARCHAR(191) UNIQUE,
    search_count INT UNSIGNED DEFAULT 1,
    last_searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_search_count (search_count DESC),
    INDEX idx_last_searched (last_searched_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.3 جدول إحصائيات الكتب
CREATE TABLE IF NOT EXISTS book_statistics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    book_id BIGINT UNSIGNED NOT NULL,
    views_count INT UNSIGNED DEFAULT 0,
    searches_count INT UNSIGNED DEFAULT 0,
    last_accessed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE INDEX idx_book_id (book_id),
    INDEX idx_views (views_count DESC),
    INDEX idx_searches (searches_count DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- الجزء 4: تحسينات الأداء على مستوى قاعدة البيانات
-- =====================================================

-- 4.1 تحسين إعدادات InnoDB (تنفذ كـ SET GLOBAL أو في my.cnf)
-- هذه التعليقات للمرجع فقط - يجب تطبيقها في ملف الإعدادات

-- innodb_buffer_pool_size = 2G (أو 70% من الرام المتاح)
-- innodb_log_file_size = 512M
-- innodb_flush_log_at_trx_commit = 2
-- innodb_flush_method = O_DIRECT
-- innodb_file_per_table = 1

-- 4.2 تحليل وتحسين الجداول
ANALYZE TABLE books;
ANALYZE TABLE pages;
ANALYZE TABLE chapters;
ANALYZE TABLE authors;
ANALYZE TABLE volumes;

-- 4.3 إعادة بناء الجداول لتحسين الأداء (اختياري - يستغرق وقتاً)
-- OPTIMIZE TABLE pages;
-- OPTIMIZE TABLE books;
-- OPTIMIZE TABLE chapters;

-- =====================================================
-- الجزء 5: إنشاء Views للاستعلامات المتكررة
-- =====================================================

-- 5.1 View لإحصائيات الكتب الشاملة
CREATE OR REPLACE VIEW v_books_stats AS
SELECT 
    b.id,
    b.title,
    b.status,
    b.visibility,
    b.pages_count,
    b.volumes_count,
    COUNT(DISTINCT p.id) as actual_pages_count,
    COUNT(DISTINCT v.id) as actual_volumes_count,
    COUNT(DISTINCT c.id) as chapters_count,
    COUNT(DISTINCT ab.author_id) as authors_count,
    SUM(p.word_count) as total_words,
    AVG(p.word_count) as avg_words_per_page,
    b.created_at,
    b.updated_at
FROM books b
LEFT JOIN pages p ON p.book_id = b.id
LEFT JOIN volumes v ON v.book_id = b.id
LEFT JOIN chapters c ON c.book_id = b.id
LEFT JOIN author_book ab ON ab.book_id = b.id
GROUP BY b.id;

-- 5.2 View لإحصائيات المؤلفين
CREATE OR REPLACE VIEW v_authors_stats AS
SELECT 
    a.id,
    a.full_name,
    a.madhhab,
    a.birth_date,
    a.death_date,
    COUNT(DISTINCT ab.book_id) as books_count,
    SUM(b.pages_count) as total_pages,
    GROUP_CONCAT(DISTINCT b.title ORDER BY b.title SEPARATOR ' | ') as books_titles
FROM authors a
LEFT JOIN author_book ab ON ab.author_id = a.id
LEFT JOIN books b ON b.id = ab.book_id
GROUP BY a.id;

-- 5.3 View للصفحات مع معلومات الكتاب والمؤلف
CREATE OR REPLACE VIEW v_pages_with_book_info AS
SELECT 
    p.id,
    p.page_number,
    p.word_count,
    p.content,
    b.id as book_id,
    b.title as book_title,
    b.status as book_status,
    GROUP_CONCAT(DISTINCT a.full_name ORDER BY a.full_name SEPARATOR ', ') as authors,
    c.title as chapter_title,
    v.volume_number,
    p.created_at,
    p.updated_at
FROM pages p
INNER JOIN books b ON p.book_id = b.id
LEFT JOIN author_book ab ON ab.book_id = b.id
LEFT JOIN authors a ON a.id = ab.author_id
LEFT JOIN chapters c ON c.id = p.chapter_id
LEFT JOIN volumes v ON v.id = p.volume_id
GROUP BY p.id;

-- 5.4 View لإحصائيات البحث اليومية
CREATE OR REPLACE VIEW v_daily_search_stats AS
SELECT 
    DATE(search_date) as search_day,
    COUNT(*) as total_searches,
    COUNT(DISTINCT search_term) as unique_terms,
    AVG(search_time_ms) as avg_response_time_ms,
    AVG(results_count) as avg_results_count,
    MAX(search_time_ms) as max_response_time_ms
FROM search_analytics
GROUP BY DATE(search_date)
ORDER BY search_day DESC;

-- =====================================================
-- الجزء 6: Stored Procedures للعمليات المتكررة
-- =====================================================

-- 6.1 إجراء لتحديث إحصائيات كتاب معين
DELIMITER //
CREATE PROCEDURE sp_update_book_stats(IN book_id_param BIGINT)
BEGIN
    UPDATE books 
    SET 
        pages_count = (SELECT COUNT(*) FROM pages WHERE book_id = book_id_param),
        volumes_count = (SELECT COUNT(*) FROM volumes WHERE book_id = book_id_param)
    WHERE id = book_id_param;
END //
DELIMITER ;

-- 6.2 إجراء لتحديث إحصائيات جميع الكتب
DELIMITER //
CREATE PROCEDURE sp_update_all_books_stats()
BEGIN
    UPDATE books b 
    SET 
        pages_count = (SELECT COUNT(*) FROM pages p WHERE p.book_id = b.id),
        volumes_count = (SELECT COUNT(*) FROM volumes v WHERE v.book_id = b.id);
END //
DELIMITER ;

-- 6.3 إجراء لتنظيف البيانات القديمة من search_analytics
DELIMITER //
CREATE PROCEDURE sp_cleanup_old_analytics(IN days_to_keep INT)
BEGIN
    DELETE FROM search_analytics 
    WHERE search_date < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
END //
DELIMITER ;

-- =====================================================
-- الجزء 7: Triggers للحفاظ على سلامة البيانات
-- =====================================================

-- 7.1 Trigger لتحديث إحصائيات الكتاب عند إضافة صفحة
DELIMITER //
CREATE TRIGGER tr_pages_after_insert
AFTER INSERT ON pages
FOR EACH ROW
BEGIN
    UPDATE books 
    SET pages_count = pages_count + 1
    WHERE id = NEW.book_id;
END //
DELIMITER ;

-- 7.2 Trigger لتحديث إحصائيات الكتاب عند حذف صفحة
DELIMITER //
CREATE TRIGGER tr_pages_after_delete
AFTER DELETE ON pages
FOR EACH ROW
BEGIN
    UPDATE books 
    SET pages_count = GREATEST(0, pages_count - 1)
    WHERE id = OLD.book_id;
END //
DELIMITER ;

-- 7.3 Trigger لتحديث word_count تلقائياً عند الإدراج
DELIMITER //
CREATE TRIGGER tr_pages_before_insert_word_count
BEFORE INSERT ON pages
FOR EACH ROW
BEGIN
    IF NEW.content IS NOT NULL AND NEW.content != '' THEN
        SET NEW.word_count = LENGTH(TRIM(NEW.content)) - LENGTH(REPLACE(TRIM(NEW.content), ' ', '')) + 1;
    END IF;
END //
DELIMITER ;

-- 7.4 Trigger لتحديث word_count تلقائياً عند التحديث
DELIMITER //
CREATE TRIGGER tr_pages_before_update_word_count
BEFORE UPDATE ON pages
FOR EACH ROW
BEGIN
    IF NEW.content IS NOT NULL AND NEW.content != '' THEN
        SET NEW.word_count = LENGTH(TRIM(NEW.content)) - LENGTH(REPLACE(TRIM(NEW.content), ' ', '')) + 1;
    END IF;
END //
DELIMITER ;

-- =====================================================
-- الجزء 8: استعلامات التحقق من التحسينات
-- =====================================================

-- 8.1 التحقق من الفهارس المضافة
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    INDEX_TYPE,
    COLUMN_NAME
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN ('pages', 'books', 'chapters', 'authors')
ORDER BY TABLE_NAME, INDEX_NAME;

-- 8.2 التحقق من أحجام الجداول بعد التحسين
SELECT 
    table_name,
    ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb,
    ROUND(data_length / 1024 / 1024, 2) AS data_mb,
    ROUND(index_length / 1024 / 1024, 2) AS index_mb,
    table_rows
FROM information_schema.TABLES
WHERE table_schema = DATABASE()
AND table_name IN ('books', 'pages', 'chapters', 'authors', 'volumes')
ORDER BY (data_length + index_length) DESC;

-- 8.3 اختبار سرعة البحث FULLTEXT
-- مثال: البحث عن كلمة في المحتوى
SELECT COUNT(*) as results,
       BENCHMARK(1000, (
           SELECT COUNT(*) 
           FROM pages 
           WHERE MATCH(content) AGAINST('العلم' IN NATURAL LANGUAGE MODE)
       )) as benchmark_time
FROM pages 
WHERE MATCH(content) AGAINST('العلم' IN NATURAL LANGUAGE MODE);

-- =====================================================
-- ملاحظات مهمة:
-- =====================================================
-- 1. قم بعمل نسخة احتياطية كاملة قبل التنفيذ
-- 2. نفذ هذا السكريبت في وقت قليل الاستخدام
-- 3. بعض العمليات قد تستغرق وقتاً طويلاً على قواعد البيانات الكبيرة
-- 4. راقب استهلاك الموارد أثناء التنفيذ
-- 5. اختبر على بيئة التطوير أولاً
-- 
-- للتنفيذ:
-- mysql -u username -p database_name < quick_optimizations.sql
-- 
-- أو من داخل MySQL:
-- source /path/to/quick_optimizations.sql;
-- =====================================================
