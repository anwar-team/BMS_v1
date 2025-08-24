<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // إضافة فهارس لجدول الكتب (فقط الجديدة)
        Schema::table('books', function (Blueprint $table) {
            // التحقق من عدم وجود الفهرس قبل إنشاؤه
            if (!$this->indexExists('books', 'books_publisher_id_index')) {
                $table->index(['publisher_id']); // فهرس للناشر
            }
            if (!$this->indexExists('books', 'books_status_visibility_index')) {
                $table->index(['status', 'visibility']); // فهرس مركب للحالة والرؤية
            }
        });

        // إضافة فهارس لجدول المجلدات
        Schema::table('volumes', function (Blueprint $table) {
            if (!$this->indexExists('volumes', 'volumes_book_id_number_index')) {
                $table->index(['book_id', 'number']); // فهرس مركب للكتاب ورقم المجلد
            }
            if (!$this->indexExists('volumes', 'volumes_book_id_created_at_index')) {
                $table->index(['book_id', 'created_at']); // فهرس مركب للكتاب وتاريخ الإنشاء
            }
        });

        // إضافة فهارس لجدول الفصول
        Schema::table('chapters', function (Blueprint $table) {
            if (!$this->indexExists('chapters', 'chapters_volume_id_order_index')) {
                $table->index(['volume_id', 'order']); // فهرس مركب للمجلد والترتيب
            }
            if (!$this->indexExists('chapters', 'chapters_book_id_order_index')) {
                $table->index(['book_id', 'order']); // فهرس مركب للكتاب والترتيب
            }
            if (!$this->indexExists('chapters', 'chapters_book_id_parent_id_index')) {
                $table->index(['book_id', 'parent_id']); // فهرس مركب للكتاب والفصل الأب
            }
        });

        // إضافة فهارس لجدول الصفحات
        Schema::table('pages', function (Blueprint $table) {
            if (!$this->indexExists('pages', 'pages_chapter_id_page_number_index')) {
                $table->index(['chapter_id', 'page_number']); // فهرس مركب للفصل ورقم الصفحة
            }
            if (!$this->indexExists('pages', 'pages_volume_id_page_number_index')) {
                $table->index(['volume_id', 'page_number']); // فهرس مركب للمجلد ورقم الصفحة
            }
            if (!$this->indexExists('pages', 'pages_book_id_created_at_index')) {
                $table->index(['book_id', 'created_at']); // فهرس مركب للكتاب وتاريخ الإنشاء
            }
        });

        // إضافة فهارس لجدول علاقة المؤلفين والكتب
        Schema::table('author_book', function (Blueprint $table) {
            if (!$this->indexExists('author_book', 'author_book_book_id_is_main_index')) {
                $table->index(['book_id', 'is_main']); // فهرس مركب للكتاب والمؤلف الرئيسي
            }
            if (!$this->indexExists('author_book', 'author_book_author_id_role_index')) {
                $table->index(['author_id', 'role']); // فهرس مركب للمؤلف والدور
            }
        });
    }

    /**
     * التحقق من وجود فهرس
     */
    private function indexExists($table, $index)
    {
        return Schema::hasIndex($table, $index);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف فهارس جدول الكتب
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['publisher_id']);
            $table->dropIndex(['status', 'visibility']);
        });

        // حذف فهارس جدول المجلدات
        Schema::table('volumes', function (Blueprint $table) {
            $table->dropIndex(['book_id', 'number']);
            $table->dropIndex(['book_id', 'created_at']);
        });

        // حذف فهارس جدول الفصول
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropIndex(['volume_id', 'order']);
            $table->dropIndex(['book_id', 'order']);
            $table->dropIndex(['book_id', 'parent_id']);
        });

        // حذف فهارس جدول الصفحات
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['chapter_id', 'page_number']);
            $table->dropIndex(['volume_id', 'page_number']);
            $table->dropIndex(['book_id', 'created_at']);
        });

        // حذف فهارس جدول علاقة المؤلفين والكتب
        Schema::table('author_book', function (Blueprint $table) {
            $table->dropIndex(['book_id', 'is_main']);
            $table->dropIndex(['author_id', 'role']);
        });
    }
};
