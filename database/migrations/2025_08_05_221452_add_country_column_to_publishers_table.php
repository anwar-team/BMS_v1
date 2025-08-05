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
        Schema::table('publishers', function (Blueprint $table) {
            if (!Schema::hasColumn('publishers', 'country')) {
                $table->string('country')->nullable()->default('')->after('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publishers', function (Blueprint $table) {
            if (Schema::hasColumn('publishers', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
