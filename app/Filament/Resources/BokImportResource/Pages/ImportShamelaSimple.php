<?php

namespace App\Filament\Resources\BokImportResource\Pages;

use App\Filament\Resources\BokImportResource;
use App\Models\BokImport;
use Filament\Resources\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ImportShamelaSimple extends Page
{
    protected static string $resource = BokImportResource::class;
    
    protected static string $view = 'filament.resources.bok-import-resource.pages.import-shamela-simple';
    
    protected static ?string $title = 'استيراد من الشاملة (بسيط)';

    public $shamela_url = '';
    public $status = 'idle';
    public $logs = [];
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('استيراد كتاب من الشاملة')
                    ->schema([
                        TextInput::make('shamela_url')
                            ->label('رابط الكتاب أو معرف الكتاب')
                            ->placeholder('https://shamela.ws/book/12345 أو 12345')
                            ->required(),
                            
                        Placeholder::make('status_display')
                            ->label('الحالة')
                            ->content($this->getStatusLabel()),
                    ])
            ]);
    }

    public function startImport()
    {
        try {
            if (empty($this->shamela_url)) {
                throw new \Exception('يرجى إدخال رابط الكتاب');
            }

            $this->status = 'processing';
            
            // استخراج معرف الكتاب
            $bookId = $this->extractBookId($this->shamela_url);
            
            if (!$bookId) {
                throw new \Exception('لا يمكن استخراج معرف الكتاب');
            }

            // إنشاء سجل استيراد
            $bokImport = BokImport::create([
                'original_filename' => "shamela_book_{$bookId}",
                'title' => "كتاب الشاملة رقم {$bookId}",
                'status' => 'processing',
                'import_source' => 'shamela',
                'user_id' => auth()->id(),
            ]);

            // تنفيذ سكربت Python (محاكاة)
            $this->simulatePythonScript($bokImport, $bookId);
            
            $this->status = 'completed';
            
            Notification::make()
                ->title('تم الاستيراد بنجاح')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            $this->status = 'failed';
            
            Notification::make()
                ->title('فشل في الاستيراد')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function extractBookId(string $input): ?string
    {
        if (is_numeric($input)) {
            return $input;
        }
        
        if (preg_match('/shamela\.ws\/book\/(\d+)/', $input, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/(\d+)/', $input, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    protected function simulatePythonScript($bokImport, $bookId)
    {
        // محاكاة تنفيذ السكربت
        sleep(2); // محاكاة الوقت
        
        $bokImport->update([
            'status' => 'completed',
            'title' => "كتاب تم استيراده من الشاملة - {$bookId}",
            'pages_count' => rand(100, 500),
        ]);
    }

    protected function getStatusLabel(): string
    {
        return match($this->status) {
            'idle' => '⚪ في الانتظار',
            'processing' => '🔄 جاري المعالجة...',
            'completed' => '✅ تم بنجاح',
            'failed' => '❌ فشل',
            default => 'غير معروف'
        };
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('start_import')
                ->label('بدء الاستيراد')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action('startImport')
                ->disabled(fn() => $this->status === 'processing'),
        ];
    }
}
