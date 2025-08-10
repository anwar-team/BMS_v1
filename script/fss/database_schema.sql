-- ===================================================
-- هيكل قاعدة البيانات لمشروع استخراج كتب الشاملة
-- Shamela Books Database Schema
-- ===================================================

-- إنشاء قاعدة البيانات (اختياري)
-- CREATE DATABASE IF NOT EXISTS bms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE bms;

-- ===================================================
-- جدول المؤلفين (Authors)
-- ===================================================
CREATE TABLE IF NOT EXISTS authors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL COMMENT 'اسم المؤلف',
    slug VARCHAR(200) COMMENT 'الاسم المختصر للرابط',
    biography TEXT COMMENT 'ترجمة المؤلف',
    madhhab VARCHAR(100) COMMENT 'المذهب الفقهي',
    birth_date VARCHAR(50) COMMENT 'تاريخ الولادة',
    death_date VARCHAR(50) COMMENT 'تاريخ الوفاة',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_authors_name (name),
    INDEX idx_authors_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول المؤلفين';

-- ===================================================
-- جدول الكتب (Books)
-- ===================================================
CREATE TABLE IF NOT EXISTS books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(500) NOT NULL COMMENT 'عنوان الكتاب',
    slug VARCHAR(500) COMMENT 'العنوان المختصر للرابط',
    shamela_id VARCHAR(50) UNIQUE NOT NULL COMMENT 'معرف الكتاب في الشاملة',
    publisher VARCHAR(200) COMMENT 'دار النشر',
    edition VARCHAR(100) COMMENT 'الطبعة',
    publication_year VARCHAR(20) COMMENT 'سنة النشر',
    pages_count INT COMMENT 'عدد الصفحات',
    volumes_count INT COMMENT 'عدد الأجزاء',
    categories JSON COMMENT 'التصنيفات',
    description TEXT COMMENT 'وصف الكتاب',
    language VARCHAR(10) DEFAULT 'ar' COMMENT 'لغة الكتاب',
    source_url VARCHAR(500) COMMENT 'رابط المصدر',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_books_title (title),
    INDEX idx_books_shamela_id (shamela_id),
    INDEX idx_books_publisher (publisher),
    INDEX idx_books_publication_year (publication_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول الكتب';

-- ===================================================
-- جدول ربط المؤلفين بالكتب (Author-Book Relationship)
-- ===================================================
CREATE TABLE IF NOT EXISTS author_book (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL COMMENT 'معرف الكتاب',
    author_id INT NOT NULL COMMENT 'معرف المؤلف',
    role VARCHAR(50) DEFAULT 'author' COMMENT 'دور المؤلف (مؤلف، محقق، مترجم، إلخ)',
    is_main BOOLEAN DEFAULT TRUE COMMENT 'هل هو المؤلف الرئيسي',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_book_author (book_id, author_id),
    INDEX idx_author_book_book_id (book_id),
    INDEX idx_author_book_author_id (author_id),
    INDEX idx_author_book_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول ربط المؤلفين بالكتب';

-- ===================================================
-- جدول الأجزاء (Volumes)
-- ===================================================
CREATE TABLE IF NOT EXISTS volumes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL COMMENT 'معرف الكتاب',
    number INT NOT NULL COMMENT 'رقم الجزء',
    title VARCHAR(200) COMMENT 'عنوان الجزء',
    page_start INT COMMENT 'الصفحة الأولى',
    page_end INT COMMENT 'الصفحة الأخيرة',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_book_volume (book_id, number),
    INDEX idx_volumes_book_id (book_id),
    INDEX idx_volumes_number (number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول أجزاء الكتب';

-- ===================================================
-- جدول الفصول (Chapters)
-- ===================================================
CREATE TABLE IF NOT EXISTS chapters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL COMMENT 'معرف الكتاب',
    volume_id INT COMMENT 'معرف الجزء',
    title VARCHAR(500) NOT NULL COMMENT 'عنوان الفصل',
    page_number INT COMMENT 'رقم الصفحة التي يبدأ فيها الفصل',
    page_end INT COMMENT 'رقم الصفحة التي ينتهي فيها الفصل',
    parent_id INT COMMENT 'معرف الفصل الأب (للفصول الفرعية)',
    level INT DEFAULT 0 COMMENT 'مستوى الفصل في التسلسل الهرمي',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (volume_id) REFERENCES volumes(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES chapters(id) ON DELETE CASCADE,
    
    INDEX idx_chapters_book_id (book_id),
    INDEX idx_chapters_volume_id (volume_id),
    INDEX idx_chapters_parent_id (parent_id),
    INDEX idx_chapters_page_number (page_number),
    INDEX idx_chapters_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول فصول الكتب';

-- ===================================================
-- جدول الصفحات (Pages)
-- ===================================================
CREATE TABLE IF NOT EXISTS pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL COMMENT 'معرف الكتاب',
    volume_id INT COMMENT 'معرف الجزء',
    chapter_id INT COMMENT 'معرف الفصل',
    page_number INT NOT NULL COMMENT 'رقم الصفحة',
    content LONGTEXT COMMENT 'محتوى الصفحة النصي',
    html_content LONGTEXT COMMENT 'محتوى الصفحة بتنسيق HTML',
    word_count INT COMMENT 'عدد الكلمات في الصفحة',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (volume_id) REFERENCES volumes(id) ON DELETE SET NULL,
    FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_book_page (book_id, page_number),
    INDEX idx_pages_book_id (book_id),
    INDEX idx_pages_volume_id (volume_id),
    INDEX idx_pages_chapter_id (chapter_id),
    INDEX idx_pages_page_number (page_number),
    INDEX idx_pages_word_count (word_count),
    
    -- فهرس نصي للبحث في المحتوى
    FULLTEXT KEY ft_pages_content (content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول صفحات الكتب';

-- ===================================================
-- Views مفيدة (اختيارية)
-- ===================================================

-- عرض تفصيلي للكتب مع معلومات المؤلفين
CREATE OR REPLACE VIEW books_with_authors AS
SELECT 
    b.id,
    b.title,
    b.shamela_id,
    b.publisher,
    b.publication_year,
    b.pages_count,
    b.volumes_count,
    GROUP_CONCAT(a.name SEPARATOR ', ') AS authors,
    b.created_at,
    b.updated_at
FROM books b
LEFT JOIN author_book ab ON b.id = ab.book_id
LEFT JOIN authors a ON ab.author_id = a.id
GROUP BY b.id;

-- عرض إحصائيات الكتب
CREATE OR REPLACE VIEW books_statistics AS
SELECT 
    b.id,
    b.title,
    b.shamela_id,
    COUNT(DISTINCT v.id) AS actual_volumes_count,
    COUNT(DISTINCT c.id) AS chapters_count,
    COUNT(DISTINCT p.id) AS actual_pages_count,
    SUM(p.word_count) AS total_words,
    AVG(p.word_count) AS avg_words_per_page
FROM books b
LEFT JOIN volumes v ON b.id = v.book_id
LEFT JOIN chapters c ON b.id = c.book_id
LEFT JOIN pages p ON b.id = p.book_id
GROUP BY b.id;

-- ===================================================
-- Stored Procedures مفيدة (اختيارية)
-- ===================================================

DELIMITER //

-- إجراء للحصول على إحصائيات كتاب معين
CREATE PROCEDURE GetBookStatistics(IN book_id INT)
BEGIN
    SELECT 
        b.title,
        b.shamela_id,
        b.pages_count AS declared_pages,
        COUNT(DISTINCT p.id) AS actual_pages,
        COUNT(DISTINCT v.id) AS volumes_count,
        COUNT(DISTINCT c.id) AS chapters_count,
        COUNT(DISTINCT a.id) AS authors_count,
        SUM(p.word_count) AS total_words,
        AVG(p.word_count) AS avg_words_per_page,
        MIN(p.page_number) AS first_page,
        MAX(p.page_number) AS last_page
    FROM books b
    LEFT JOIN pages p ON b.id = p.book_id
    LEFT JOIN volumes v ON b.id = v.book_id
    LEFT JOIN chapters c ON b.id = c.book_id
    LEFT JOIN author_book ab ON b.id = ab.book_id
    LEFT JOIN authors a ON ab.author_id = a.id
    WHERE b.id = book_id
    GROUP BY b.id;
END //

-- إجراء للبحث في محتوى الكتب
CREATE PROCEDURE SearchInBooks(IN search_term VARCHAR(500))
BEGIN
    SELECT 
        b.title,
        b.shamela_id,
        p.page_number,
        SUBSTRING(p.content, 
            GREATEST(1, LOCATE(search_term, p.content) - 50), 
            100
        ) AS context,
        MATCH(p.content) AGAINST(search_term IN NATURAL LANGUAGE MODE) AS relevance
    FROM books b
    JOIN pages p ON b.id = p.book_id
    WHERE MATCH(p.content) AGAINST(search_term IN NATURAL LANGUAGE MODE)
    ORDER BY relevance DESC
    LIMIT 50;
END //

DELIMITER ;

-- ===================================================
-- بيانات تجريبية (اختيارية)
-- ===================================================

-- إدراج مؤلف تجريبي
-- INSERT INTO authors (name, biography, birth_date, death_date) 
-- VALUES ('الإمام الشافعي', 'محمد بن إدريس الشافعي، إمام من أئمة المذاهب الأربعة', '150', '204');

-- إدراج كتاب تجريبي
-- INSERT INTO books (title, shamela_id, publisher, pages_count, volumes_count) 
-- VALUES ('الأم', '12345', 'دار المعرفة', 500, 8);

-- ===================================================
-- تحسينات الأداء
-- ===================================================

-- تحسين إعدادات MySQL للنصوص العربية
-- SET NAMES utf8mb4;
-- SET CHARACTER SET utf8mb4;
-- SET character_set_connection=utf8mb4;

-- تحسين إعدادات InnoDB
-- SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
-- SET GLOBAL innodb_log_file_size = 268435456; -- 256MB

-- ===================================================
-- ملاحظات مهمة
-- ===================================================

/*
1. تأكد من أن MySQL يدعم utf8mb4 للنصوص العربية
2. قم بضبط حجم innodb_buffer_pool_size حسب حجم البيانات المتوقع
3. استخدم الفهارس النصية (FULLTEXT) للبحث السريع في المحتوى
4. راقب أداء الاستعلامات وأضف فهارس إضافية حسب الحاجة
5. قم بعمل نسخ احتياطية دورية للبيانات
6. استخدم الـ Views للاستعلامات المعقدة المتكررة
7. الـ Stored Procedures مفيدة للعمليات المعقدة
*/

-- ===================================================
-- انتهاء ملف إنشاء قاعدة البيانات
-- ===================================================