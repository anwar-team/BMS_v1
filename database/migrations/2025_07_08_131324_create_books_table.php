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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('slug', 200)->unique();
            $table->string('cover_image', 255)->nullable();
            $table->year('published_year')->nullable();
            $table->string('publisher', 200)->nullable();
            $table->integer('pages_count')->nullable();
            $table->integer('volumes_count')->default(1)->nullable();
            $table->enum('status', ['draft', 'review', 'published', 'archived'])->default('draft');
            $table->enum('visibility', ['public', 'private', 'restricted'])->default('public');
            //$table->string('cover_image_url', 500)->nullable();
            $table->string('source_url', 255)->nullable();
            $table->bigInteger('book_section_id')->unsigned()->nullable();
            $table->unsignedBigInteger('publisher_id')->nullable();
            $table->timestamps();

            $table->foreign('book_section_id')->references('id')->on('book_sections')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};