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
        Schema::create('book_indexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('page_id')->nullable()->constrained('pages')->onDelete('cascade');
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->onDelete('set null');
            $table->foreignId('volume_id')->nullable()->constrained('volumes')->onDelete('set null');
            $table->string('keyword', 255);
            $table->string('normalized_keyword', 255); // الكلمة بعد التطبيع
            $table->integer('page_number');
            $table->text('context')->nullable();          // السياق المحيط بالكلمة
            $table->integer('position_in_page')->nullable();  // موقع الكلمة في الصفحة
            $table->integer('frequency')->default(1);
            $table->enum('index_type', ['keyword', 'person', 'place', 'concept', 'hadith', 'verse'])->default('keyword');
            $table->float('relevance_score', 3, 2)->default(1.00); // درجة الأهمية
            $table->boolean('is_auto_generated')->default(true);
            $table->timestamps();
            
            // فهارس للبحث السريع
            $table->index(['book_id', 'keyword']);
            $table->index(['normalized_keyword']);
            $table->index(['page_number', 'book_id']);
            $table->index(['index_type', 'book_id']);
            $table->fullText(['keyword', 'context']); // فهرس النص الكامل
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_indexes');
    }
};