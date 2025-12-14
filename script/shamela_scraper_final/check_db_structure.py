#!/usr/bin/env python
# -*- coding: utf-8 -*-

import mysql.connector
from mysql.connector import Error
import sys

def check_database_structure():
    """فحص هيكل قاعدة البيانات وعرضه"""
    
    config = {
        'host': 'srv1800.hstgr.io',
        'port': 3306,
        'user': 'u994369532_test',
        'password': 'Test20205',
        'database': 'u994369532_test',
        'charset': 'utf8mb4'
    }
    
    try:
        connection = mysql.connector.connect(**config)
        cursor = connection.cursor(dictionary=True)
        print("✅ تم الاتصال بقاعدة البيانات بنجاح")
        
        # عرض جميع الجداول
        cursor.execute("SHOW TABLES")
        tables = [table[f'Tables_in_{config["database"]}'] for table in cursor.fetchall()]
        print(f"\n📋 الجداول الموجودة: {tables}")
        
        # فحص كل جدول
        for table in ['books', 'publishers', 'book_sections', 'authors']:
            if table in tables:
                print(f"\n📊 جدول {table}:")
                cursor.execute(f"DESCRIBE {table}")
                columns = cursor.fetchall()
                for col in columns:
                    print(f"  - {col['Field']} ({col['Type']}) {'NOT NULL' if col['Null'] == 'NO' else 'NULL'}")
            else:
                print(f"\n⚠️ جدول {table} غير موجود")
                
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
    print("🔍 فحص هيكل قاعدة البيانات...")
    check_database_structure()
