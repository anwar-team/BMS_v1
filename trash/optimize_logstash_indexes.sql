-- ══════════════════════════════════════════════════════════════════════════════
-- تحسين MySQL Indexes لتسريع Logstash Query
-- الهدف: من 26 ثانية إلى أقل من 1 ثانية
-- ══════════════════════════════════════════════════════════════════════════════

USE bms;

-- 1. Index على pages.id (للترتيب و WHERE)
-- هذا مهم جداً لأن الـ query يستخدم: WHERE p.id > X ORDER BY p.id
ALTER TABLE pages ADD INDEX idx_pages_id_optimized (id);

-- 2. Index على pages.book_id (للـ JOIN مع books)
ALTER TABLE pages ADD INDEX idx_pages_book_id (book_id) IF NOT EXISTS;

-- 3. Index على books.id (PRIMARY KEY - عادة موجود)
-- لكن نتأكد أنه موجود
-- SHOW INDEX FROM books; -- فحص فقط

-- 4. Index مركب على author_book لتسريع الـ JOIN
-- (book_id, is_main) لأن الـ query يستخدم: AND ab.is_main = 1
ALTER TABLE author_book ADD INDEX idx_author_book_main (book_id, is_main);

-- 5. Index على author_book.author_id (للـ JOIN مع authors)
ALTER TABLE author_book ADD INDEX idx_author_book_author (author_id) IF NOT EXISTS;

-- 6. Index على books.book_section_id (للـ JOIN مع book_sections)
ALTER TABLE books ADD INDEX idx_books_section (book_section_id) IF NOT EXISTS;

-- 7. Analyze Tables لتحديث الإحصائيات
ANALYZE TABLE pages;
ANALYZE TABLE books;
ANALYZE TABLE author_book;
ANALYZE TABLE authors;
ANALYZE TABLE book_sections;

-- ══════════════════════════════════════════════════════════════════════════════
-- التحقق من الـ Indexes
-- ══════════════════════════════════════════════════════════════════════════════

SHOW INDEX FROM pages WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM books WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM author_book WHERE Key_name LIKE 'idx_%';

-- ══════════════════════════════════════════════════════════════════════════════
-- اختبار السرعة
-- ══════════════════════════════════════════════════════════════════════════════

-- قبل التحسين vs بعد التحسين:
EXPLAIN SELECT 
  p.id,
  p.page_number,
  p.content,
  p.book_id,
  b.title as book_title,
  b.book_section_id,
  a.full_name as book_author,
  bs.name as book_section
FROM pages p 
LEFT JOIN books b ON p.book_id = b.id 
LEFT JOIN author_book ab ON b.id = ab.book_id AND ab.is_main = 1
LEFT JOIN authors a ON ab.author_id = a.id
LEFT JOIN book_sections bs ON b.book_section_id = bs.id
WHERE p.id > 1000000 
ORDER BY p.id ASC 
LIMIT 10000;

-- يجب أن تظهر: "Using index" في Extra column
