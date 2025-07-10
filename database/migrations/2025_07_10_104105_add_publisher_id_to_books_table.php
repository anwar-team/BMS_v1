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
        Schema::table('books', function (Blueprint $table) {
            // إضافة عمود publisher_id مع foreign key إلى جدول publishers
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // حذف foreign key وعمود publisher_id
            $table->dropForeign(['publisher_id']);
            $table->dropColumn('publisher_id');
        });
    }
};
