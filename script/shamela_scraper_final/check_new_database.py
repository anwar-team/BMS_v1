#!/usr/bin/env python
# -*- coding: utf-8 -*-

import mysql.connector
from mysql.connector import Error
import sys
from datetime import datetime

def check_new_database():
    """فحص قاعدة البيانات الجديدة وعرض المحتويات"""
    
    config = {
        'host': '192.168.1.192',
        'port': 3306,
        'user': 'root',
        'password': 'Azizmohammad@2003',
        'database': 'u994369532_test',
        'charset': 'utf8mb4'
    }
    
    try:
        print("🔍 فحص قاعدة البيانات الجديدة...")
        print("=" * 60)
        
        connection = mysql.connector.connect(**config)
        cursor = connection.cursor(dictionary=True)
        print("✅ تم الاتصال بقاعدة البيانات بنجاح")
        
        # فحص الكتب المحفوظة
        print("\n📚 الكتب المحفوظة:")
        print("-" * 40)
        cursor.execute("""
            SELECT b.id, b.title, b.shamela_id, b.pages_count, 
                   b.volumes_count, b.edition, b.edition_number,
                   p.name as publisher_name, bs.name as section_name
            FROM books b
            LEFT JOIN publishers p ON b.publisher_id = p.id
            LEFT JOIN book_sections bs ON b.book_section_id = bs.id
            ORDER BY b.id DESC
            LIMIT 10
        """)
        books = cursor.fetchall()
        
        if books:
            for book in books:
                print(f"🆔 ID: {book['id']}")
                print(f"📖 العنوان: {book['title']}")
                print(f"🔢 رقم الشاملة: {book['shamela_id']}")
                print(f"📄 الصفحات: {book['pages_count']}")
                print(f"📚 المجلدات: {book['volumes_count']}")
                print(f"📝 الطبعة: {book['edition']}")
                print(f"🔢 رقم الطبعة: {book['edition_number']}")
                print(f"🏢 الناشر: {book['publisher_name']}")
                print(f"📂 القسم: {book['section_name']}")
                print("-" * 40)
        else:
            print("❌ لا توجد كتب محفوظة")
        
        # فحص الناشرين
        print("\n🏢 الناشرين:")
        print("-" * 30)
        cursor.execute("SELECT * FROM publishers ORDER BY id DESC LIMIT 5")
        publishers = cursor.fetchall()
        
        for pub in publishers:
            print(f"🆔 {pub['id']}: {pub['name']}")
        
        # فحص الأقسام
        print("\n📂 أقسام الكتب:")
        print("-" * 30)
        cursor.execute("SELECT * FROM book_sections ORDER BY id DESC LIMIT 5")
        sections = cursor.fetchall()
        
        for section in sections:
            print(f"🆔 {section['id']}: {section['name']}")
        
        # فحص المؤلفين
        print("\n👥 المؤلفين:")
        print("-" * 30)
        cursor.execute("SELECT * FROM authors ORDER BY id DESC LIMIT 5")
        authors = cursor.fetchall()
        
        for author in authors:
            print(f"🆔 {author['id']}: {author['full_name']}")
        
        # فحص الفصول للكتاب الأخير
        if books:
            last_book_id = books[0]['id']
            print(f"\n📑 فصول الكتاب {last_book_id}:")
            print("-" * 40)
            cursor.execute("""
                SELECT id, title, `order`, page_start, page_end
                FROM chapters 
                WHERE book_id = %s 
                ORDER BY `order`
                LIMIT 10
            """, (last_book_id,))
            chapters = cursor.fetchall()
            
            for chapter in chapters:
                print(f"📋 {chapter['order']}: {chapter['title']} (ص {chapter['page_start']}-{chapter['page_end']})")
        
        # إحصائيات عامة
        print("\n📊 إحصائيات عامة:")
        print("-" * 30)
        
        tables = ['books', 'publishers', 'book_sections', 'authors', 'chapters', 'pages']
        for table in tables:
            try:
                cursor.execute(f"SELECT COUNT(*) as count FROM {table}")
                count = cursor.fetchone()['count']
                print(f"📈 {table}: {count} سجل")
            except:
                print(f"⚠️ {table}: جدول غير متاح")
        
    except Error as e:
        print(f"❌ خطأ في قاعدة البيانات: {e}")
        return False
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'connection' in locals():
            connection.close()
            print("\n🔐 تم إغلاق الاتصال")
    
    return True

if __name__ == "__main__":
    check_new_database()
