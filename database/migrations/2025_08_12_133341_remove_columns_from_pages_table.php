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
            $table->dropColumn([
                'shamela_page_id',
                'content_hash',
                'content_type',
                'word_count',
                'character_count',
                'has_images',
                'has_footnotes',
                'has_tables',
                'formatting_info',
                'plain_text',
                'reading_time_minutes',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Re-add the dropped columns with their original types
            $table->string('shamela_page_id')->nullable();
            $table->string('content_hash')->nullable();
            $table->string('content_type')->nullable();
            $table->integer('word_count')->nullable();
            $table->integer('character_count')->nullable();
            $table->boolean('has_images')->default(false);
            $table->boolean('has_footnotes')->default(false);
            $table->boolean('has_tables')->default(false);
            $table->json('formatting_info')->nullable();
            $table->longText('plain_text')->nullable();
            $table->integer('reading_time_minutes')->nullable();
        });
    }
};
