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
        Schema::table('books', function (Blueprint $table) {
            $table->string('language', 10)->default('ar')->after('description'); // ISO 639-1
            $table->string('isbn', 20)->nullable()->after('language');
            $table->string('edition', 100)->nullable()->after('isbn');
            $table->enum('original_format', ['bok', 'pdf', 'doc', 'html', 'txt', 'epub', 'other'])->nullable()->after('edition');
            $table->enum('content_rating', ['general', 'scholarly', 'advanced', 'specialized'])->default('general')->after('original_format');
            $table->integer('estimated_reading_time')->nullable()->after('content_rating'); // بالدقائق
            $table->integer('total_word_count')->default(0)->after('estimated_reading_time');
            $table->integer('total_character_count')->default(0)->after('total_word_count');
            $table->boolean('has_footnotes')->default(false)->after('total_character_count');
            $table->boolean('has_indexes')->default(false)->after('has_footnotes');
            $table->boolean('has_references')->default(false)->after('has_indexes');
            $table->json('subjects')->nullable()->after('has_references'); // المواضيع الفرعية
            $table->json('keywords')->nullable()->after('subjects'); // الكلمات المفتاحية
            $table->text('abstract')->nullable()->after('keywords'); // ملخص الكتاب
            $table->string('doi', 100)->nullable()->after('abstract'); // Digital Object Identifier
            $table->date('hijri_date')->nullable()->after('doi'); // التاريخ الهجري
            $table->string('manuscript_info', 500)->nullable()->after('hijri_date'); // معلومات المخطوط
            
            // فهارس جديدة
            $table->index(['language']);
            $table->index(['original_format']);
            $table->index(['content_rating']);
            $table->index(['has_footnotes']);
            $table->index(['has_indexes']);
            $table->index(['has_references']);
            $table->index(['hijri_date']);
            $table->fullText(['abstract']); // فهرس النص الكامل للملخص
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['language']);
            $table->dropIndex(['original_format']);
            $table->dropIndex(['content_rating']);
            $table->dropIndex(['has_footnotes']);
            $table->dropIndex(['has_indexes']);
            $table->dropIndex(['has_references']);
            $table->dropIndex(['hijri_date']);
            $table->dropFullText(['abstract']);
            
            $table->dropColumn([
                'language',
                'isbn',
                'edition',
                'original_format',
                'content_rating',
                'estimated_reading_time',
                'total_word_count',
                'total_character_count',
                'has_footnotes',
                'has_indexes',
                'has_references',
                'subjects',
                'keywords',
                'abstract',
                'doi',
                'hijri_date',
                'manuscript_info'
            ]);
        });
    }
};