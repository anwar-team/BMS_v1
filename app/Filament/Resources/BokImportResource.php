<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BokImportResource\Pages;
use App\Services\BokConverterService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use App\Models\Book;

class BokImportResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    
    protected static ?string $navigationLabel = 'استيراد ملفات BOK';
    
    protected static ?string $modelLabel = 'استيراد BOK';
    
    protected static ?string $pluralModelLabel = 'استيراد ملفات BOK';
    
    protected static ?string $navigationGroup = 'إدارة الكتب';
    
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('رفع الملف')
                        ->schema([
                            Section::make('رفع ملف BOK')
                                ->description('اختر ملف .bok من المكتبة الشاملة لتحويله إلى قاعدة البيانات')
                                ->schema([
                                    FileUpload::make('bok_file')
                                        ->label('ملف BOK')
                                        ->acceptedFileTypes(['.bok'])
                                        ->directory('bok-imports')
                                        ->preserveFilenames()
                                        ->maxSize(100 * 1024) // 100MB
                                        ->required()
                                        ->helperText('يجب أن يكون الملف بصيغة .bok من المكتبة الشاملة')
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state) {
                                                // تحليل الملف وعرض معلومات أولية
                                                $filePath = Storage::path($state);
                                                $analyzer = new BokConverterService();
                                                
                                                try {
                                                    // يمكن إضافة تحليل أولي هنا
                                                    $set('file_ready', true);
                                                } catch (\Exception $e) {
                                                    Notification::make()
                                                        ->title('خطأ في تحليل الملف')
                                                        ->body($e->getMessage())
                                                        ->danger()
                                                        ->send();
                                                }
                                            }
                                        }),
                                        
                                    Toggle::make('file_ready')
                                        ->label('الملف جاهز للتحليل')
                                        ->disabled()
                                        ->default(false),
                                ])
                        ]),
                        
                    Wizard\Step::make('معاينة البيانات')
                        ->schema([
                            Section::make('معلومات الكتاب المستخرجة')
                                ->description('راجع المعلومات المستخرجة من ملف BOK قبل الحفظ')
                                ->schema([
                                    TextInput::make('extracted_title')
                                        ->label('عنوان الكتاب')
                                        ->disabled()
                                        ->default('سيتم استخراجه من الملف'),
                                        
                                    TextInput::make('extracted_author')
                                        ->label('المؤلف')
                                        ->disabled()
                                        ->default('سيتم استخراجه من الملف'),
                                        
                                    Textarea::make('extracted_description')
                                        ->label('الوصف')
                                        ->disabled()
                                        ->rows(3)
                                        ->default('سيتم استخراجه من الملف'),
                                        
                                    TextInput::make('extracted_pages_count')
                                        ->label('عدد الصفحات')
                                        ->disabled()
                                        ->default('0'),
                                        
                                    TextInput::make('extracted_chapters_count')
                                        ->label('عدد الفصول')
                                        ->disabled()
                                        ->default('0'),
                                ])
                        ]),
                        
                    Wizard\Step::make('خيارات التحويل')
                        ->schema([
                            Section::make('إعدادات التحويل')
                                ->description('اختر الإعدادات المناسبة لعملية التحويل')
                                ->schema([
                                    Toggle::make('auto_detect_structure')
                                        ->label('الكشف التلقائي عن هيكل الكتاب')
                                        ->helperText('محاولة تحديد الفصول والأجزاء تلقائياً')
                                        ->default(true),
                                        
                                    Toggle::make('clean_text')
                                        ->label('تنظيف النصوص')
                                        ->helperText('إزالة الأحرف غير المرغوبة وتنسيق النصوص')
                                        ->default(true),
                                        
                                    Toggle::make('create_indexes')
                                        ->label('إنشاء فهارس البحث')
                                        ->helperText('إنشاء فهارس لتسريع البحث في النصوص')
                                        ->default(true),
                                        
                                    TextInput::make('default_language')
                                        ->label('اللغة الافتراضية')
                                        ->default('ar')
                                        ->required(),
                                        
                                    Forms\Components\Select::make('import_status')
                                        ->label('حالة الكتاب بعد الاستيراد')
                                        ->options([
                                            'draft' => 'مسودة',
                                            'published' => 'منشور',
                                            'archived' => 'مؤرشف'
                                        ])
                                        ->default('published')
                                        ->required(),
                                ])
                        ]),
                        
                    Wizard\Step::make('التحويل')
                        ->schema([
                            Section::make('تنفيذ التحويل')
                                ->description('سيتم تحويل ملف BOK إلى قاعدة البيانات')
                                ->schema([
                                    Forms\Components\Placeholder::make('conversion_status')
                                        ->label('حالة التحويل')
                                        ->content('جاهز للبدء'),
                                        
                                    Forms\Components\Placeholder::make('conversion_progress')
                                        ->label('التقدم')
                                        ->content('0%'),
                                        
                                    Textarea::make('conversion_log')
                                        ->label('سجل التحويل')
                                        ->rows(10)
                                        ->disabled()
                                        ->default('سيتم عرض تفاصيل عملية التحويل هنا...'),
                                ])
                        ])
                ])
                ->submitAction(\Filament\Forms\Components\Actions\Action::make('convert')
                    ->label('بدء التحويل')
                    ->action(function (array $data) {
                        return static::performConversion($data);
                    })
                )
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الكتاب')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('authors.name')
                    ->label('المؤلف')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('language')
                    ->label('اللغة')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'warning',
                    }),
                    
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('عدد الصفحات')
                    ->counts('pages')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الاستيراد')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'archived' => 'مؤرشف'
                    ]),
                    
                Tables\Filters\SelectFilter::make('language')
                    ->label('اللغة')
                    ->options([
                        'ar' => 'العربية',
                        'en' => 'الإنجليزية'
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBokImports::route('/'),
            'create' => Pages\CreateBokImport::route('/create'),
            'view' => Pages\ViewBokImport::route('/{record}'),
            'edit' => Pages\EditBokImport::route('/{record}/edit'),
        ];
    }
    
    /**
     * تنفيذ عملية التحويل
     */
    public static function performConversion(array $data): void
    {
        try {
            $bokFile = $data['bok_file'];
            $filePath = Storage::path($bokFile);
            
            $converter = new BokConverterService();
            $result = $converter->convertBokFile($filePath);
            
            if ($result['success']) {
                Notification::make()
                    ->title('تم التحويل بنجاح')
                    ->body("تم تحويل الكتاب '{$result['title']}' بنجاح")
                    ->success()
                    ->send();
                    
                // إعادة توجيه إلى صفحة الكتاب
                redirect()->route('filament.admin.resources.books.view', $result['book_id']);
            } else {
                Notification::make()
                    ->title('فشل في التحويل')
                    ->body($result['error'])
                    ->danger()
                    ->send();
            }
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في التحويل')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * تحليل ملف BOK واستخراج معلومات أولية
     */
    public static function analyzeBokFile(string $filePath): array
    {
        try {
            $converter = new BokConverterService();
            // يمكن إضافة تحليل أولي هنا
            
            return [
                'title' => 'عنوان مستخرج من الملف',
                'author' => 'مؤلف مستخرج من الملف',
                'description' => 'وصف مستخرج من الملف',
                'pages_count' => 0,
                'chapters_count' => 0
            ];
            
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }
}