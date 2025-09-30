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
            $table->dropColumn([
                'isbn',
                'original_format',
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
                'manuscript_info',
                'published_year',
                'publisher',
                'categories',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('isbn')->nullable();
            $table->string('original_format')->nullable();
            $table->integer('estimated_reading_time')->nullable();
            $table->integer('total_word_count')->nullable();
            $table->integer('total_character_count')->nullable();
            $table->boolean('has_footnotes')->default(false);
            $table->boolean('has_indexes')->default(false);
            $table->boolean('has_references')->default(false);
            $table->text('subjects')->nullable();
            $table->text('keywords')->nullable();
            $table->text('abstract')->nullable();
            $table->string('doi')->nullable();
            $table->string('hijri_date')->nullable();
            $table->text('manuscript_info')->nullable();
            $table->integer('published_year')->nullable();
            $table->string('publisher')->nullable();
            $table->string('categories')->nullable();
        });
    }
};
