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
        Schema::table('chapters', function (Blueprint $table) {
            $table->integer('level')->default(1)->after('parent_id'); // مستوى الفصل في الهيكل الهرمي
            $table->string('section_type', 50)->default('chapter')->after('level'); // نوع القسم
            $table->text('description')->nullable()->after('title');
            $table->integer('word_count')->default(0)->after('page_end');
            $table->integer('estimated_reading_time')->nullable()->after('word_count'); // بالدقائق
            $table->boolean('has_subsections')->default(false)->after('estimated_reading_time');
            $table->json('metadata')->nullable()->after('has_subsections'); // بيانات إضافية
            $table->string('arabic_number', 50)->nullable()->after('chapter_number'); // الترقيم العربي
            $table->boolean('is_appendix')->default(false)->after('arabic_number');
            $table->boolean('is_introduction')->default(false)->after('is_appendix');
            $table->boolean('is_conclusion')->default(false)->after('is_introduction');
            
            // فهارس جديدة
            $table->index(['level']);
            $table->index(['section_type']);
            $table->index(['has_subsections']);
            $table->index(['is_appendix']);
            $table->index(['is_introduction']);
            $table->index(['is_conclusion']);
            $table->index(['book_id', 'level', 'order']); // فهرس مركب للهيكل الهرمي
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropIndex(['level']);
            $table->dropIndex(['section_type']);
            $table->dropIndex(['has_subsections']);
            $table->dropIndex(['is_appendix']);
            $table->dropIndex(['is_introduction']);
            $table->dropIndex(['is_conclusion']);
            $table->dropIndex(['book_id', 'level', 'order']);
            
            $table->dropColumn([
                'level',
                'section_type',
                'description',
                'word_count',
                'estimated_reading_time',
                'has_subsections',
                'metadata',
                'arabic_number',
                'is_appendix',
                'is_introduction',
                'is_conclusion'
            ]);
        });
    }
};