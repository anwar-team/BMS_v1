<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ====================================
        // 1. Books Table - الأهم!
        // ====================================
        Schema::table('books', function (Blueprint $table) {
            // Index على book_section_id للبحث السريع حسب القسم
            if (!$this->indexExists('books', 'books_book_section_id_index')) {
                $table->index('book_section_id', 'books_book_section_id_index');
            }
            
            // Composite index للبحث حسب القسم مع الترتيب بالتاريخ
            if (!$this->indexExists('books', 'books_section_created_index')) {
                $table->index(['book_section_id', 'created_at'], 'books_section_created_index');
            }
            
            // Index على title للبحث السريع
            if (!$this->indexExists('books', 'books_title_index')) {
                $table->index('title', 'books_title_index');
            }
            
            // Index على slug للبحث بالـ slug
            // slug already has unique index, no need for additional
            
            // Index على created_at للترتيب الزمني
            if (!$this->indexExists('books', 'books_created_at_index')) {
                $table->index('created_at', 'books_created_at_index');
            }
            
            // Composite index للحالة والرؤية مع التاريخ
            if (!$this->indexExists('books', 'books_status_visibility_created_index')) {
                $table->index(['status', 'visibility', 'created_at'], 'books_status_visibility_created_index');
            }
        });

        // ====================================
        // 2. Authors Table
        // ====================================
        Schema::table('authors', function (Blueprint $table) {
            // Index على full_name للبحث السريع
            // Already has unique index from previous migration
            
            // Index على created_at
            if (!$this->indexExists('authors', 'authors_created_at_index')) {
                $table->index('created_at', 'authors_created_at_index');
            }
            
            // Index على death_date للمؤلفين المتوفين
            if (!$this->indexExists('authors', 'authors_death_date_index')) {
                $table->index('death_date', 'authors_death_date_index');
            }
        });

        // ====================================
        // 3. Author_Book Table - مهم جداً!
        // ====================================
        Schema::table('author_book', function (Blueprint $table) {
            // Index على author_id (إن لم يكن موجود)
            if (!$this->indexExists('author_book', 'author_book_author_id_index')) {
                $table->index('author_id', 'author_book_author_id_index');
            }
            
            // Index على book_id (إن لم يكن موجود)
            if (!$this->indexExists('author_book', 'author_book_book_id_index')) {
                $table->index('book_id', 'author_book_book_id_index');
            }
            
            // Composite index للبحث السريع عن مؤلف + is_main
            if (!$this->indexExists('author_book', 'author_book_author_main_index')) {
                $table->index(['author_id', 'is_main'], 'author_book_author_main_index');
            }
            
            // Index على display_order للترتيب
            if (!$this->indexExists('author_book', 'author_book_display_order_index')) {
                $table->index('display_order', 'author_book_display_order_index');
            }
        });

        // ====================================
        // 4. Book Sections Table
        // ====================================
        Schema::table('book_sections', function (Blueprint $table) {
            // Index على name للبحث
            if (!$this->indexExists('book_sections', 'book_sections_name_index')) {
                $table->index('name', 'book_sections_name_index');
            }
        });

        // ====================================
        // 5. Publishers Table (إن وجد)
        // ====================================
        if (Schema::hasTable('publishers')) {
            Schema::table('publishers', function (Blueprint $table) {
                // Index على name للبحث
                if (!$this->indexExists('publishers', 'publishers_name_index')) {
                    $table->index('name', 'publishers_name_index');
                }
            });
        }

        // ====================================
        // 6. Full-Text Indexes للبحث النصي
        // ====================================
        // ملاحظة: Full-Text يحتاج MyISAM أو InnoDB مع MySQL 5.6+
        // سنستخدم Raw SQL لأن Laravel لا يدعمها بشكل كامل
        
        try {
            // Full-text على books.title
            DB::statement('ALTER TABLE books ADD FULLTEXT INDEX books_title_fulltext (title)');
        } catch (\Exception $e) {
            // Index موجود مسبقاً أو Database لا يدعم Full-text
        }
        
        try {
            // Full-text على books.description
            DB::statement('ALTER TABLE books ADD FULLTEXT INDEX books_description_fulltext (description)');
        } catch (\Exception $e) {
            // Index موجود مسبقاً
        }
        
        try {
            // Full-text مُركّب على title و description معاً
            DB::statement('ALTER TABLE books ADD FULLTEXT INDEX books_title_description_fulltext (title, description)');
        } catch (\Exception $e) {
            // Index موجود مسبقاً
        }
    }

    /**
     * التحقق من وجود Index
     */
    private function indexExists($table, $index): bool
    {
        $connection = Schema::getConnection();
        $dbSchemaManager = $connection->getDoctrineSchemaManager();
        $doctrineTable = $dbSchemaManager->listTableDetails($table);
        
        return $doctrineTable->hasIndex($index);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Books table
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex('books_book_section_id_index');
            $table->dropIndex('books_section_created_index');
            $table->dropIndex('books_title_index');
            $table->dropIndex('books_created_at_index');
            $table->dropIndex('books_status_visibility_created_index');
        });

        // Authors table
        Schema::table('authors', function (Blueprint $table) {
            $table->dropIndex('authors_created_at_index');
            $table->dropIndex('authors_death_date_index');
        });

        // Author_Book table
        Schema::table('author_book', function (Blueprint $table) {
            $table->dropIndex('author_book_author_id_index');
            $table->dropIndex('author_book_book_id_index');
            $table->dropIndex('author_book_author_main_index');
            $table->dropIndex('author_book_display_order_index');
        });

        // Book Sections table
        Schema::table('book_sections', function (Blueprint $table) {
            $table->dropIndex('book_sections_name_index');
        });

        // Publishers table
        if (Schema::hasTable('publishers')) {
            Schema::table('publishers', function (Blueprint $table) {
                $table->dropIndex('publishers_name_index');
            });
        }

        // Drop Full-text indexes
        try {
            DB::statement('ALTER TABLE books DROP INDEX books_title_fulltext');
        } catch (\Exception $e) {
            // Index لا يوجد
        }
        
        try {
            DB::statement('ALTER TABLE books DROP INDEX books_description_fulltext');
        } catch (\Exception $e) {
            // Index لا يوجد
        }
        
        try {
            DB::statement('ALTER TABLE books DROP INDEX books_title_description_fulltext');
        } catch (\Exception $e) {
            // Index لا يوجد
        }
    }
};
