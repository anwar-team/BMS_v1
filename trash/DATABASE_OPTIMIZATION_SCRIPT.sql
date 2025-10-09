-- =====================================================
-- BMS Database Optimization SQL Script
-- تاريخ الإنشاء: 10 سبتمبر 2025
-- الهدف: تحسين أداء قاعدة البيانات وإنشاء فهارس للبحث السريع
-- =====================================================

-- =====================================================
-- 1. تحسينات الفهارس الفورية
-- =====================================================

-- إنشاء فهارس البحث النصي (FULLTEXT) للبحث السريع
ALTER TABLE pages ADD FULLTEXT ft_content (content);
ALTER TABLE pages ADD FULLTEXT ft_content_part (content, part);
ALTER TABLE books ADD FULLTEXT ft_title_desc (title, description);
ALTER TABLE authors ADD FULLTEXT ft_author_bio (full_name, biography);
ALTER TABLE chapters ADD FULLTEXT ft_chapter_title_desc (title, description);

-- فهارس مُحسنة للصفحات
CREATE INDEX idx_pages_book_page_word ON pages(book_id, page_number, word_count);
CREATE INDEX idx_pages_content_length ON pages(CHAR_LENGTH(content));
CREATE INDEX idx_pages_created_updated ON pages(created_at, updated_at);

-- فهارس مُحسنة للكتب
CREATE INDEX idx_books_status_visibility_date ON books(status, visibility, created_at);
CREATE INDEX idx_books_pages_count ON books(pages_count DESC);
CREATE INDEX idx_books_publisher_status ON books(publisher_id, status);

-- فهارس مُحسنة للمؤلفين
CREATE INDEX idx_authors_madhhab ON authors(madhhab);
CREATE INDEX idx_authors_dates ON authors(birth_date, death_date);

-- فهارس مُحسنة للفصول
CREATE INDEX idx_chapters_book_level_order_opt ON chapters(book_id, level, `order`) 
INCLUDE (title, page_start, page_end);

-- =====================================================
-- 2. إنشاء جداول الفهرس المقلوب (Inverted Index)
-- =====================================================

