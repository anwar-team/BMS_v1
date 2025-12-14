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
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->string('title', 500);
            $table->string('author', 255)->nullable();
            $table->string('publisher', 255)->nullable();
            $table->year('publication_year')->nullable();
            $table->string('page_reference', 100)->nullable(); // مثل: "ص 123" أو "ج2/ص45"
            $table->enum('reference_type', ['book', 'article', 'website', 'manuscript', 'hadith_collection', 'tafsir', 'fatwa'])->default('book');
            $table->string('isbn', 20)->nullable();
            $table->text('url')->nullable();
            $table->text('notes')->nullable(); // ملاحظات إضافية
            $table->string('edition', 100)->nullable(); // الطبعة
            $table->string('volume_info', 100)->nullable(); // معلومات المجلد
            $table->integer('citation_count')->default(0); // عدد مرات الاستشهاد
            $table->boolean('is_verified')->default(false); // هل المرجع محقق
            $table->timestamps();
            
            // فهارس للبحث
            $table->index(['book_id', 'reference_type']);
            $table->index(['author']);
            $table->index(['publication_year']);
            $table->fullText(['title', 'author', 'publisher']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};