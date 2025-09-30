<?php

namespace Database\Seeders;

use App\Models\BookSection;
use App\Models\Book;
use App\Models\Author;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookSystemSeeder extends Seeder
{
    /**
     * تشغيل seeder لنظام إدارة الكتب
     * يقوم بإنشاء بيانات تجريبية للأقسام والكتب والمؤلفين
     */
    public function run(): void
    {
        // إنشاء أقسام الكتب الرئيسية
        $this->createBookSections();
        
        // إنشاء مؤلفين
        $this->createAuthors();
        
        // إنشاء كتب وربطها بالأقسام والمؤلفين
        $this->createBooks();
        
        $this->command->info('✅ تم إنشاء بيانات تجريبية لنظام إدارة الكتب بنجاح!');
    }

    /**
     * إنشاء أقسام الكتب
     */
    private function createBookSections(): void
    {
        $sections = [
            [
                'name' => 'القرآن الكريم وعلومه',
                'description' => 'كتب في التفسير وعلوم القرآن والقراءات',
                'slug' => 'quran-sciences',
                'logo_path' => 'images/sections/quran.svg'
            ],
            [
                'name' => 'الحديث النبوي الشريف',
                'description' => 'كتب الأحاديث النبوية وشروحها',
                'slug' => 'hadith',
                'logo_path' => 'images/sections/hadith.svg'
            ],
            [
                'name' => 'الفقه الإسلامي',
                'description' => 'كتب الفقه على المذاهب الأربعة',
                'slug' => 'fiqh',
                'logo_path' => 'images/sections/fiqh.svg'
            ],
            [
                'name' => 'العقيدة الإسلامية',
                'description' => 'كتب في أصول الدين والعقيدة',
                'slug' => 'aqeedah',
                'logo_path' => 'images/sections/aqeedah.svg'
            ],
            [
                'name' => 'السيرة النبوية',
                'description' => 'كتب سيرة النبي محمد صلى الله عليه وسلم',
                'slug' => 'seerah',
                'logo_path' => 'images/sections/seerah.svg'
            ],
            [
                'name' => 'التاريخ الإسلامي',
                'description' => 'كتب التاريخ الإسلامي والتراجم',
                'slug' => 'history',
                'logo_path' => 'images/sections/history.svg'
            ],
            [
                'name' => 'الأدب والبلاغة',
                'description' => 'كتب الأدب العربي والبلاغة',
                'slug' => 'literature',
                'logo_path' => 'images/sections/literature.svg'
            ],
            [
                'name' => 'اللغة العربية',
                'description' => 'كتب النحو والصرف والمعاجم',
                'slug' => 'arabic-language',
                'logo_path' => 'images/sections/language.svg'
            ]
        ];

        foreach ($sections as $index => $section) {
            BookSection::create([
                'name' => $section['name'],
                'description' => $section['description'],
                'slug' => $section['slug'],
                'logo_path' => $section['logo_path'],
                'sort_order' => $index + 1,
                'is_active' => true
            ]);
        }

        $this->command->info('📚 تم إنشاء ' . count($sections) . ' أقسام للكتب');
    }

    /**
     * إنشاء مؤلفين
     */
    private function createAuthors(): void
    {
        $authors = [
            [
                'full_name' => 'الإمام البخاري',
                'biography' => 'محمد بن إسماعيل البخاري، إمام المحدثين وصاحب الصحيح',
                'madhhab' => 'آخرون',
                'birth_date' => '810-01-01',
                'death_date' => '870-01-01'
            ],
            [
                'full_name' => 'الإمام مسلم',
                'biography' => 'مسلم بن الحجاج النيسابوري، صاحب صحيح مسلم',
                'madhhab' => 'آخرون',
                'birth_date' => '815-01-01',
                'death_date' => '875-01-01'
            ],
            [
                'full_name' => 'الإمام الشافعي',
                'biography' => 'محمد بن إدريس الشافعي، إمام المذهب الشافعي',
                'madhhab' => 'المذهب الشافعي',
                'birth_date' => '767-01-01',
                'death_date' => '820-01-01'
            ],
            [
                'full_name' => 'الإمام أحمد بن حنبل',
                'biography' => 'أحمد بن محمد بن حنبل، إمام المذهب الحنبلي',
                'madhhab' => 'المذهب الحنبلي',
                'birth_date' => '780-01-01',
                'death_date' => '855-01-01'
            ],
            [
                'full_name' => 'ابن تيمية',
                'biography' => 'أحمد بن عبد الحليم بن تيمية، شيخ الإسلام',
                'madhhab' => 'المذهب الحنبلي',
                'birth_date' => '1263-01-01',
                'death_date' => '1328-01-01'
            ],
            [
                'full_name' => 'ابن كثير',
                'biography' => 'إسماعيل بن عمر بن كثير، المفسر والمؤرخ',
                'madhhab' => 'المذهب الشافعي',
                'birth_date' => '1300-01-01',
                'death_date' => '1373-01-01'
            ],
            [
                'full_name' => 'الطبري',
                'biography' => 'محمد بن جرير الطبري، المفسر والمؤرخ',
                'madhhab' => 'آخرون',
                'birth_date' => '838-01-01',
                'death_date' => '923-01-01'
            ],
            [
                'full_name' => 'الجاحظ',
                'biography' => 'عمرو بن بحر الجاحظ، أديب ومفكر عباسي',
                'madhhab' => 'آخرون',
                'birth_date' => '776-01-01',
                'death_date' => '868-01-01'
            ]
        ];

        foreach ($authors as $author) {
            Author::create($author);
        }

        $this->command->info('👤 تم إنشاء ' . count($authors) . ' مؤلفين في جدول authors');
    }

    /**
     * إنشاء كتب وربطها بالأقسام والمؤلفين
     */
    private function createBooks(): void
    {
        $sections = BookSection::all();
        $authors = Author::all();

        $books = [
            // كتب الحديث
            [
                'title' => 'صحيح البخاري',
                'description' => 'أصح كتاب بعد كتاب الله تعالى',
                'published_year' => 850,
                'section_slug' => 'hadith',
                'author_names' => ['الإمام البخاري'],
                'pages_count' => 3000,
                'volumes_count' => 9
            ],
            [
                'title' => 'صحيح مسلم',
                'description' => 'ثاني أصح الكتب بعد صحيح البخاري',
                'published_year' => 860,
                'section_slug' => 'hadith',
                'author_names' => ['الإمام مسلم'],
                'pages_count' => 2800,
                'volumes_count' => 8
            ],
            
            // كتب التفسير
            [
                'title' => 'تفسير الطبري',
                'description' => 'جامع البيان في تأويل القرآن',
                'published_year' => 900,
                'section_slug' => 'quran-sciences',
                'author_names' => ['الطبري'],
                'pages_count' => 8000,
                'volumes_count' => 24
            ],
            [
                'title' => 'تفسير ابن كثير',
                'description' => 'تفسير القرآن العظيم',
                'published_year' => 1365,
                'section_slug' => 'quran-sciences',
                'author_names' => ['ابن كثير'],
                'pages_count' => 4000,
                'volumes_count' => 8
            ],
            
            // كتب الفقه
            [
                'title' => 'الأم',
                'description' => 'كتاب الفقه للإمام الشافعي',
                'published_year' => 815,
                'section_slug' => 'fiqh',
                'author_names' => ['الإمام الشافعي'],
                'pages_count' => 2500,
                'volumes_count' => 7
            ],
            [
                'title' => 'مسند أحمد',
                'description' => 'مسند الإمام أحمد بن حنبل',
                'published_year' => 850,
                'section_slug' => 'hadith',
                'author_names' => ['الإمام أحمد بن حنبل'],
                'pages_count' => 6000,
                'volumes_count' => 15
            ],
            
            // كتب العقيدة
            [
                'title' => 'مجموع الفتاوى',
                'description' => 'فتاوى شيخ الإسلام ابن تيمية',
                'published_year' => 1320,
                'section_slug' => 'aqeedah',
                'author_names' => ['ابن تيمية'],
                'pages_count' => 12000,
                'volumes_count' => 37
            ],
            
            // كتب التاريخ
            [
                'title' => 'البداية والنهاية',
                'description' => 'كتاب في التاريخ من بداية الخلق إلى نهاية العالم',
                'published_year' => 1365,
                'section_slug' => 'history',
                'author_names' => ['ابن كثير'],
                'pages_count' => 5000,
                'volumes_count' => 14
            ],
            [
                'title' => 'تاريخ الرسل والملوك',
                'description' => 'تاريخ الطبري المشهور',
                'published_year' => 915,
                'section_slug' => 'history',
                'author_names' => ['الطبري'],
                'pages_count' => 4500,
                'volumes_count' => 11
            ],
            
            // كتب الأدب
            [
                'title' => 'البيان والتبيين',
                'description' => 'كتاب في البلاغة والأدب',
                'published_year' => 850,
                'section_slug' => 'literature',
                'author_names' => ['الجاحظ'],
                'pages_count' => 1200,
                'volumes_count' => 4
            ],
            [
                'title' => 'كتاب البخلاء',
                'description' => 'كتاب أدبي ساخر عن البخل والبخلاء',
                'published_year' => 860,
                'section_slug' => 'literature',
                'author_names' => ['الجاحظ'],
                'pages_count' => 400,
                'volumes_count' => 1
            ]
        ];

        foreach ($books as $bookData) {
            // البحث عن القسم
            $section = $sections->where('slug', $bookData['section_slug'])->first();
            
            // إنشاء الكتاب
            $book = Book::create([
                'title' => $bookData['title'],
                'description' => $bookData['description'],
                'slug' => Str::slug($bookData['title']),
                'published_year' => $bookData['published_year'],
                'book_section_id' => $section->id,
                'pages_count' => $bookData['pages_count'],
                'volumes_count' => $bookData['volumes_count'],
                'status' => 'published',
                'visibility' => 'public'
            ]);

            // ربط الكتاب بالمؤلفين
            foreach ($bookData['author_names'] as $authorName) {
                $author = $authors->where('full_name', $authorName)->first();
                if ($author) {
                    $book->authors()->attach($author->id, [
                        'role' => 'مؤلف',
                        'is_main' => true,
                        'display_order' => 1
                    ]);
                }
            }
        }

        $this->command->info('📖 تم إنشاء ' . count($books) . ' كتاب وربطهم بالأقسام والمؤلفين');
    }
}
