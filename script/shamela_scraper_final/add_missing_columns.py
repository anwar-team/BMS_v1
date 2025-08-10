#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from shamela_database_manager import ShamelaDatabaseManager

def add_missing_columns():
    """إضافة الأعمدة المفقودة إلى جدول books"""
    db_config = {
        'host': 'srv1800.hstgr.io',
        'port': 3306,
        'user': 'u994369532_test',
        'password': 'Test20205',
        'database': 'u994369532_test',
        'charset': 'utf8mb4'
    }
    
    db = ShamelaDatabaseManager(db_config)
    
    try:
        db.connect()
        
        # إضافة الأعمدة المفقودة
        columns_to_add = [
            "ALTER TABLE books ADD COLUMN IF NOT EXISTS categories JSON NULL AFTER volumes_count",
            "ALTER TABLE books ADD COLUMN IF NOT EXISTS edition VARCHAR(100) NULL AFTER publisher",
            "ALTER TABLE books ADD COLUMN IF NOT EXISTS language VARCHAR(10) NULL DEFAULT 'ar' AFTER description"
        ]
        
        for sql in columns_to_add:
            try:
                db.cursor.execute(sql)
                print(f"تم تنفيذ: {sql}")
            except Exception as e:
                print(f"خطأ في تنفيذ {sql}: {e}")
        
        db.connection.commit()
        print("تم إضافة جميع الأعمدة المفقودة بنجاح")
        
    except Exception as e:
        print(f"خطأ: {e}")
    finally:
        db.disconnect()

if __name__ == "__main__":
    add_missing_columns()