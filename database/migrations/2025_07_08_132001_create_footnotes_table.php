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
        Schema::create('footnotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('page_id')->nullable()->constrained('pages')->onDelete('cascade');
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->onDelete('set null');
            $table->foreignId('volume_id')->nullable()->constrained('volumes')->onDelete('set null');
            $table->integer('footnote_number')->nullable();
            $table->longText('content');
            $table->text('position_in_page')->nullable(); // موقع الهامش في الصفحة
            $table->text('reference_text')->nullable();   // النص المرجعي
            $table->enum('type', ['footnote', 'endnote', 'margin_note', 'commentary'])->default('footnote');
            $table->integer('order_in_page')->default(0);
            $table->boolean('is_original')->default(true); // هل الهامش من النص الأصلي أم مضاف
            $table->timestamps();
            
            // فهارس للأداء
            $table->index(['book_id', 'page_id']);
            $table->index(['chapter_id', 'type']);
            $table->index(['footnote_number', 'page_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footnotes');
    }
};