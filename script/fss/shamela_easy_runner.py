#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
سكربت تشغيل سهل لاستخراج كتب الشاملة
Easy runner for Shamela Books Scraper

هذا السكربت يوفر واجهة بسيطة لاستخراج الكتب من المكتبة الشاملة
ويحفظها في قاعدة البيانات أو ملفات JSON
"""

import os
import sys
import argparse
import logging
from typing import Optional, List, Dict, Any

# إضافة مجلد السكربت إلى المسار
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

try:
    from shamela_complete_scraper import scrape_complete_book, save_book_to_json
    from shamela_database_manager import ShamelaDatabaseManager
    from utils import (
        extract_book_id_from_url, is_valid_shamela_url, 
        ProgressTracker, format_duration, safe_filename
    )
    from config import (
        setup_logging, get_db_config, get_message, 
        OUTPUT_DIR, PROJECT_NAME, PROJECT_VERSION
    )
except ImportError as e:
    print(f"خطأ في استيراد الوحدات المطلوبة: {e}")
    print("تأكد من وجود جميع الملفات المطلوبة في نفس المجلد")
    sys.exit(1)

# إعداد نظام السجلات
logger = setup_logging()

class ShamelaScraper:
    """فئة رئيسية لاستخراج كتب الشاملة"""
    
    def __init__(self, db_config: Optional[Dict[str, Any]] = None):
        self.db_config = db_config
        self.db_manager = None
        
        if db_config:
            try:
                self.db_manager = ShamelaDatabaseManager(db_config)
                logger.info("تم الاتصال بقاعدة البيانات بنجاح")
            except Exception as e:
                logger.error(f"فشل الاتصال بقاعدة البيانات: {e}")
                self.db_manager = None
    
    def extract_single_book(self, book_input: str, 
                           save_to_db: bool = True,
                           save_to_json: bool = True,
                           extract_html: bool = False,
                           output_dir: str = None) -> bool:
        """استخراج كتاب واحد"""
        
        # استخراج معرف الكتاب
        book_id = self._get_book_id(book_input)
        if not book_id:
            logger.error(f"لا يمكن استخراج معرف الكتاب من: {book_input}")
            return False
        
        logger.info(f"بدء استخراج الكتاب رقم: {book_id}")
        
        try:
            # استخراج بيانات الكتاب
            book_data = scrape_complete_book(
                book_id=book_id,
                extract_html=extract_html
            )
            
            if not book_data:
                logger.error(f"فشل في استخراج بيانات الكتاب {book_id}")
                return False
            
            logger.info(f"تم استخراج الكتاب: {book_data.title}")
            logger.info(f"عدد الصفحات: {len(book_data.pages)}")
            
            success = True
            
            # حفظ في قاعدة البيانات
            if save_to_db and self.db_manager:
                try:
                    self.db_manager.save_complete_book(book_data)
                    logger.info("تم حفظ الكتاب في قاعدة البيانات")
                except Exception as e:
                    logger.error(f"فشل حفظ الكتاب في قاعدة البيانات: {e}")
                    success = False
            
            # حفظ في ملف JSON
            if save_to_json:
                try:
                    if not output_dir:
                        output_dir = OUTPUT_DIR
                    
                    filename = safe_filename(f"{book_data.title}_{book_id}")
                    json_path = os.path.join(output_dir, f"{filename}.json")
                    
                    save_book_to_json(book_data, json_path)
                    logger.info(f"تم حفظ الكتاب في: {json_path}")
                except Exception as e:
                    logger.error(f"فشل حفظ الكتاب في ملف JSON: {e}")
                    success = False
            
            return success
            
        except Exception as e:
            logger.error(f"خطأ في استخراج الكتاب {book_id}: {e}")
            return False
    
    def extract_multiple_books(self, book_inputs: List[str],
                              save_to_db: bool = True,
                              save_to_json: bool = True,
                              extract_html: bool = False,
                              output_dir: str = None) -> Dict[str, bool]:
        """استخراج عدة كتب"""
        
        results = {}
        total_books = len(book_inputs)
        
        logger.info(f"بدء استخراج {total_books} كتاب")
        
        # متتبع التقدم
        progress = ProgressTracker(total_books, "استخراج الكتب")
        
        for i, book_input in enumerate(book_inputs, 1):
            book_id = self._get_book_id(book_input)
            
            if not book_id:
                logger.warning(f"تخطي: لا يمكن استخراج معرف الكتاب من {book_input}")
                results[book_input] = False
                progress.update()
                continue
            
            logger.info(f"[{i}/{total_books}] معالجة الكتاب: {book_id}")
            
            success = self.extract_single_book(
                book_input=book_id,
                save_to_db=save_to_db,
                save_to_json=save_to_json,
                extract_html=extract_html,
                output_dir=output_dir
            )
            
            results[book_input] = success
            progress.update()
            
            # طباعة التقدم
            logger.info(progress.get_status_message())
        
        # ملخص النتائج
        successful = sum(1 for success in results.values() if success)
        failed = total_books - successful
        
        logger.info(f"انتهت المعالجة: {successful} نجح، {failed} فشل")
        
        return results
    
    def _get_book_id(self, book_input: str) -> Optional[str]:
        """استخراج معرف الكتاب من النص أو الرابط"""
        
        # إذا كان رقماً مباشراً
        if book_input.isdigit():
            return book_input
        
        # إذا كان رابطاً
        if is_valid_shamela_url(book_input):
            return extract_book_id_from_url(book_input)
        
        # محاولة استخراج الرقم من النص
        import re
        numbers = re.findall(r'\d+', book_input)
        if numbers:
            return numbers[0]
        
        return None
    
    def close(self):
        """إغلاق الاتصالات"""
        if self.db_manager:
            self.db_manager.close()

def load_books_from_file(file_path: str) -> List[str]:
    """تحميل قائمة الكتب من ملف"""
    books = []
    
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            for line in f:
                line = line.strip()
                
                # تخطي الأسطر الفارغة والتعليقات
                if not line or line.startswith('#'):
                    continue
                
                books.append(line)
        
        logger.info(f"تم تحميل {len(books)} كتاب من الملف: {file_path}")
        
    except Exception as e:
        logger.error(f"خطأ في تحميل ملف الكتب: {e}")
    
    return books

def interactive_mode():
    """الوضع التفاعلي"""
    print(f"\n=== {PROJECT_NAME} v{PROJECT_VERSION} ===")
    print("الوضع التفاعلي - اكتب 'help' للمساعدة أو 'exit' للخروج\n")
    
    scraper = None
    
    while True:
        try:
            command = input("shamela> ").strip()
            
            if not command:
                continue
            
            if command.lower() in ['exit', 'quit', 'خروج']:
                break
            
            if command.lower() in ['help', 'مساعدة']:
                print_help()
                continue
            
            if command.lower().startswith('db '):
                # إعداد قاعدة البيانات
                db_parts = command.split()[1:]
                if len(db_parts) >= 4:
                    host, user, password, database = db_parts[:4]
                    db_config = {
                        'host': host,
                        'user': user,
                        'password': password,
                        'database': database
                    }
                    
                    if scraper:
                        scraper.close()
                    
                    scraper = ShamelaScraper(db_config)
                    print("تم إعداد قاعدة البيانات")
                else:
                    print("استخدام: db <host> <user> <password> <database>")
                continue
            
            if command.lower().startswith('book '):
                # استخراج كتاب
                book_input = command[5:].strip()
                
                if not scraper:
                    scraper = ShamelaScraper()
                
                success = scraper.extract_single_book(
                    book_input=book_input,
                    save_to_db=scraper.db_manager is not None,
                    save_to_json=True
                )
                
                if success:
                    print("تم استخراج الكتاب بنجاح")
                else:
                    print("فشل في استخراج الكتاب")
                continue
            
            # محاولة استخراج كتاب مباشرة
            if not scraper:
                scraper = ShamelaScraper()
            
            success = scraper.extract_single_book(
                book_input=command,
                save_to_db=scraper.db_manager is not None,
                save_to_json=True
            )
            
            if success:
                print("تم استخراج الكتاب بنجاح")
            else:
                print("فشل في استخراج الكتاب")
                
        except KeyboardInterrupt:
            print("\nتم إيقاف العملية")
            break
        except Exception as e:
            print(f"خطأ: {e}")
    
    if scraper:
        scraper.close()
    
    print("تم الخروج من البرنامج")

def print_help():
    """طباعة المساعدة"""
    help_text = """
