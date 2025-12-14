<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BokImport;
use App\Models\Book;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ImportFromShamela extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shamela:import {book_id} {--save-db} {--save-json} {--extract-html}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a book from Shamela website using Python scraper';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookId = $this->argument('book_id');
        $saveDb = $this->option('save-db');
        $saveJson = $this->option('save-json');
        $extractHtml = $this->option('extract-html');
        
        $this->info("Starting import for book ID: {$bookId}");
        
        try {
            // تشغيل سكربت Python
            $this->runPythonScript($bookId, $saveDb, $saveJson, $extractHtml);
            
            if ($saveDb) {
                $this->processBookInDatabase($bookId);
            }
            
            $this->info("Import completed successfully!");
            
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    protected function runPythonScript($bookId, $saveDb, $saveJson, $extractHtml)
    {
        $pythonScript = base_path('script/shamela_scraper_final/shamela_easy_runner.py');
        
        $command = "python \"{$pythonScript}\" --book-id {$bookId}";
        
        if ($saveDb) {
            $command .= ' --save-db';
        }
        
        if ($saveJson) {
            $command .= ' --save-json';
        }
        
        if ($extractHtml) {
            $command .= ' --extract-html';
        }
        
        $this->info("Running: {$command}");
        
        // تشغيل الأمر
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("Python script failed with code {$returnCode}: " . implode("\n", $output));
        }
        
        $this->info("Python script completed successfully");
        foreach ($output as $line) {
            $this->line($line);
        }
    }
    
    protected function processBookInDatabase($bookId)
    {
        $this->info("Processing book data for database...");
        
        // البحث عن ملف JSON
        $jsonPath = base_path("script/shamela_scraper_final/shamela_books/");
        $jsonFiles = glob($jsonPath . "*_{$bookId}_*.json");
        
        if (empty($jsonFiles)) {
            $this->warn("No JSON file found for book {$bookId}");
            return;
        }
        
        $latestFile = end($jsonFiles);
        $bookData = json_decode(file_get_contents($latestFile), true);
        
        if (!$bookData) {
            throw new \Exception("Failed to read book data from JSON file");
        }
        
        $this->info("Found book data: " . ($bookData['title'] ?? 'Unknown title'));
        
        // إنشاء أو تحديث الكتاب في قاعدة البيانات
        $book = Book::updateOrCreate(
            ['metadata->shamela_id' => $bookId],
            [
                'title' => $bookData['title'] ?? "كتاب الشاملة رقم {$bookId}",
                'slug' => Str::slug($bookData['title'] ?? "shamela-book-{$bookId}"),
                'description' => $bookData['description'] ?? null,
                'language' => 'ar',
                'pages_count' => count($bookData['pages'] ?? []),
                'is_public' => true,
                'metadata' => [
                    'shamela_id' => $bookId,
                    'import_date' => now()->toISOString(),
                    'imported_from' => 'shamela_scraper'
                ]
            ]
        );
        
        $this->info("Book saved to database with ID: {$book->id}");
        
        // حفظ ملف JSON في التخزين
        $fileName = "shamela_books/book_{$bookId}_" . date('Y-m-d_H-i-s') . ".json";
        Storage::put($fileName, file_get_contents($latestFile));
        
        $this->info("JSON file saved to storage: {$fileName}");
    }
}
