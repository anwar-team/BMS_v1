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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('volume_id')->unsigned();
            $table->bigInteger('book_id')->unsigned();
            $table->string('chapter_number', 20)->nullable();
            $table->string('title', 255);
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->integer('order')->default(0);
            $table->integer('page_start')->nullable();
            $table->integer('page_end')->nullable();
            $table->enum('chapter_type', ['main', 'sub'])->default('main');
            $table->timestamps();

            $table->foreign('volume_id')->references('id')->on('volumes')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('chapters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};