الأوامر المتاحة:

  book <معرف_أو_رابط>     - استخراج كتاب واحد
  db <host> <user> <pass> <db> - إعداد قاعدة البيانات
  help                    - عرض هذه المساعدة
  exit                    - الخروج من البرنامج

أمثلة:
  book 123               - استخراج الكتاب رقم 123
  book https://shamela.ws/book/456 - استخراج كتاب من الرابط
  db localhost root password bms - إعداد قاعدة البيانات

ملاحظة: يمكنك كتابة معرف الكتاب أو الرابط مباشرة بدون كلمة 'book'
"""
    print(help_text)

def main():
    """الوظيفة الرئيسية"""
    parser = argparse.ArgumentParser(
        description=f"{PROJECT_NAME} - استخراج كتب المكتبة الشاملة",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
أمثلة الاستخدام:

  # استخراج كتاب واحد
  python shamela_easy_runner.py --book 123
  
  # استخراج كتاب مع حفظ HTML
  python shamela_easy_runner.py --book 123 --html
  
  # استخراج عدة كتب من ملف
  python shamela_easy_runner.py --file books.txt
  
  # استخراج مع قاعدة البيانات
  python shamela_easy_runner.py --book 123 --db-host localhost --db-user root --db-pass password --db-name bms
  
  # الوضع التفاعلي
  python shamela_easy_runner.py --interactive
"""
    )
    
    # خيارات الكتب
    book_group = parser.add_mutually_exclusive_group()
    book_group.add_argument('--book', '-b', 
                           help='معرف الكتاب أو رابطه')
    book_group.add_argument('--books', '-B', nargs='+',
                           help='قائمة معرفات أو روابط الكتب')
    book_group.add_argument('--file', '-f',
                           help='ملف يحتوي على قائمة الكتب')
    book_group.add_argument('--interactive', '-i', action='store_true',
                           help='تشغيل الوضع التفاعلي')
    
    # خيارات الحفظ
    parser.add_argument('--no-json', action='store_true',
                       help='عدم حفظ ملفات JSON')
    parser.add_argument('--no-db', action='store_true',
                       help='عدم حفظ في قاعدة البيانات')
    parser.add_argument('--html', action='store_true',
                       help='استخراج محتوى HTML للصفحات')
    parser.add_argument('--output-dir', '-o',
                       help='مجلد الحفظ (افتراضي: shamela_books)')
    
    # إعدادات قاعدة البيانات
    db_group = parser.add_argument_group('إعدادات قاعدة البيانات')
    db_group.add_argument('--db-host', default='localhost',
                         help='عنوان خادم قاعدة البيانات')
    db_group.add_argument('--db-port', type=int, default=3306,
                         help='منفذ قاعدة البيانات')
    db_group.add_argument('--db-user', default='root',
                         help='اسم مستخدم قاعدة البيانات')
    db_group.add_argument('--db-pass', default='',
                         help='كلمة مرور قاعدة البيانات')
    db_group.add_argument('--db-name', default='bms',
                         help='اسم قاعدة البيانات')
    
    # خيارات أخرى
    parser.add_argument('--verbose', '-v', action='store_true',
                       help='عرض تفاصيل أكثر')
    parser.add_argument('--version', action='version',
                       version=f'{PROJECT_NAME} {PROJECT_VERSION}')
    
    args = parser.parse_args()
    
    # إعداد مستوى السجلات
    if args.verbose:
        logging.getLogger().setLevel(logging.DEBUG)
    
    # الوضع التفاعلي
    if args.interactive:
        interactive_mode()
        return
    
    # التحقق من وجود مدخلات
    if not any([args.book, args.books, args.file]):
        parser.print_help()
        return
    
    # إعداد قاعدة البيانات
    db_config = None
    if not args.no_db:
        db_config = get_db_config({
            'host': args.db_host,
            'port': args.db_port,
            'user': args.db_user,
            'password': args.db_pass,
            'database': args.db_name
        })
    
    # إنشاء كائن الاستخراج
    scraper = ShamelaScraper(db_config)
    
    try:
        # تحديد قائمة الكتب
        books_to_process = []
        
        if args.book:
            books_to_process = [args.book]
        elif args.books:
            books_to_process = args.books
        elif args.file:
            books_to_process = load_books_from_file(args.file)
        
        if not books_to_process:
            logger.error("لا توجد كتب للمعالجة")
            return
        
        # معالجة الكتب
        if len(books_to_process) == 1:
            # كتاب واحد
            success = scraper.extract_single_book(
                book_input=books_to_process[0],
                save_to_db=not args.no_db,
                save_to_json=not args.no_json,
                extract_html=args.html,
                output_dir=args.output_dir
            )
            
            if success:
                logger.info("تمت العملية بنجاح")
            else:
                logger.error("فشلت العملية")
                sys.exit(1)
        else:
            # عدة كتب
            results = scraper.extract_multiple_books(
                book_inputs=books_to_process,
                save_to_db=not args.no_db,
                save_to_json=not args.no_json,
                extract_html=args.html,
                output_dir=args.output_dir
            )
            
            # طباعة النتائج
            successful = sum(1 for success in results.values() if success)
            total = len(results)
            
            print(f"\nملخص النتائج: {successful}/{total} كتاب تم بنجاح")
            
            # طباعة الكتب الفاشلة
            failed_books = [book for book, success in results.items() if not success]
            if failed_books:
                print("\nالكتب التي فشلت:")
                for book in failed_books:
                    print(f"  - {book}")
    
    finally:
        scraper.close()

if __name__ == '__main__':
    main()