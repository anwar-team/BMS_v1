<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $publishers = [
            [
                'name' => 'دار الكتب العلمية',
                'country' => 'لبنان',
                'email' => 'info@dar-kotob.com',
                'phone' => '+961 1 123456',
                'description' => 'دار نشر متخصصة في الكتب الإسلامية والعربية التراثية',
                'website_url' => 'https://www.dar-kotob.com',
                'is_active' => true,
            ],
            [
                'name' => 'مكتبة الرشد',
                'country' => 'السعودية',
                'email' => 'info@rushd.sa',
                'phone' => '+966 11 1234567',
                'description' => 'مكتبة متخصصة في الكتب الشرعية والأدبية',
                'website_url' => 'https://www.rushd.sa',
                'is_active' => true,
            ],
            [
                'name' => 'دار الفكر',
                'country' => 'سوريا',
                'email' => 'contact@darfikr.com',
                'phone' => '+963 11 1234567',
                'description' => 'دار نشر عريقة تختص بالكتب الفكرية والثقافية',
                'website_url' => 'https://www.darfikr.com',
                'is_active' => true,
            ],
            [
                'name' => 'مؤسسة الرسالة',
                'country' => 'لبنان',
                'email' => 'info@resala.org',
                'phone' => '+961 1 987654',
                'description' => 'مؤسسة نشر تهتم بالكتب الإسلامية المعاصرة',
                'website_url' => 'https://www.resala.org',
                'is_active' => true,
            ],
            [
                'name' => 'دار المعرفة',
                'country' => 'مصر',
                'email' => 'info@darmaerifa.com',
                'phone' => '+20 2 12345678',
                'description' => 'دار نشر مصرية متخصصة في الكتب العلمية والأدبية',
                'website_url' => null,
                'is_active' => false,
            ],
        ];

        foreach ($publishers as $publisher) {
            Publisher::create($publisher);
        }
    }
}