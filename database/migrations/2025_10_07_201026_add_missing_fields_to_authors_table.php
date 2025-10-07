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
        Schema::table('authors', function (Blueprint $table) {
            $table->boolean('is_living')->default(false)->after('madhhab');
            $table->enum('birth_year_type', ['gregorian', 'hijri'])->default('gregorian')->after('is_living');
            $table->integer('birth_year')->nullable()->after('birth_year_type');
            $table->enum('death_year_type', ['gregorian', 'hijri'])->default('gregorian')->nullable()->after('birth_year');
            $table->integer('death_year')->nullable()->after('death_year_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn([
                'is_living',
                'birth_year_type',
                'birth_year',
                'death_year_type',
                'death_year'
            ]);
        });
    }
};
