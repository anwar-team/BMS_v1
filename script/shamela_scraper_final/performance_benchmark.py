#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
نظام قياس الأداء المتقدم لمقارنة سرعة استخراج البيانات
بين النسخة الأصلية والمحسنة من مكشطة الشاملة
"""

import time
import json
import subprocess
import sys
import os
from datetime import datetime
from typing import Dict, List, Tuple, Any
import statistics

class PerformanceBenchmark:
    def __init__(self):
        self.results = {
            'original': [],
            'optimized': [],
            'comparison': {}
        }
        self.test_books = [
            {'id': 43, 'name': 'كتاب اختبار 43'},
            {'id': 100, 'name': 'كتاب اختبار 100'},
            {'id': 200, 'name': 'كتاب اختبار 200'}
        ]
        self.max_pages_options = [5, 10, 20, 50]
        
    def run_scraper(self, script_name: str, book_id: int, max_pages: int, output_file: str) -> Dict[str, Any]:
        """تشغيل المكشطة وقياس الأداء"""
        start_time = time.time()
        
        try:
            # تشغيل المكشطة
            cmd = [
                'python', script_name,
                '--book-id', str(book_id),
                '--max-pages', str(max_pages),
                '--output', output_file
            ]
            
            process = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                encoding='utf-8',
                timeout=300  # 5 دقائق كحد أقصى
            )
            
            end_time = time.time()
            execution_time = end_time - start_time
            
            # تحليل النتائج
            success = process.returncode == 0
            
            # قراءة الملف المُنتج للحصول على إحصائيات
            pages_extracted = 0
            file_size = 0
            
            if success and os.path.exists(output_file):
                file_size = os.path.getsize(output_file)
                try:
                    with open(output_file, 'r', encoding='utf-8') as f:
                        data = json.load(f)
                        pages_extracted = len(data.get('pages', []))
                except:
                    pass
            
            return {
                'success': success,
                'execution_time': execution_time,
                'pages_extracted': pages_extracted,
                'file_size': file_size,
                'pages_per_second': pages_extracted / execution_time if execution_time > 0 else 0,
                'stdout': process.stdout,
                'stderr': process.stderr,
                'book_id': book_id,
                'max_pages': max_pages,
                'timestamp': datetime.now().isoformat()
            }
            
        except subprocess.TimeoutExpired:
            return {
                'success': False,
                'execution_time': 300,
                'pages_extracted': 0,
                'file_size': 0,
                'pages_per_second': 0,
                'error': 'انتهت مهلة التنفيذ (5 دقائق)',
                'book_id': book_id,
                'max_pages': max_pages,
                'timestamp': datetime.now().isoformat()
            }
        except Exception as e:
            return {
                'success': False,
                'execution_time': 0,
                'pages_extracted': 0,
                'file_size': 0,
                'pages_per_second': 0,
                'error': str(e),
                'book_id': book_id,
                'max_pages': max_pages,
                'timestamp': datetime.now().isoformat()
            }
    
    def compare_outputs(self, original_file: str, optimized_file: str) -> Dict[str, Any]:
        """مقارنة مخرجات الملفين للتأكد من دقة البيانات"""
        try:
            # تشغيل سكريبت المقارنة
            result = subprocess.run(
                ['python', 'compare_files.py'],
                capture_output=True,
                text=True,
                encoding='utf-8',
                cwd=os.getcwd()
            )
            
            # تحليل النتيجة
            files_identical = result.returncode == 0
            
            return {
                'files_identical': files_identical,
                'comparison_output': result.stdout,
                'comparison_errors': result.stderr
            }
            
        except Exception as e:
            return {
                'files_identical': False,
                'comparison_output': '',
                'comparison_errors': f'خطأ في المقارنة: {str(e)}'
            }
    
    def run_benchmark_suite(self) -> None:
        """تشغيل مجموعة اختبارات الأداء الكاملة"""
        print("🚀 بدء اختبارات الأداء المتقدمة...")
        print("=" * 60)
        
        total_tests = len(self.test_books) * len(self.max_pages_options)
        current_test = 0
        
        for book in self.test_books:
            for max_pages in self.max_pages_options:
                current_test += 1
                print(f"\n📊 اختبار {current_test}/{total_tests}: كتاب {book['id']} - {max_pages} صفحة")
                print("-" * 50)
                
                # ملفات الإخراج
                original_output = f"benchmark_original_{book['id']}_{max_pages}.json"
                optimized_output = f"benchmark_optimized_{book['id']}_{max_pages}.json"
                
                # اختبار النسخة الأصلية
                print("⏱️  اختبار النسخة الأصلية...")
                original_result = self.run_scraper(
                    'enhanced_shamela_scraper.py',
                    book['id'],
                    max_pages,
                    original_output
                )
                self.results['original'].append(original_result)
                
                if original_result['success']:
                    print(f"✅ النسخة الأصلية: {original_result['execution_time']:.2f}ث - {original_result['pages_extracted']} صفحة")
                else:
                    print(f"❌ فشل النسخة الأصلية: {original_result.get('error', 'خطأ غير معروف')}")
                
                # اختبار النسخة المحسنة
                print("⚡ اختبار النسخة المحسنة...")
                optimized_result = self.run_scraper(
                    'enhanced_shamela_scraper_optimized.py',
                    book['id'],
                    max_pages,
                    optimized_output
                )
                self.results['optimized'].append(optimized_result)
                
                if optimized_result['success']:
                    print(f"✅ النسخة المحسنة: {optimized_result['execution_time']:.2f}ث - {optimized_result['pages_extracted']} صفحة")
                else:
                    print(f"❌ فشل النسخة المحسنة: {optimized_result.get('error', 'خطأ غير معروف')}")
                
                # مقارنة النتائج إذا نجح كلا الاختبارين
                if original_result['success'] and optimized_result['success']:
                    # مقارنة دقة البيانات
                    print("🔍 مقارنة دقة البيانات...")
                    
                    # تحديث ملفات المقارنة مؤقتاً
                    self.update_compare_script_files(original_output, optimized_output)
                    
                    comparison = self.compare_outputs(original_output, optimized_output)
                    
                    # حساب تحسن الأداء
                    speed_improvement = (
                        (original_result['execution_time'] - optimized_result['execution_time']) / 
                        original_result['execution_time'] * 100
                    )
                    
                    print(f"📈 تحسن السرعة: {speed_improvement:+.1f}%")
                    print(f"🎯 دقة البيانات: {'✅ متطابقة' if comparison['files_identical'] else '❌ مختلفة'}")
                    
                    # إضافة النتائج للمقارنة
                    test_key = f"{book['id']}_{max_pages}"
                    self.results['comparison'][test_key] = {
                        'original_time': original_result['execution_time'],
                        'optimized_time': optimized_result['execution_time'],
                        'speed_improvement': speed_improvement,
                        'data_accuracy': comparison['files_identical'],
                        'pages_extracted': original_result['pages_extracted']
                    }
                
                # تنظيف الملفات المؤقتة
                for temp_file in [original_output, optimized_output]:
                    if os.path.exists(temp_file):
                        try:
                            os.remove(temp_file)
                        except:
                            pass
        
        # إنتاج التقرير النهائي
        self.generate_final_report()
    
    def update_compare_script_files(self, file1: str, file2: str) -> None:
        """تحديث أسماء الملفات في سكريبت المقارنة مؤقتاً"""
        try:
            # قراءة سكريبت المقارنة
            with open('compare_files.py', 'r', encoding='utf-8') as f:
                content = f.read()
            
            # استبدال أسماء الملفات
            content = content.replace(
                'file1 = "original_test_43.json"',
                f'file1 = "{file1}"'
            )
            content = content.replace(
                'file2 = "optimized_test_43.json"',
                f'file2 = "{file2}"'
            )
            
            # كتابة الملف المؤقت
            with open('compare_files_temp.py', 'w', encoding='utf-8') as f:
                f.write(content)
            
            # تشغيل النسخة المؤقتة
            subprocess.run(['python', 'compare_files_temp.py'], capture_output=True)
            
            # حذف الملف المؤقت
            if os.path.exists('compare_files_temp.py'):
                os.remove('compare_files_temp.py')
                
        except Exception as e:
            print(f"تحذير: لم يتم تحديث سكريبت المقارنة: {e}")
    
    def generate_final_report(self) -> None:
        """إنتاج التقرير النهائي لنتائج الأداء"""
        print("\n" + "=" * 60)
        print("📋 التقرير النهائي لاختبارات الأداء")
        print("=" * 60)
        
        # إحصائيات عامة
        successful_original = [r for r in self.results['original'] if r['success']]
        successful_optimized = [r for r in self.results['optimized'] if r['success']]
        
        print(f"\n📊 الإحصائيات العامة:")
        print(f"   الاختبارات الناجحة (الأصلية): {len(successful_original)}/{len(self.results['original'])}")
        print(f"   الاختبارات الناجحة (المحسنة): {len(successful_optimized)}/{len(self.results['optimized'])}")
        
        if successful_original and successful_optimized:
            # متوسط الأوقات
            avg_original = statistics.mean([r['execution_time'] for r in successful_original])
            avg_optimized = statistics.mean([r['execution_time'] for r in successful_optimized])
            overall_improvement = (avg_original - avg_optimized) / avg_original * 100
            
            print(f"\n⏱️  متوسط أوقات التنفيذ:")
            print(f"   النسخة الأصلية: {avg_original:.2f} ثانية")
            print(f"   النسخة المحسنة: {avg_optimized:.2f} ثانية")
            print(f"   التحسن الإجمالي: {overall_improvement:+.1f}%")
            
            # متوسط الصفحات في الثانية
            avg_pps_original = statistics.mean([r['pages_per_second'] for r in successful_original if r['pages_per_second'] > 0])
            avg_pps_optimized = statistics.mean([r['pages_per_second'] for r in successful_optimized if r['pages_per_second'] > 0])
            
            print(f"\n📄 متوسط الصفحات في الثانية:")
            print(f"   النسخة الأصلية: {avg_pps_original:.2f} صفحة/ثانية")
            print(f"   النسخة المحسنة: {avg_pps_optimized:.2f} صفحة/ثانية")
            print(f"   تحسن الإنتاجية: {((avg_pps_optimized - avg_pps_original) / avg_pps_original * 100):+.1f}%")
        
        # دقة البيانات
        accurate_tests = sum(1 for comp in self.results['comparison'].values() if comp['data_accuracy'])
        total_comparisons = len(self.results['comparison'])
        
        print(f"\n🎯 دقة البيانات:")
        print(f"   الاختبارات المتطابقة: {accurate_tests}/{total_comparisons}")
        print(f"   معدل الدقة: {(accurate_tests/total_comparisons*100 if total_comparisons > 0 else 0):.1f}%")
        
        # حفظ التقرير التفصيلي
        report_file = f"performance_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(self.results, f, ensure_ascii=False, indent=2)
        
        print(f"\n💾 تم حفظ التقرير التفصيلي في: {report_file}")
        
        # توصيات
        print(f"\n💡 التوصيات:")
        if overall_improvement > 0:
            print(f"   ✅ النسخة المحسنة تظهر تحسناً في الأداء بنسبة {overall_improvement:.1f}%")
        else:
            print(f"   ⚠️  النسخة المحسنة لم تحقق تحسناً ملحوظاً في الأداء")
        
        if accurate_tests == total_comparisons:
            print(f"   ✅ جميع البيانات المستخرجة متطابقة - منطق الاستخراج محفوظ")
        else:
            print(f"   ❌ هناك اختلافات في البيانات - يجب مراجعة منطق الاستخراج")

def main():
    """الدالة الرئيسية"""
    benchmark = PerformanceBenchmark()
    
    try:
        benchmark.run_benchmark_suite()
    except KeyboardInterrupt:
        print("\n⏹️  تم إيقاف الاختبار بواسطة المستخدم")
    except Exception as e:
        print(f"\n❌ خطأ في تشغيل الاختبار: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()