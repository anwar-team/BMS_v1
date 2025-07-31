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
        Schema::table('pages', function (Blueprint $table) {
            $table->enum('content_type', ['text', 'html', 'markdown', 'mixed'])->default('text')->after('content');
            $table->string('content_hash', 64)->nullable()->after('content_type'); // SHA256 hash
            $table->integer('word_count')->default(0)->after('content_hash');
            $table->integer('character_count')->default(0)->after('word_count');
            $table->boolean('has_footnotes')->default(false)->after('character_count');
            $table->boolean('has_images')->default(false)->after('has_footnotes');
            $table->boolean('has_tables')->default(false)->after('has_images');
            $table->json('formatting_info')->nullable()->after('has_tables'); // معلومات التنسيق
            $table->text('plain_text')->nullable()->after('formatting_info'); // النص بدون تنسيق للبحث
            $table->float('reading_time_minutes', 5, 2)->nullable()->after('plain_text'); // وقت القراءة المقدر
            
            // فهارس جديدة
            $table->index(['content_type']);
            $table->index(['word_count']);
            $table->index(['has_footnotes']);
            $table->index(['content_hash']);
            $table->fullText(['plain_text']); // فهرس النص الكامل
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['content_type']);
            $table->dropIndex(['word_count']);
            $table->dropIndex(['has_footnotes']);
            $table->dropIndex(['content_hash']);
            $table->dropFullText(['plain_text']);
            
            $table->dropColumn([
                'content_type',
                'content_hash',
                'word_count',
                'character_count',
                'has_footnotes',
                'has_images',
                'has_tables',
                'formatting_info',
                'plain_text',
                'reading_time_minutes'
            ]);
        });
    }
};