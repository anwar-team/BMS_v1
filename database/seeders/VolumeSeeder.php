<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class VolumeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('pages')->truncate();
        DB::table('chapters')->truncate();
        DB::table('volumes')->truncate();
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $books = \App\Models\Book::all();
        foreach ($books as $book) {
            $volumesCount = rand(1, 5);
            for ($i = 1; $i <= $volumesCount; $i++) {
                \App\Models\Volume::factory()->create([
                    'book_id' => $book->id,
                    'number' => $i,
                ]);
            }
        }
    }
}
