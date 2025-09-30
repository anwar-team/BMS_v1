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
        Schema::create('page_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->onDelete('cascade');
            $table->foreignId('reference_id')->constrained('references')->onDelete('cascade');
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->onDelete('set null');
            $table->text('citation_text')->nullable(); // النص المقتبس
            $table->integer('position_in_page')->nullable();
            $table->enum('citation_type', ['direct_quote', 'paraphrase', 'reference', 'see_also'])->default('reference');
            $table->text('context')->nullable(); // السياق المحيط بالاستشهاد
            $table->boolean('is_primary_source')->default(false);
            $table->timestamps();
            
            // فهارس
            $table->index(['page_id', 'reference_id']);
            $table->index(['citation_type']);
            $table->unique(['page_id', 'reference_id', 'position_in_page']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_references');
    }
};