-- جدول مخزن الكلمات والمصطلحات
CREATE TABLE search_terms (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    term VARCHAR(100) NOT NULL COMMENT 'الكلمة الأصلية',
    normalized_term VARCHAR(100) NOT NULL COMMENT 'الكلمة بعد التطبيع',
    root_term VARCHAR(50) NULL COMMENT 'جذر الكلمة',
    term_type ENUM('word', 'phrase', 'root', 'synonym') DEFAULT 'word',
    frequency INT UNSIGNED DEFAULT 0 COMMENT 'تكرار الكلمة في كامل المجموعة',
    document_frequency INT UNSIGNED DEFAULT 0 COMMENT 'عدد الوثائق التي تحتوي الكلمة',
    idf_score DECIMAL(10,6) DEFAULT 0 COMMENT 'Inverse Document Frequency',
    language CHAR(2) DEFAULT 'ar' COMMENT 'لغة المصطلح',
    is_stop_word BOOLEAN DEFAULT FALSE COMMENT 'كلمة وقف',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_term_lang (term, language),
    INDEX idx_normalized (normalized_term),
    INDEX idx_root (root_term),
    INDEX idx_frequency (frequency DESC),
    INDEX idx_document_freq (document_frequency DESC),
    INDEX idx_idf_score (idf_score DESC),
    FULLTEXT ft_terms (term, normalized_term, root_term)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ربط الكلمات بالصفحات مع درجات الصلة
CREATE TABLE term_page_occurrences (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    term_id BIGINT NOT NULL,
    page_id BIGINT NOT NULL,
    book_id BIGINT NOT NULL,
    chapter_id BIGINT NULL,
    volume_id BIGINT NULL,
    
    occurrences_count SMALLINT UNSIGNED DEFAULT 1 COMMENT 'عدد مرات الظهور',
    positions JSON COMMENT 'مواضع الكلمة في الصفحة',
    tf_score DECIMAL(8,6) DEFAULT 0 COMMENT 'Term Frequency',
    tfidf_score DECIMAL(8,6) DEFAULT 0 COMMENT 'TF-IDF Score',
    
    context_before VARCHAR(200) COMMENT 'السياق قبل الكلمة',
    context_after VARCHAR(200) COMMENT 'السياق بعد الكلمة',
    highlighted_snippet TEXT COMMENT 'المقطع المُمييز',
    
    relevance_boost DECIMAL(3,2) DEFAULT 1.0 COMMENT 'معامل تعزيز الصلة',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (term_id) REFERENCES search_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE SET NULL,
    FOREIGN KEY (volume_id) REFERENCES volumes(id) ON DELETE SET NULL,
    
    UNIQUE INDEX idx_term_page (term_id, page_id),
    INDEX idx_page_terms (page_id, tfidf_score DESC),
    INDEX idx_book_terms (book_id, term_id),
    INDEX idx_chapter_terms (chapter_id, term_id),
    INDEX idx_tf_score (tf_score DESC),
    INDEX idx_tfidf_score (tfidf_score DESC),
    INDEX idx_occurrences (occurrences_count DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول المرادفات والكلمات ذات الصلة
CREATE TABLE term_synonyms (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    primary_term_id BIGINT NOT NULL,
    synonym_term_id BIGINT NOT NULL,
    similarity_score DECIMAL(4,3) DEFAULT 1.0 COMMENT 'درجة التشابه 0-1',
    relationship_type ENUM('synonym', 'related', 'variant', 'root') DEFAULT 'synonym',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (primary_term_id) REFERENCES search_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (synonym_term_id) REFERENCES search_terms(id) ON DELETE CASCADE,
    
    UNIQUE INDEX idx_term_synonym (primary_term_id, synonym_term_id),
    INDEX idx_similarity (similarity_score DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول إحصائيات البحث والتحليلات
CREATE TABLE search_analytics (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    search_query TEXT NOT NULL COMMENT 'الاستعلام الأصلي',
    normalized_query TEXT COMMENT 'الاستعلام بعد التطبيع',
    query_hash CHAR(32) NOT NULL COMMENT 'MD5 hash للاستعلام',
    
    search_type ENUM('simple', 'advanced', 'phrase', 'boolean') DEFAULT 'simple',
    results_count INT UNSIGNED DEFAULT 0,
    execution_time_ms INT UNSIGNED DEFAULT 0,
    
    filters_applied JSON COMMENT 'الفلاتر المطبقة',
    clicked_results JSON COMMENT 'النتائج التي تم النقر عليها',
    user_rating TINYINT COMMENT 'تقييم المستخدم للنتائج 1-5',
    
    user_id CHAR(36) NULL,
    user_session VARCHAR(100),
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    search_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_query_hash (query_hash),
    INDEX idx_search_type (search_type),
    INDEX idx_timestamp (search_timestamp),
    INDEX idx_user_id (user_id),
    INDEX idx_execution_time (execution_time_ms),
    FULLTEXT ft_search_query (search_query)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول التوصيات والاقتراحات
CREATE TABLE search_suggestions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    suggestion_text VARCHAR(200) NOT NULL,
    original_query VARCHAR(200) NOT NULL,
    suggestion_type ENUM('correction', 'completion', 'related') DEFAULT 'completion',
    frequency INT UNSIGNED DEFAULT 1,
    success_rate DECIMAL(4,3) DEFAULT 0,
    language CHAR(2) DEFAULT 'ar',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_suggestion_text (suggestion_text),
    INDEX idx_original_query (original_query),
    INDEX idx_type_frequency (suggestion_type, frequency DESC),
    INDEX idx_success_rate (success_rate DESC),
    FULLTEXT ft_suggestions (suggestion_text, original_query)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. جداول التحسين والكاش
-- =====================================================

-- جدول كاش نتائج البحث المتكررة
CREATE TABLE search_cache (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    query_hash CHAR(32) NOT NULL,
    filters_hash CHAR(32) NOT NULL,
    cached_results JSON NOT NULL,
    results_count INT UNSIGNED,
    cache_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_timestamp TIMESTAMP,
    hit_count INT UNSIGNED DEFAULT 0,
    
    UNIQUE INDEX idx_cache_key (query_hash, filters_hash),
    INDEX idx_expiry (expiry_timestamp),
    INDEX idx_hit_count (hit_count DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول إحصائيات الأداء
CREATE TABLE performance_metrics (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(10,4) NOT NULL,
    metric_unit VARCHAR(20),
    category ENUM('search', 'database', 'cache', 'index') DEFAULT 'search',
    measured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_metric_name (metric_name),
    INDEX idx_category_time (category, measured_at),
    INDEX idx_measured_at (measured_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. تحسين الجداول الموجودة
-- =====================================================

-- إضافة أعمدة محسنة لجدول الصفحات
ALTER TABLE pages 
ADD COLUMN content_hash CHAR(32) COMMENT 'MD5 hash للمحتوى',
ADD COLUMN content_language CHAR(2) DEFAULT 'ar',
ADD COLUMN readability_score DECIMAL(4,2) COMMENT 'درجة سهولة القراءة',
ADD COLUMN last_indexed_at TIMESTAMP NULL COMMENT 'آخر فهرسة',
ADD INDEX idx_content_hash (content_hash),
ADD INDEX idx_last_indexed (last_indexed_at);

-- إضافة أعمدة محسنة لجدول الكتب
ALTER TABLE books
ADD COLUMN search_popularity INT UNSIGNED DEFAULT 0 COMMENT 'شعبية البحث',
ADD COLUMN last_search_date TIMESTAMP NULL COMMENT 'آخر بحث',
ADD INDEX idx_search_popularity (search_popularity DESC),
ADD INDEX idx_last_search (last_search_date);

-- =====================================================
-- 5. إعدادات الأداء والتحسين
-- =====================================================

-- تحسين إعدادات MySQL للبحث النصي
SET GLOBAL ft_min_word_len = 2;
SET GLOBAL ft_boolean_syntax = '+ -><()~*:""&|';
SET GLOBAL innodb_ft_min_token_size = 2;
SET GLOBAL innodb_ft_enable_stopword = OFF;

-- تحسين ذاكرة InnoDB
SET GLOBAL innodb_buffer_pool_size = 2147483648; -- 2GB
SET GLOBAL innodb_log_file_size = 268435456; -- 256MB

-- =====================================================
-- 6. إجراءات مخزنة للصيانة
-- =====================================================

DELIMITER //

-- إجراء لبناء الفهرس المقلوب
CREATE PROCEDURE BuildInvertedIndex()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE page_id_var BIGINT;
    DECLARE content_var LONGTEXT;
    DECLARE book_id_var BIGINT;
    
    DECLARE page_cursor CURSOR FOR 
        SELECT id, book_id, content 
        FROM pages 
        WHERE content IS NOT NULL 
        AND content != ''
        AND (last_indexed_at IS NULL OR last_indexed_at < updated_at);
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN page_cursor;
    
    read_loop: LOOP
        FETCH page_cursor INTO page_id_var, book_id_var, content_var;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- هنا يتم استدعاء دالة معالجة الصفحة
        CALL ProcessPageForIndexing(page_id_var, book_id_var, content_var);
        
        -- تحديث وقت الفهرسة
        UPDATE pages 
        SET last_indexed_at = CURRENT_TIMESTAMP 
        WHERE id = page_id_var;
        
    END LOOP;
    
    CLOSE page_cursor;
    
    -- حساب IDF scores
    CALL CalculateIDFScores();
    
END //

-- إجراء لحساب IDF scores
CREATE PROCEDURE CalculateIDFScores()
BEGIN
    DECLARE total_pages INT;
    
    SELECT COUNT(*) INTO total_pages FROM pages WHERE content IS NOT NULL;
    
    UPDATE search_terms st
    SET idf_score = LOG(total_pages / GREATEST(1, document_frequency))
    WHERE document_frequency > 0;
    
    -- تحديث TF-IDF scores
    UPDATE term_page_occurrences tpo
    JOIN search_terms st ON tpo.term_id = st.id
    SET tpo.tfidf_score = tpo.tf_score * st.idf_score;
    
END //

-- إجراء لتنظيف الكاش المنتهي الصلاحية
CREATE PROCEDURE CleanExpiredCache()
BEGIN
    DELETE FROM search_cache 
    WHERE expiry_timestamp < CURRENT_TIMESTAMP;
    
    -- تنظيف الإحصائيات القديمة (أكثر من 6 أشهر)
    DELETE FROM search_analytics 
    WHERE search_timestamp < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 6 MONTH);
    
END //

-- إجراء لتحديث إحصائيات الأداء
CREATE PROCEDURE UpdatePerformanceMetrics()
BEGIN
    -- إدراج متوسط وقت البحث
    INSERT INTO performance_metrics (metric_name, metric_value, metric_unit, category)
    SELECT 
        'avg_search_time',
        AVG(execution_time_ms),
        'milliseconds',
        'search'
    FROM search_analytics 
    WHERE search_timestamp >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY);
    
    -- إدراج عدد البحثات اليومية
    INSERT INTO performance_metrics (metric_name, metric_value, metric_unit, category)
    SELECT 
        'daily_searches',
        COUNT(*),
        'count',
        'search'
    FROM search_analytics 
    WHERE DATE(search_timestamp) = CURDATE();
    
    -- حجم الفهرس
    INSERT INTO performance_metrics (metric_name, metric_value, metric_unit, category)
    SELECT 
        'index_size',
        COUNT(*),
        'terms',
        'index'
    FROM search_terms;
    
END //

DELIMITER ;

-- =====================================================
-- 7. تشغيل التحسينات الأولية
-- =====================================================

-- تحديث hash للمحتوى الموجود
UPDATE pages 
SET content_hash = MD5(content) 
WHERE content IS NOT NULL AND content_hash IS NULL;

-- تحديث عدد الكلمات للصفحات التي لا تحتوي على هذه البيانات
UPDATE pages 
SET word_count = (
    LENGTH(TRIM(content)) - LENGTH(REPLACE(TRIM(content), ' ', '')) + 1
) 
WHERE content IS NOT NULL 
AND content != '' 
AND (word_count IS NULL OR word_count = 0);

-- إنشاء فهرس لكلمات الوقف العربية الشائعة
INSERT INTO search_terms (term, normalized_term, term_type, is_stop_word, language) VALUES
('في', 'في', 'word', TRUE, 'ar'),
('من', 'من', 'word', TRUE, 'ar'),
('إلى', 'الى', 'word', TRUE, 'ar'),
('على', 'على', 'word', TRUE, 'ar'),
('عن', 'عن', 'word', TRUE, 'ar'),
('مع', 'مع', 'word', TRUE, 'ar'),
('هذا', 'هذا', 'word', TRUE, 'ar'),
('هذه', 'هذه', 'word', TRUE, 'ar'),
('ذلك', 'ذلك', 'word', TRUE, 'ar'),
('التي', 'التي', 'word', TRUE, 'ar'),
('الذي', 'الذي', 'word', TRUE, 'ar'),
('التي', 'التي', 'word', TRUE, 'ar')
ON DUPLICATE KEY UPDATE frequency = frequency + 1;

-- =====================================================
-- 8. استعلامات التحقق من الأداء
-- =====================================================

-- التحقق من حجم الفهارس
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    ROUND(STAT_VALUE * @@innodb_page_size / 1024 / 1024, 2) AS 'Index Size (MB)'
FROM information_schema.INNODB_SYS_TABLESTATS 
WHERE TABLE_NAME LIKE '%search%';

-- إحصائيات البحث
SELECT 
    COUNT(*) as total_searches,
    AVG(execution_time_ms) as avg_time,
    MIN(execution_time_ms) as min_time,
    MAX(execution_time_ms) as max_time
FROM search_analytics 
WHERE search_timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- أكثر المصطلحات بحثاً
SELECT 
    st.term,
    st.frequency,
    st.document_frequency,
    ROUND(st.idf_score, 4) as idf_score
FROM search_terms st 
ORDER BY st.frequency DESC 
LIMIT 20;

-- =====================================================
-- نهاية السكريبت
-- =====================================================

-- رسالة تأكيد
SELECT 'تم تطبيق جميع التحسينات بنجاح! 🚀' as status;
SELECT CONCAT('إجمالي الجداول المُحسنة: ', COUNT(*)) as summary
FROM information_schema.tables 
WHERE table_schema = 'bms';
