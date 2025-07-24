<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Page;
use App\Models\PageReference;
use App\Models\Reference;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageReferenceSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // التأكد من وجود بيانات أساسية
        if (Page::count() === 0 || Reference::count() === 0) {
            $this->command->warn('لا توجد صفحات أو مراجع في قاعدة البيانات. يرجى تشغيل PageSeeder و ReferenceSeeder أولاً.');
            return;
        }

        $this->command->info('بدء إنشاء مراجع الصفحات...');

        // الحصول على عينة من الصفحات والمراجع
        $pages = Page::with(['book', 'chapter'])->take(100)->get();
        $references = Reference::all();
        
        if ($references->isEmpty()) {
            $this->command->warn('لا توجد مراجع متاحة. يرجى تشغيل ReferenceSeeder أولاً.');
            return;
        }

        foreach ($pages as $page) {
            $this->createPageReferencesForPage($page, $references);
        }

        // إنشاء مراجع صفحات خاصة
        $this->createSpecialPageReferences($references);
        $this->createQuranicPageReferences($references);
        $this->createHadithPageReferences($references);

        $this->command->info('تم إنشاء ' . PageReference::count() . ' مرجع صفحة بنجاح.');
    }

    /**
     * إنشاء مراجع صفحات لصفحة معينة
     */
    private function createPageReferencesForPage(Page $page, $references): void
    {
        // عدد عشوائي من المراجع لكل صفحة (0-5)
        $referenceCount = fake()->numberBetween(0, 5);
        
        for ($i = 0; $i < $referenceCount; $i++) {
            $this->createPageReferenceForPage($page, $references);
        }
    }

    /**
     * إنشاء مرجع صفحة لصفحة معينة
     */
    private function createPageReferenceForPage(Page $page, $references): void
    {
        // اختيار مرجع عشوائي
        $reference = $references->random();
        
        // تحديد نوع الاقتباس بناءً على الاحتمالات
        $citationType = $this->getRandomCitationType();
        
        PageReference::factory()
            ->state([
                'page_id' => $page->id,
                'reference_id' => $reference->id,
                'chapter_id' => $page->chapter_id,
            ])
            ->{$citationType}() // استخدام الحالة المناسبة
            ->create();
    }

    /**
     * اختيار نوع اقتباس عشوائي بناءً على الاحتمالات
     */
    private function getRandomCitationType(): string
    {
        $types = [
            'directQuote' => 30,  // 30% اقتباس مباشر
            'reference' => 25,    // 25% مرجع عام
            'paraphrase' => 20,   // 20% إعادة صياغة
            'seeAlso' => 15,      // 15% انظر أيضاً
            'compare' => 10,      // 10% مقارنة
        ];
        
        $random = fake()->numberBetween(1, 100);
        $cumulative = 0;
        
        foreach ($types as $type => $percentage) {
            $cumulative += $percentage;
            if ($random <= $cumulative) {
                return $type;
            }
        }
        
        return 'reference'; // افتراضي
    }

    /**
     * إنشاء مراجع صفحات خاصة للكتب المهمة
     */
    private function createSpecialPageReferences($references): void
    {
        $this->command->info('إنشاء مراجع صفحات خاصة للكتب المهمة...');
        
        $importantBooks = Book::where('status', 'published')
            ->where('visibility', 'public')
            ->orderBy('pages_count', 'desc')
            ->take(5)
            ->get();
        
        foreach ($importantBooks as $book) {
            $pages = $book->pages()->take(20)->get();
            
            foreach ($pages as $page) {
                // مراجع مصادر أولية
                $this->createPrimarySourceReferences($page, $references);
                
                // مراجع مصادر ثانوية
                $this->createSecondarySourceReferences($page, $references);
            }
        }
    }

    /**
     * إنشاء مراجع مصادر أولية
     */
    private function createPrimarySourceReferences(Page $page, $references): void
    {
        $referenceCount = fake()->numberBetween(1, 3);
        
        for ($i = 0; $i < $referenceCount; $i++) {
            $reference = $references->random();
            
            PageReference::factory()
                ->primarySource()
                ->directQuote()
                ->state([
                    'page_id' => $page->id,
                    'reference_id' => $reference->id,
                    'chapter_id' => $page->chapter_id,
                ])
                ->create();
        }
    }

    /**
     * إنشاء مراجع مصادر ثانوية
     */
    private function createSecondarySourceReferences(Page $page, $references): void
    {
        $referenceCount = fake()->numberBetween(1, 4);
        
        for ($i = 0; $i < $referenceCount; $i++) {
            $reference = $references->random();
            
            PageReference::factory()
                ->secondarySource()
                ->state([
                    'page_id' => $page->id,
                    'reference_id' => $reference->id,
                    'chapter_id' => $page->chapter_id,
                    'citation_type' => fake()->randomElement(['paraphrase', 'see_also', 'compare']),
                ])
                ->create();
        }
    }

    /**
     * إنشاء مراجع صفحات قرآنية
     */
    private function createQuranicPageReferences($references): void
    {
        $this->command->info('إنشاء مراجع صفحات قرآنية...');
        
        // البحث عن المراجع القرآنية
        $quranicReferences = $references->where('reference_type', 'verse');
        
        if ($quranicReferences->isEmpty()) {
            return;
        }
        
        $pages = Page::take(50)->get();
        
        foreach ($pages as $page) {
            if (fake()->boolean(40)) { // 40% احتمال وجود مرجع قرآني
                $reference = $quranicReferences->random();
                
                PageReference::factory()
                    ->quranic()
                    ->primarySource()
                    ->state([
                        'page_id' => $page->id,
                        'reference_id' => $reference->id,
                        'chapter_id' => $page->chapter_id,
                    ])
                    ->create();
            }
        }
    }

    /**
     * إنشاء مراجع صفحات الأحاديث
     */
    private function createHadithPageReferences($references): void
    {
        $this->command->info('إنشاء مراجع صفحات الأحاديث...');
        
        // البحث عن مراجع الأحاديث
        $hadithReferences = $references->where('reference_type', 'hadith');
        
        if ($hadithReferences->isEmpty()) {
            return;
        }
        
        $pages = Page::take(60)->get();
        
        foreach ($pages as $page) {
            if (fake()->boolean(35)) { // 35% احتمال وجود مرجع حديث
                $reference = $hadithReferences->random();
                
                PageReference::factory()
                    ->hadith()
                    ->primarySource()
                    ->state([
                        'page_id' => $page->id,
                        'reference_id' => $reference->id,
                        'chapter_id' => $page->chapter_id,
                    ])
                    ->create();
            }
        }
    }

    /**
     * إنشاء مراجع صفحات فقهية
     */
    public function createFiqhPageReferences($references): void
    {
        $this->command->info('إنشاء مراجع صفحات فقهية...');
        
        // البحث عن المراجع الفقهية
        $fiqhReferences = $references->where('reference_type', 'book')
            ->filter(function ($ref) {
                return str_contains($ref->title, 'فقه') || 
                       str_contains($ref->title, 'المغني') || 
                       str_contains($ref->title, 'الأم');
            });
        
        if ($fiqhReferences->isEmpty()) {
            return;
        }
        
        $pages = Page::take(40)->get();
        
        foreach ($pages as $page) {
            if (fake()->boolean(30)) { // 30% احتمال وجود مرجع فقهي
                $reference = $fiqhReferences->random();
                
                PageReference::factory()
                    ->fiqh()
                    ->state([
                        'page_id' => $page->id,
                        'reference_id' => $reference->id,
                        'chapter_id' => $page->chapter_id,
                    ])
                    ->create();
            }
        }
    }

    /**
     * إنشاء مراجع صفحات متنوعة المواضع
     */
    public function createVariedPositionPageReferences($references): void
    {
        $this->command->info('إنشاء مراجع صفحات متنوعة المواضع...');
        
        $pages = Page::take(30)->get();
        
        foreach ($pages as $page) {
            // مراجع في بداية الصفحة
            if (fake()->boolean(40)) {
                $reference = $references->random();
                
                PageReference::factory()
                    ->atPageStart()
                    ->state([
                        'page_id' => $page->id,
                        'reference_id' => $reference->id,
                        'chapter_id' => $page->chapter_id,
                    ])
                    ->create();
            }
            
            // مراجع في نهاية الصفحة
            if (fake()->boolean(30)) {
                $reference = $references->random();
                
                PageReference::factory()
                    ->atPageEnd()
                    ->state([
                        'page_id' => $page->id,
                        'reference_id' => $reference->id,
                        'chapter_id' => $page->chapter_id,
                    ])
                    ->create();
            }
        }
    }

    /**
     * إنشاء مراجع صفحات متنوعة الأنواع
     */
    public function createVariedTypePageReferences($references): void
    {
        $this->command->info('إنشاء مراجع صفحات متنوعة الأنواع...');
        
        $pages = Page::take(25)->get();
        
        foreach ($pages as $page) {
            $referenceCount = fake()->numberBetween(1, 4);
            
            for ($i = 0; $i < $referenceCount; $i++) {
                $reference = $references->random();
                $citationType = fake()->randomElement([
                    'directQuote', 'paraphrase', 'reference', 'seeAlso', 'compare'
                ]);
                
                PageReference::factory()
                    ->{$citationType}()
                    ->state([
                        'page_id' => $page->id,
                        'reference_id' => $reference->id,
                        'chapter_id' => $page->chapter_id,
                    ])
                    ->create();
            }
        }
    }
}