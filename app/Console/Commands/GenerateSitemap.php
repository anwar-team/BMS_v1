<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Book;
use App\Models\Author;
use App\Models\BookSection;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap for SEO';

    public function handle()
    {
        $this->info('Generating sitemap...');
        
        $sitemap = Sitemap::create();

        // الصفحة الرئيسية
        $sitemap->add(
            Url::create('/')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0)
        );

        // صفحة البحث
        $sitemap->add(
            Url::create('/ultra-fast-search')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.9)
        );

        // الكتب (أول 1000 كتاب للأداء)
        $this->info('Adding books to sitemap...');
        Book::query()
            ->select(['id', 'updated_at'])
            ->limit(1000)
            ->chunk(100, function ($books) use ($sitemap) {
                foreach ($books as $book) {
                    $sitemap->add(
                        Url::create("/books/{$book->id}")
                            ->setLastModificationDate($book->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                }
            });

        // المؤلفون (أول 500 مؤلف)
        $this->info('Adding authors to sitemap...');
        Author::query()
            ->select(['id', 'updated_at'])
            ->limit(500)
            ->chunk(100, function ($authors) use ($sitemap) {
                foreach ($authors as $author) {
                    $sitemap->add(
                        Url::create("/authors/{$author->id}/details")
                            ->setLastModificationDate($author->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.7)
                    );
                }
            });

        // الأقسام
        $this->info('Adding sections to sitemap...');
        BookSection::query()
            ->select(['id', 'updated_at'])
            ->chunk(100, function ($sections) use ($sitemap) {
                foreach ($sections as $section) {
                    $sitemap->add(
                        Url::create("/sections/{$section->id}")
                            ->setLastModificationDate($section->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.6)
                    );
                }
            });

        // حفظ Sitemap
        $sitemap->writeToFile(public_path('sitemap.xml'));
        
        $this->info('Sitemap generated successfully at: public/sitemap.xml');
        
        return Command::SUCCESS;
    }
}
