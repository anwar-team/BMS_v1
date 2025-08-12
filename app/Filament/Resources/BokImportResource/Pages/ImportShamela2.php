<?php

namespace App\Filament\Resources\BokImportResource\Pages;

use App\Filament\Resources\BokImportResource;
use App\Models\BokImport;
use App\Models\Book;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ImportShamela extends Page
{
    protected static string $resource = BokImportResource::class;
    
    protected static string $view = 'filament.resources.bok-import-resource.pages.import-shamela';
    
    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-down';
    
    protected static ?string $title = 'استيراد من الشاملة';
    
    protected static ?string $navigationLabel = 'استيراد من الشاملة';

    public ?string $shamela_url = '';
    public bool $save_to_db = true;
    public bool $save_to_json = true;
    public bool $extract_html = false;
    public ?string $import_status = 'idle';
    public ?array $import_log = [];
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات الاستيراد من الشاملة')
                    ->description('أدخل رابط الكتاب من موقع shamela.ws أو معرف الكتاب')
                    ->schema([
                        TextInput::make('shamela_url')
                            ->label('رابط الكتاب أو معرف الكتاب')
                            ->placeholder('https://shamela.ws/book/12345 أو 12345')
                            ->required()
                            ->helperText('يمكنك إدخال الرابط الكامل للكتاب أو معرف الكتاب فقط'),
                            
                        Grid::make(2)
                            ->schema([
                                Toggle::make('save_to_db')
                                    ->label('حفظ في قاعدة البيانات')
                                    ->default(true)
                                    ->helperText('حفظ الكتاب مباشرة في قاعدة البيانات'),
                                    
                                Toggle::make('save_to_json')
                                    ->label('حفظ كملف JSON')
                                    ->default(true)
                                    ->helperText('حفظ نسخة احتياطية كملف JSON'),
                            ]),
                            
                        Toggle::make('extract_html')
                            ->label('استخراج HTML')
                            ->default(false)
                            ->helperText('استخراج المحتوى بصيغة HTML (يستغرق وقت أطول)'),
                    ]),
                    
                Section::make('حالة الاستيراد')
                    ->schema([
                        Placeholder::make('status')
                            ->label('الحالة الحالية')
                            ->content(fn () => $this->getStatusLabel()),
                            
                        Placeholder::make('log')
                            ->label('سجل العمليات')
                            ->content(fn () => $this->getLogContent())
                            ->visible(fn () => !empty($this->import_log)),
                    ])
            ]);
    }

    public function startImport()
    {
        try {
            if (empty($this->shamela_url)) {
                throw new \Exception('يرجى إدخال رابط الكتاب أو معرف الكتاب');
            }
            
            $this->import_status = 'processing';
            $this->import_log = [];
            
            // استخراج معرف الكتاب من الرابط
            $book_id = $this->extractBookId($this->shamela_url);
            
            if (!$book_id) {
                throw new \Exception('لا يمكن استخراج معرف الكتاب من الرابط المدخل');
            }

            // إنشاء سجل استيراد جديد
            $bokImport = BokImport::create([
                'original_filename' => "shamela_book_{$book_id}",
                'title' => "كتاب الشاملة رقم {$book_id}",
                'status' => 'processing',
                'import_source' => 'shamela',
                'user_id' => auth()->id(),
                'metadata' => [
                    'shamela_url' => $this->shamela_url,
                    'book_id' => $book_id,
                    'import_options' => [
                        'save_to_db' => $this->save_to_db,
                        'save_to_json' => $this->save_to_json,
                        'extract_html' => $this->extract_html,
                    ]
                ]
            ]);

            $this->addLog("تم إنشاء سجل الاستيراد بنجاح");
            $this->addLog("بدء استخراج الكتاب من الشاملة...");

            // تنفيذ سكربت Python
            $pythonScript = base_path('script/shamela_scraper_final/shamela_easy_runner.py');
            $command = $this->buildPythonCommand($pythonScript, $book_id);
            
            $this->addLog("تشغيل الأمر: {$command}");
            
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(300); // 5 دقائق
            $process->run();

            if ($process->isSuccessful()) {
                $this->addLog("تم استخراج الكتاب بنجاح");
                
                // محاولة قراءة الملف المحفوظ وإدراجه في قاعدة البيانات
                $this->processImportedBook($bokImport, $book_id);
                
                $bokImport->update(['status' => 'completed']);
                $this->import_status = 'completed';
                
                Notification::make()
                    ->title('تم الاستيراد بنجاح')
                    ->success()
                    ->body("تم استيراد الكتاب من الشاملة وحفظه في قاعدة البيانات")
                    ->send();
                    
            } else {
                throw new \Exception("فشل في تنفيذ سكربت Python: " . $process->getErrorOutput());
            }

        } catch (\Exception $e) {
            $this->import_status = 'failed';
            $this->addLog("خطأ: " . $e->getMessage());
            
            if (isset($bokImport)) {
                $bokImport->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
            
            Notification::make()
                ->title('فشل في الاستيراد')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function extractBookId(string $input): ?string
    {
        // إذا كان الإدخال رقم فقط
        if (is_numeric($input)) {
            return $input;
        }
        
        // استخراج من الرابط
        if (preg_match('/shamela\.ws\/book\/(\d+)/', $input, $matches)) {
            return $matches[1];
        }
        
        // استخراج الأرقام من النص
        if (preg_match('/(\d+)/', $input, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    protected function buildPythonCommand(string $script, string $bookId): string
    {
        $python = 'python'; // يمكن تغييرها إلى python3 حسب البيئة
        
        $args = [
            escapeshellarg($script),
            '--book-id', escapeshellarg($bookId),
        ];
        
        if ($this->save_to_db) {
            $args[] = '--save-db';
        }
        
        if ($this->save_to_json) {
            $args[] = '--save-json';
        }
        
        if ($this->extract_html) {
            $args[] = '--extract-html';
        }
        
        return $python . ' ' . implode(' ', $args);
    }

    protected function processImportedBook(BokImport $bokImport, string $bookId): void
    {
        try {
            // البحث عن ملف JSON المحفوظ
            $jsonPath = base_path("script/shamela_scraper_final/shamela_books/");
            $jsonFiles = glob($jsonPath . "*_{$bookId}_*.json");
            
            if (empty($jsonFiles)) {
                $this->addLog("لم يتم العثور على ملف JSON للكتاب");
                return;
            }
            
            $latestFile = end($jsonFiles);
            $bookData = json_decode(file_get_contents($latestFile), true);
            
            if (!$bookData) {
                throw new \Exception("فشل في قراءة بيانات الكتاب من ملف JSON");
            }
            
            $this->addLog("تم قراءة بيانات الكتاب من: " . basename($latestFile));
            
            // إنشاء الكتاب في قاعدة البيانات
            $book = Book::create([
                'title' => $bookData['title'] ?? "كتاب الشاملة رقم {$bookId}",
                'slug' => Str::slug($bookData['title'] ?? "shamela-book-{$bookId}"),
                'description' => $bookData['description'] ?? null,
                'language' => 'ar',
                'pages_count' => count($bookData['pages'] ?? []),
                'is_public' => true,
                'metadata' => [
                    'shamela_id' => $bookId,
                    'shamela_url' => $this->shamela_url,
                    'import_date' => now()->toISOString(),
                    'imported_from' => 'shamela_scraper'
                ]
            ]);
            
            // ربط الكتاب بسجل الاستيراد
            $bokImport->update([
                'book_id' => $book->id,
                'title' => $book->title,
                'pages_count' => $book->pages_count,
            ]);
            
            $this->addLog("تم إنشاء الكتاب في قاعدة البيانات بنجاح");
            
            // حفظ ملف JSON في التخزين
            $fileName = "shamela_books/book_{$bookId}_" . date('Y-m-d_H-i-s') . ".json";
            Storage::put($fileName, file_get_contents($latestFile));
            
            $this->addLog("تم حفظ ملف JSON في التخزين");
            
        } catch (\Exception $e) {
            $this->addLog("خطأ في معالجة بيانات الكتاب: " . $e->getMessage());
            throw $e;
        }
    }

    protected function addLog(string $message): void
    {
        $this->import_log[] = '[' . date('H:i:s') . '] ' . $message;
    }

    protected function getStatusLabel(): string
    {
        return match($this->import_status) {
            'idle' => '⚪ في الانتظار',
            'processing' => '🔄 جاري المعالجة...',
            'completed' => '✅ تم بنجاح',
            'failed' => '❌ فشل',
            default => 'غير معروف'
        };
    }

    protected function getLogContent(): string
    {
        if (empty($this->import_log)) {
            return 'لا توجد سجلات بعد';
        }
        
        return '<div style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; max-height: 300px; overflow-y: auto;">' . 
               implode('<br>', array_map('htmlspecialchars', $this->import_log)) . 
               '</div>';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('start_import')
                ->label('بدء الاستيراد')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action('startImport')
                ->disabled(fn() => $this->import_status === 'processing'),
                
            Action::make('reset')
                ->label('إعادة تعيين')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->import_status = 'idle';
                    $this->import_log = [];
                    $this->shamela_url = '';
                })
                ->visible(fn() => $this->import_status !== 'idle'),
        ];
    }
}
