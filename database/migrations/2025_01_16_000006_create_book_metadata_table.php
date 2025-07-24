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
        Schema::create('book_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->string('metadata_key', 100);
            $table->text('metadata_value');
            $table->enum('metadata_type', ['dublin_core', 'custom', 'shamela_specific', 'islamic_metadata'])->default('custom');
            $table->string('data_type', 50)->default('string'); // string, number, date, boolean, json
            $table->text('description')->nullable();
            $table->boolean('is_searchable')->default(true);
            $table->boolean('is_public')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // فهارس
            $table->index(['book_id', 'metadata_key']);
            $table->index(['metadata_type']);
            $table->index(['is_searchable', 'is_public']);
            $table->unique(['book_id', 'metadata_key']); // منع التكرار
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_metadata');
    }
};