<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Reference;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReferenceSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // التأكد من وجود بيانات أساسية
        if (Book::count() === 0) {
            $this->command->warn('لا توجد كتب في قاعدة البيانات. يرجى تشغيل BookSeeder أولاً.');
            return;
        }

        $this->command->info('بدء إنشاء المراجع والمصادر...');

        // الحصول على عينة من الكتب
        $books = Book::take(10)->get();
        
        foreach ($books as $book) {
            $this->createReferencesForBook($book);
        }

        // إنشاء مراجع خاصة
        $this->createSpecialReferences();
        $this->createClassicReferences();
        $this->createModernReferences();

        $this->command->info('تم إنشاء ' . Reference::count() . ' مرجع بنجاح.');
    }

    /**
     * إنشاء مراجع لكتاب معين
     */
    private function createReferencesForBook(Book $book): void
    {
        // عدد عشوائي من المراجع لكل كتاب (5-20)
        $referenceCount = fake()->numberBetween(5, 20);
        
        for ($i = 0; $i < $referenceCount; $i++) {
            $this->createReferenceForBook($book);
        }
    }

    /**
     * إنشاء مرجع لكتاب معين
     */
    private function createReferenceForBook(Book $book): void
    {
        // تحديد نوع المرجع بناءً على الاحتمالات
        $referenceType = $this->getRandomReferenceType();
        
        Reference::factory()
            ->state([
                'book_id' => $book->id,
            ])
            ->{$referenceType}() // استخدام الحالة المناسبة
            ->create();
    }

    /**
     * اختيار نوع مرجع عشوائي بناءً على الاحتمالات
     */
    private function getRandomReferenceType(): string
    {
        $types = [
            'book' => 40,      // 40% كتب
            'hadith' => 25,    // 25% أحاديث
            'verse' => 15,     // 15% آيات
            'article' => 10,   // 10% مقالات
            'manuscript' => 5, // 5% مخطوطات
            'website' => 5,    // 5% مواقع
        ];
        
        $random = fake()->numberBetween(1, 100);
        $cumulative = 0;
        
        foreach ($types as $type => $percentage) {
            $cumulative += $percentage;
            if ($random <= $cumulative) {
                return $type;
            }
        }
        
        return 'book'; // افتراضي
    }

    /**
     * إنشاء مراجع خاصة للكتب المهمة
     */
    private function createSpecialReferences(): void
    {
        $this->command->info('إنشاء مراجع خاصة للكتب المهمة...');
        
        $importantBooks = Book::where('status', 'published')
            ->where('visibility', 'public')
            ->orderBy('pages_count', 'desc')
            ->take(5)
            ->get();
        
        foreach ($importantBooks as $book) {
            // مراجع قرآنية
            $this->createQuranicReferences($book);
            
            // مراجع الأحاديث
            $this->createHadithReferences($book);
            
            // مراجع الكتب المهمة
            $this->createImportantBookReferences($book);
        }
    }

    /**
     * إنشاء مراجع قرآنية
     */
    private function createQuranicReferences(Book $book): void
    {
        Reference::factory()
            ->count(fake()->numberBetween(10, 25))
            ->verse()
            ->verified()
            ->state([
                'book_id' => $book->id,
            ])
            ->create();
    }

    /**
     * إنشاء مراجع الأحاديث
     */
    private function createHadithReferences(Book $book): void
    {
        Reference::factory()
            ->count(fake()->numberBetween(15, 30))
            ->hadith()
            ->verified()
            ->state([
                'book_id' => $book->id,
            ])
            ->create();
    }

    /**
     * إنشاء مراجع الكتب المهمة
     */
    private function createImportantBookReferences(Book $book): void
    {
        Reference::factory()
            ->count(fake()->numberBetween(8, 15))
            ->book()
            ->verified()
            ->popular()
            ->state([
                'book_id' => $book->id,
            ])
            ->create();
    }

    /**
     * إنشاء مراجع كلاسيكية
     */
    private function createClassicReferences(): void
    {
        $this->command->info('إنشاء مراجع كلاسيكية...');
        
        $books = Book::take(8)->get();
        
        foreach ($books as $book) {
            // كتب تراثية
            Reference::factory()
                ->count(fake()->numberBetween(5, 12))
                ->book()
                ->classic()
                ->verified()
                ->state([
                    'book_id' => $book->id,
                ])
                ->create();
            
            // مخطوطات
            Reference::factory()
                ->count(fake()->numberBetween(2, 6))
                ->manuscript()
                ->classic()
                ->state([
                    'book_id' => $book->id,
                ])
                ->create();
        }
    }

    /**
     * إنشاء مراجع حديثة
     */
    private function createModernReferences(): void
    {
        $this->command->info('إنشاء مراجع حديثة...');
        
        $books = Book::take(6)->get();
        
        foreach ($books as $book) {
            // كتب حديثة
            Reference::factory()
                ->count(fake()->numberBetween(3, 8))
                ->book()
                ->recent()
                ->verified()
                ->state([
                    'book_id' => $book->id,
                ])
                ->create();
            
            // مقالات
            Reference::factory()
                ->count(fake()->numberBetween(2, 5))
                ->article()
                ->recent()
                ->state([
                    'book_id' => $book->id,
                ])
                ->create();
            
            // مواقع إلكترونية
            Reference::factory()
                ->count(fake()->numberBetween(1, 3))
                ->website()
                ->recent()
                ->state([
                    'book_id' => $book->id,
                ])
                ->create();
        }
    }

    /**
     * إنشاء مراجع متنوعة الشعبية
     */
    public function createVariedPopularityReferences(): void
    {
        $this->command->info('إنشاء مراجع متنوعة الشعبية...');
        
        $books = Book::take(5)->get();
        
        foreach ($books as $book) {
            // مراجع شائعة
            Reference::factory()
                ->count(fake()->numberBetween(3, 8))
                ->popular()
                ->verified()
                ->state([
                    'book_id' => $book->id,
                ])
                ->create();
            
            // مراجع غير موثقة
            Reference::factory()
                ->count(fake()->numberBetween(1, 4))
                ->unverified()
                ->state([
                    'book_id' => $book->id,
                ])
                ->create();
        }
    }

    /**
     * إنشاء مراجع حسب التخصص
     */
    public function createSpecializedReferences(): void
    {
        $this->command->info('إنشاء مراجع متخصصة...');
        
        $books = Book::take(4)->get();
        
        foreach ($books as $book) {
            // مراجع فقهية
            Reference::factory()
                ->count(fake()->numberBetween(4, 10))
                ->book()
                ->state([
                    'book_id' => $book->id,
                    'title' => fake()->randomElement([
                        'المغني لابن قدامة', 'الأم للشافعي', 'المدونة الكبرى',
                        'بدائع الصنائع', 'المحلى بالآثار', 'الفروع لابن مفلح'
                    ]),
                ])
                ->create();
            
            // مراجع تفسير
            Reference::factory()
                ->count(fake()->numberBetween(3, 7))
                ->book()
                ->state([
                    'book_id' => $book->id,
                    'title' => fake()->randomElement([
                        'تفسير الطبري', 'تفسير ابن كثير', 'تفسير القرطبي',
                        'تفسير البغوي', 'تفسير الرازي', 'تفسير السعدي'
                    ]),
                ])
                ->create();
            
            // مراجع حديث
            Reference::factory()
                ->count(fake()->numberBetween(5, 12))
                ->hadith()
                ->state([
                    'book_id' => $book->id,
                ])
                ->create();
        }
    }
}