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
        Schema::table('book_sections', function (Blueprint $table) {
            $table->integer('icon_custom_size')->nullable()->after('icon_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_sections', function (Blueprint $table) {
            $table->dropColumn('icon_custom_size');
        });
    }
};
