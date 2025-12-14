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
        Schema::create('book_extracted_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            
            // ✅ معلومات القسم المستخرجة (الأولوية الأولى)
            $table->string('extracted_section_name', 500)->nullable();
            $table->unsignedBigInteger('matched_section_id')->nullable();
            $table->decimal('section_match_confidence', 3, 2)->default(0.00);
            
            // ✅ معلومات المؤلف المستخرجة
            $table->string('extracted_author_name', 500)->nullable();
            $table->string('extracted_author_death_year', 20)->nullable();
            $table->string('extracted_author_madhhab', 100)->nullable();
            $table->unsignedBigInteger('matched_author_id')->nullable();
            $table->decimal('author_match_confidence', 3, 2)->default(0.00);
            
            // ✅ معلومات الناشر المستخرجة
            $table->string('extracted_publisher_name', 500)->nullable();
            $table->string('extracted_publisher_city', 200)->nullable();
            $table->unsignedBigInteger('matched_publisher_id')->nullable();
            $table->decimal('publisher_match_confidence', 3, 2)->default(0.00);
            
            // ✅ معلومات الطبعة المستخرجة
            $table->string('extracted_edition', 200)->nullable();
            $table->string('extracted_edition_number', 50)->nullable();
            $table->string('extracted_year_hijri', 10)->nullable();
            $table->string('extracted_year_miladi', 10)->nullable();
            
            // ✅ معلومات التحقيق
            $table->string('extracted_tahqeeq_name', 500)->nullable();
            $table->unsignedBigInteger('matched_tahqeeq_author_id')->nullable();
            
            // ✅ معلومات إضافية
            $table->integer('extracted_pages_count')->nullable();
            $table->integer('extracted_volumes_count')->nullable();
            
            // ✅ حالة المعالجة
            $table->boolean('is_processed')->default(false);
            $table->boolean('is_applied')->default(false);
            $table->boolean('needs_review')->default(true);
            $table->enum('processing_status', ['pending', 'extracted', 'matched', 'applied', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            
            // ✅ التواريخ
            $table->timestamp('extracted_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
            
            // ✅ الفهارس
            $table->unique('book_id');
            $table->index('processing_status');
            $table->index('needs_review');
            $table->index('is_applied');
            
            // ✅ Foreign Keys
            $table->foreign('matched_section_id')->references('id')->on('book_sections')->onDelete('set null');
            $table->foreign('matched_author_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('matched_publisher_id')->references('id')->on('publishers')->onDelete('set null');
            $table->foreign('matched_tahqeeq_author_id')->references('id')->on('authors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_extracted_metadata');
    }
};
