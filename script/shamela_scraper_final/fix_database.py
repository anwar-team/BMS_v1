#!/usr/bin/env python
# -*- coding: utf-8 -*-

import mysql.connector
from mysql.connector import Error
import sys

def check_and_fix_database():
    """فحص قاعدة البيانات وإضافة الحقول المفقودة"""
    
    config = {
        'host': 'srv1800.hstgr.io',
        'port': 3306,
        'user': 'u994369532_test',
        'password': 'Test20205',
        'database': 'u994369532_test',
        'charset': 'utf8mb4'
    }
    
    try:
        # الاتصال بقاعدة البيانات
        connection = mysql.connector.connect(**config)
        cursor = connection.cursor(dictionary=True)
        print("✅ تم الاتصال بقاعدة البيانات بنجاح")
        
        # فحص جدول publishers
        print("\n📊 فحص جدول publishers...")
        cursor.execute("DESCRIBE publishers")
        columns = cursor.fetchall()
        
        existing_columns = [col['Field'] for col in columns]
        print(f"الحقول الموجودة: {existing_columns}")
        
        # إضافة حقل slug إذا لم يكن موجوداً
        if 'slug' not in existing_columns:
            print("🔧 إضافة حقل slug...")
            cursor.execute("""
                ALTER TABLE publishers 
                ADD COLUMN slug VARCHAR(255) NULL AFTER name,
                ADD UNIQUE KEY publishers_slug_unique (slug)
            """)
            print("✅ تم إضافة حقل slug")
        else:
            print("✅ حقل slug موجود بالفعل")
            
        # فحص جدول books
        print("\n📊 فحص جدول books...")
        try:
            cursor.execute("DESCRIBE books")
            columns = cursor.fetchall()
            existing_columns = [col['Field'] for col in columns]
            print(f"الحقول الموجودة في books: {existing_columns}")
            
            # قائمة الحقول المطلوبة
            required_columns = [
                ('slug', 'VARCHAR(255) NULL AFTER title'),
                ('edition_number', 'INT NULL AFTER edition'),
                ('edition_date_hijri', 'VARCHAR(100) NULL AFTER publication_year'),
                ('source_url', 'TEXT NULL AFTER description'),
                ('has_original_pagination', 'BOOLEAN DEFAULT FALSE AFTER source_url')
            ]
            
            for field, definition in required_columns:
                if field not in existing_columns:
                    print(f"🔧 إضافة حقل {field}...")
                    cursor.execute(f"ALTER TABLE books ADD COLUMN {field} {definition}")
                    print(f"✅ تم إضافة حقل {field}")
                else:
                    print(f"✅ حقل {field} موجود بالفعل")
                    
        except Error as e:
            if "doesn't exist" in str(e):
                print("⚠️ جدول books غير موجود - سيتم إنشاؤه لاحقاً")
            else:
                print(f"خطأ في فحص books: {e}")
        
        # فحص جدول book_sections
        print("\n📊 فحص جدول book_sections...")
        try:
            cursor.execute("DESCRIBE book_sections")
            columns = cursor.fetchall()
            existing_columns = [col['Field'] for col in columns]
            print(f"الحقول الموجودة: {existing_columns}")
            
            if 'slug' not in existing_columns:
                print("🔧 إضافة حقل slug...")
                cursor.execute("""
                    ALTER TABLE book_sections 
                    ADD COLUMN slug VARCHAR(255) NULL AFTER name,
                    ADD UNIQUE KEY book_sections_slug_unique (slug)
                """)
                print("✅ تم إضافة حقل slug")
            else:
                print("✅ حقل slug موجود بالفعل")
        except Error as e:
            if "doesn't exist" in str(e):
                print("⚠️ جدول book_sections غير موجود - سيتم إنشاؤه لاحقاً")
            else:
                print(f"خطأ في فحص book_sections: {e}")
        
        # حفظ التغييرات
        connection.commit()
        print("\n✅ تم حفظ جميع التغييرات بنجاح!")
        
    except Error as e:
        print(f"❌ خطأ في قاعدة البيانات: {e}")
        return False
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'connection' in locals():
            connection.close()
            print("🔐 تم إغلاق الاتصال")
    
    return True

if __name__ == "__main__":
    print("🛠️ بدء فحص وإصلاح قاعدة البيانات...")
    success = check_and_fix_database()
    sys.exit(0 if success else 1)
