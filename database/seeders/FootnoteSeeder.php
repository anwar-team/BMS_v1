<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Footnote;
use App\Models\Page;
use App\Models\Volume;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FootnoteSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // التأكد من وجود بيانات أساسية
        if (Book::count() === 0 || Page::count() === 0) {
            $this->command->warn('لا توجد كتب أو صفحات في قاعدة البيانات. يرجى تشغيل BookSeeder و PageSeeder أولاً.');
            return;
        }

        $this->command->info('بدء إنشاء الحواشي...');

        // الحصول على عينة من الكتب والصفحات
        $books = Book::with(['pages', 'chapters', 'volumes'])->take(10)->get();
        
        foreach ($books as $book) {
            $this->createFootnotesForBook($book);
        }

        $this->command->info('تم إنشاء ' . Footnote::count() . ' حاشية بنجاح.');
    }

    /**
     * إنشاء حواشي لكتاب معين
     */
    private function createFootnotesForBook(Book $book): void
    {
        $pages = $book->pages()->take(20)->get(); // أول 20 صفحة
        
        foreach ($pages as $page) {
            // عدد عشوائي من الحواشي لكل صفحة (0-8)
            $footnoteCount = fake()->numberBetween(0, 8);
            
            for ($i = 1; $i <= $footnoteCount; $i++) {
                $this->createFootnoteForPage($book, $page, $i);
            }
        }
    }

    /**
     * إنشاء حاشية لصفحة معينة
     */
    private function createFootnoteForPage(Book $book, Page $page, int $order): void
    {
        // اختيار فصل عشوائي من الكتاب
        $chapter = $book->chapters()->inRandomOrder()->first();
        $volume = $book->volumes()->inRandomOrder()->first();
        
        // تحديد نوع الحاشية بناءً على الاحتمالات
        $type = $this->getRandomFootnoteType();
        
        Footnote::factory()
            ->state([
                'book_id' => $book->id,
                'page_id' => $page->id,
                'chapter_id' => $chapter?->id,
                'volume_id' => $volume?->id,
                'footnote_number' => $order,
                'order_in_page' => $order,
                'type' => $type,
                'position_in_page' => fake()->numberBetween(100 * $order, 100 * ($order + 1)),
            ])
            ->{$type}() // استخدام الحالة المناسبة
            ->create();
    }

    /**
     * اختيار نوع حاشية عشوائي بناءً على الاحتمالات
     */
    private function getRandomFootnoteType(): string
    {
        $types = [
            'explanation' => 40,  // 40% تفسيرية
            'reference' => 25,    // 25% مرجعية
            'translation' => 15,  // 15% ترجمة
            'correction' => 10,   // 10% تصحيح
            'addition' => 10,     // 10% إضافية
        ];
        
        $random = fake()->numberBetween(1, 100);
        $cumulative = 0;
        
        foreach ($types as $type => $percentage) {
            $cumulative += $percentage;
            if ($random <= $cumulative) {
                return $type;
            }
        }
        
        return 'explanation'; // افتراضي
    }

    /**
     * إنشاء حواشي خاصة للكتب المهمة
     */
    public function createSpecialFootnotes(): void
    {
        $this->command->info('إنشاء حواشي خاصة للكتب المهمة...');
        
        // البحث عن كتب مهمة (يمكن تحديدها بناءً على معايير معينة)
        $importantBooks = Book::where('status', 'published')
            ->where('visibility', 'public')
            ->orderBy('pages_count', 'desc')
            ->take(5)
            ->get();
        
        foreach ($importantBooks as $book) {
            // إنشاء حواشي طويلة ومفصلة
            Footnote::factory()
                ->count(10)
                ->long()
                ->explanation()
                ->state([
                    'book_id' => $book->id,
                    'page_id' => $book->pages()->inRandomOrder()->first()?->id,
                    'is_original' => true,
                ])
                ->create();
            
            // إنشاء حواشي مرجعية موثقة
            Footnote::factory()
                ->count(15)
                ->reference()
                ->state([
                    'book_id' => $book->id,
                    'page_id' => $book->pages()->inRandomOrder()->first()?->id,
                    'is_original' => true,
                ])
                ->create();
        }
    }

    /**
     * إنشاء حواشي تحقيق (من المحقق)
     */
    public function createEditorialFootnotes(): void
    {
        $this->command->info('إنشاء حواشي التحقيق...');
        
        $books = Book::take(5)->get();
        
        foreach ($books as $book) {
            // حواشي تصحيح من المحقق
            Footnote::factory()
                ->count(fake()->numberBetween(3, 8))
                ->correction()
                ->editorial()
                ->state([
                    'book_id' => $book->id,
                    'page_id' => $book->pages()->inRandomOrder()->first()?->id,
                ])
                ->create();
            
            // حواشي إضافية من المحقق
            Footnote::factory()
                ->count(fake()->numberBetween(2, 6))
                ->addition()
                ->editorial()
                ->state([
                    'book_id' => $book->id,
                    'page_id' => $book->pages()->inRandomOrder()->first()?->id,
                ])
                ->create();
        }
    }

    /**
     * إنشاء حواشي متنوعة الأطوال
     */
    public function createVariedLengthFootnotes(): void
    {
        $this->command->info('إنشاء حواشي متنوعة الأطوال...');
        
        $books = Book::take(3)->get();
        
        foreach ($books as $book) {
            $pages = $book->pages()->take(10)->get();
            
            foreach ($pages as $page) {
                // حواشي قصيرة
                Footnote::factory()
                    ->count(fake()->numberBetween(1, 3))
                    ->short()
                    ->state([
                        'book_id' => $book->id,
                        'page_id' => $page->id,
                    ])
                    ->create();
                
                // حاشية طويلة واحدة
                if (fake()->boolean(30)) { // 30% احتمال
                    Footnote::factory()
                        ->long()
                        ->explanation()
                        ->state([
                            'book_id' => $book->id,
                            'page_id' => $page->id,
                        ])
                        ->create();
                }
            }
        }
    }
}