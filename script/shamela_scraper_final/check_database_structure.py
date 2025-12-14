#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector

def check_and_create_tables():
    """فحص بنية قاعدة البيانات وإنشاء الجداول المفقودة"""
    db_config = {
        'host': 'srv1800.hstgr.io',
        'port': 3306,
        'user': 'u994369532_test',
        'password': 'Test20205',
        'database': 'u994369532_test',
        'charset': 'utf8mb4'
    }
    
    connection = None
    cursor = None
    
    try:
        connection = mysql.connector.connect(**db_config)
        cursor = connection.cursor()
        
        # فحص الجداول الموجودة
        cursor.execute('SHOW TABLES')
        result = cursor.fetchall()
        tables = [table[0] for table in result] if result else []
        print(f"الجداول الموجودة: {tables}")
        
        # إنشاء الجداول المفقودة
        required_tables = {
            'volumes': '''
                CREATE TABLE IF NOT EXISTS volumes (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    book_id BIGINT UNSIGNED NOT NULL,
                    number INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    page_start INT NULL,
                    page_end INT NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    INDEX idx_book_id (book_id),
                    INDEX idx_number (number)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ''',
            'chapters': '''
                CREATE TABLE IF NOT EXISTS chapters (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    book_id BIGINT UNSIGNED NOT NULL,
                    volume_id BIGINT UNSIGNED NULL,
                    parent_id BIGINT UNSIGNED NULL,
                    title VARCHAR(500) NOT NULL,
                    page_number INT NULL,
                    page_end INT NULL,
                    level INT DEFAULT 1,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    INDEX idx_book_id (book_id),
                    INDEX idx_volume_id (volume_id),
                    INDEX idx_parent_id (parent_id),
                    INDEX idx_page_number (page_number)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ''',
            'pages': '''
                CREATE TABLE IF NOT EXISTS pages (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    book_id BIGINT UNSIGNED NOT NULL,
                    volume_id BIGINT UNSIGNED NULL,
                    chapter_id BIGINT UNSIGNED NULL,
                    page_number INT NOT NULL,
                    content LONGTEXT NULL,
                    html_content LONGTEXT NULL,
                    word_count INT DEFAULT 0,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    INDEX idx_book_id (book_id),
                    INDEX idx_volume_id (volume_id),
                    INDEX idx_chapter_id (chapter_id),
                    INDEX idx_page_number (page_number)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ''',
            'author_book': '''
                CREATE TABLE IF NOT EXISTS author_book (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    book_id BIGINT UNSIGNED NOT NULL,
                    author_id BIGINT UNSIGNED NOT NULL,
                    role VARCHAR(50) DEFAULT 'author',
                    is_main BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    INDEX idx_book_id (book_id),
                    INDEX idx_author_id (author_id),
                    UNIQUE KEY unique_book_author (book_id, author_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            '''
        }
        
        for table_name, create_sql in required_tables.items():
            if table_name not in tables:
                print(f"إنشاء جدول {table_name}...")
                cursor.execute(create_sql)
                print(f"تم إنشاء جدول {table_name} بنجاح")
            else:
                print(f"جدول {table_name} موجود بالفعل")
        
        connection.commit()
        print("تم إنشاء جميع الجداول المطلوبة بنجاح")
        
    except Exception as e:
        print(f"خطأ: {e}")
        import traceback
        traceback.print_exc()
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()

if __name__ == "__main__":
    check_and_create_tables()