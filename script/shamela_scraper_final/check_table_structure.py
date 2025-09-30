#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
from pathlib import Path
from enhanced_database_manager import EnhancedShamelaDatabaseManager

def load_env_config():
    env_path = Path(__file__).parent.parent.parent / '.env'
    config = {}
    with open(env_path, 'r', encoding='utf-8') as f:
        for line in f:
            line = line.strip()
            if line and not line.startswith('#') and '=' in line:
                key, value = line.split('=', 1)
                config[key] = value.strip('"')
    
    return {
        'host': config.get('DB_HOST'),
        'port': int(config.get('DB_PORT', 3306)),
        'user': config.get('DB_USERNAME'),
        'password': config.get('DB_PASSWORD'),
        'database': config.get('DB_DATABASE'),
        'charset': 'utf8mb4'
    }

def main():
    try:
        db_config = load_env_config()
        with EnhancedShamelaDatabaseManager(db_config) as db_manager:
            db_manager.connect()
            
            # التحقق من جدول الصفحات
            result = db_manager.execute_query('DESCRIBE pages')
            print("هيكل جدول الصفحات:")
            for row in result:
                print(f"  {row['Field']} - {row['Type']}")
            
            print("\nهيكل جدول الكتب:")
            result = db_manager.execute_query('DESCRIBE books')
            for row in result:
                print(f"  {row['Field']} - {row['Type']}")
                
    except Exception as e:
        print(f"خطأ: {e}")
        return False
    return True

if __name__ == "__main__":
    main()