#!/usr/bin/env python
# -*- coding: utf-8 -*-

import mysql.connector
import sys

def quick_db_check():
    """فحص سريع لقاعدة البيانات"""
    
    try:
        # اتصال واحد سريع
        conn = mysql.connector.connect(
            host='srv1800.hstgr.io',
            user='u994369532_test',
            password='Test20205',
            database='u994369532_test'
        )
        cursor = conn.cursor(dictionary=True)
        
        print("✅ اتصال ناجح - فحص البيانات الجديدة:")
        print("=" * 50)
        
        # آخر كتاب محفوظ
        cursor.execute("SELECT * FROM books ORDER BY id DESC LIMIT 1")
        book = cursor.fetchone()
        if book:
            print(f"📚 آخر كتاب: {book['title']}")
            print(f"🆔 ID: {book['id']}")
            print(f"🔢 الشاملة: {book['shamela_id']}")
            print(f"📄 الصفحات: {book['pages_count']}")
            print(f"📝 الطبعة: {book['edition']} (رقم {book['edition_number']})")
        
        # عدد الكتب
        cursor.execute("SELECT COUNT(*) as count FROM books")
        books_count = cursor.fetchone()['count']
        print(f"\n📊 إجمالي الكتب: {books_count}")
        
        # عدد الناشرين
        cursor.execute("SELECT COUNT(*) as count FROM publishers")
        pub_count = cursor.fetchone()['count']
        print(f"🏢 إجمالي الناشرين: {pub_count}")
        
        # آخر ناشر
        cursor.execute("SELECT name FROM publishers ORDER BY id DESC LIMIT 1")
        pub = cursor.fetchone()
        if pub:
            print(f"📰 آخر ناشر: {pub['name']}")
        
        cursor.close()
        conn.close()
        print("\n✅ تم إنهاء الفحص بنجاح!")
        
    except Exception as e:
        print(f"❌ خطأ: {e}")

if __name__ == "__main__":
    quick_db_check()
