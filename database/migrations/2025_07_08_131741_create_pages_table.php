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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('book_id')->unsigned();
            $table->bigInteger('volume_id')->unsigned()->nullable();
            $table->bigInteger('chapter_id')->unsigned()->nullable();
            $table->integer('page_number');
            $table->longText('content')->nullable();
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('volume_id')->references('id')->on('volumes')->onDelete('cascade');
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('set null');
            
            // منع التكرار لرقم الصفحة في نفس الكتاب
            $table->unique(['book_id', 'page_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
