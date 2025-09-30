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
            $table->dropColumn([
                'chapter_number',
                'arabic_number',
                'is_conclusion',
                'is_introduction',
                'is_appendix',
                'description',
                'section_type',
                'word_count',
                'has_subsections',
                'metadata',
                'chapter_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            // Re-add the dropped columns with their original types
            $table->integer('chapter_number')->nullable();
            $table->string('arabic_number')->nullable();
            $table->boolean('is_conclusion')->default(false);
            $table->boolean('is_introduction')->default(false);
            $table->boolean('is_appendix')->default(false);
            $table->text('description')->nullable();
            $table->string('section_type')->nullable();
            $table->integer('word_count')->nullable();
            $table->boolean('has_subsections')->default(false);
            $table->json('metadata')->nullable();
            $table->string('chapter_type')->nullable();
        });
    }
};
