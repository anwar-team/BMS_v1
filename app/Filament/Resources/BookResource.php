<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\BookSection;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Carbon\Carbon;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Filament\Forms\Components\Hidden;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportAction;
use Filament\Notifications\Notification;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;
    protected static ?string $navigationGroup = null;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return 'الكتب';
    }

    public static function getModelLabel(): string
    {
        return 'كتاب';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الكتب';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المكتبة';
    }
    
    protected static ?int $navigationSort = -10;

    /**
     * Optimize queries to avoid N+1 Query Problem
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['volumes', 'pages'])
            ->with([
                'bookSection',
                'publisher',
                'authorBooks' => function ($query) {
                    $query->with('author')->orderBy('display_order');
                }
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('BookTabs')
                ->tabs([
                    // Tab 1: Basic Book Information
                    Tab::make('معلومات الكتاب')
                        ->icon('heroicon-o-book-open')
                        ->schema([
                            self::getBasicInfoSection(),
                            self::getBookPropertiesSection(),
                            self::getCoverImageSection(),
                        ]),

                    // Tab 2: Categories and Authors
                    Tab::make('الأقسام والمؤلفون')
                        ->icon('heroicon-o-tag')
                        ->schema([
                            self::getBookSectionSelect(),
                            self::getAuthorsRepeater(),
                        ]),

                    // Tab 3: Volumes and Chapters
                    Tab::make('الأجزاء والفصول')
                        ->icon('heroicon-o-folder-open')
                        ->schema([
                            self::getVolumesRepeater(),
                        ]),
                ])
                ->columnSpanFull()
                ->persistTabInQueryString(),
        ]);
    }

    private static function getBasicInfoSection(): Section
    {
        return Section::make('المعلومات الأساسية')
            ->description('أدخل العنوان والوصف والمعرف الفريد للكتاب')
            ->icon('heroicon-o-identification')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('title')
                        ->label('العنوان')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('slug', Str::slug($state));
                            }
                        })
                        ->columnSpan(2),
                ]),
                
                Textarea::make('description')
                    ->label('الوصف')
                    ->rows(4)
                    ->maxLength(1000)
                    ->columnSpanFull(),

                Grid::make(3)->schema([
                    TextInput::make('slug')
                        ->label('المعرف')
                        ->required()
                        ->maxLength(255)
                        ->unique(Book::class, 'slug', ignoreRecord: true)
                        ->rules(['alpha_dash'])
                        ->suffixAction(
                            Action::make('generateSlug')
                                ->icon('heroicon-m-sparkles')
                                ->tooltip('توليد تلقائي من العنوان')
                                ->action(function (callable $set, callable $get) {
                                    if ($get('title')) {
                                        $set('slug', Str::slug($get('title')));
                                    }
                                })
                        ),
                    
                    TextInput::make('edition')
                        ->label('رقم الطبعة')
                        ->numeric()
                        ->placeholder('1'),
                    
                    TextInput::make('edition_DATA')
                        ->label(' سنة الطباعة ')
                        ->numeric()
                        ->placeholder('2025'),
                ]),
            ])
            ->collapsible();
    }

    private static function getBookPropertiesSection(): Section
    {
        return Section::make('خصائص الكتاب')
            ->description('الخصائص الفيزيائية والرقمية للكتاب ومعلومات النشر')
            ->icon('heroicon-o-book-open')
            ->schema([
                Grid::make(2)->schema([
                    Select::make('publisher_id')
                        ->label('الناشر')
                        ->relationship('publisher', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm(self::getPublisherForm())
                        ->createOptionAction(function (Action $action) {
                            return $action
                                ->modalHeading('إضافة ناشر جديد')
                                ->modalSubmitActionLabel('إضافة الناشر')
                                ->modalWidth('lg');
                        }),
                    
                    TextInput::make('source_url')
                        ->label('رابط المصدر')
                        ->url()
                        ->placeholder('https://example.com'),
                ]),
                
                Grid::make(4)->schema([
                    Select::make('visibility')
                        ->label('الرؤية')
                        ->options([
                            'public' => 'عام',
                            'private' => 'خاص',
                        ])
                        ->required()
                        ->default('public'),
                    
                    Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'draft' => 'مسودة',
                            'published' => 'منشور',
                            'archived' => 'مؤرشف',
                        ])
                        ->required()
                        ->default('draft'),
                ]),
            ])
            ->collapsible();
    }

    private static function getCoverImageSection(): Section
    {
        return Section::make('صورة الغلاف')
            ->description('ارفع صورة غلاف عالية الجودة للكتاب')
            ->icon('heroicon-o-photo')
            ->schema([
                FileUpload::make('cover_image')
                    ->label('صورة الغلاف')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '3:4',
                        '2:3',
                    ])
                    ->maxSize(5120)
                    ->directory('books/covers')
                    ->visibility('public')
                    ->helperText('حجم أقصى: 5 ميجابايت. النسب المفضلة: 3:4 أو 2:3')
                    ->columnSpanFull(),
            ])
            ->collapsible();
    }

    private static function getBookSectionSelect(): Select
    {
        return Select::make('book_section_id')
            ->relationship('bookSection', 'name')
            ->label('قسم الكتاب')
            ->searchable()
            ->preload()
            ->createOptionForm(self::getBookSectionForm())
            ->createOptionAction(function (Action $action) {
                return $action
                    ->modalHeading('إضافة قسم جديد')
                    ->modalSubmitActionLabel('إضافة القسم')
                    ->modalWidth('lg');
            })
            ->required();
    }

    private static function getAuthorsRepeater(): Repeater
    {
        return Repeater::make('authorBooks')
            ->label('المؤلفون ودورهم')
            ->relationship('authorBooks')
            ->schema([
                Grid::make(4)->schema([
                    Select::make('author_id')
                        ->label('المؤلف')
                        ->relationship('author', 'full_name')
                        ->searchable(['full_name'])
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                        ->createOptionForm(self::getAuthorForm())
                        ->createOptionAction(function (Action $action) {
                            return $action
                                ->modalHeading('إضافة مؤلف جديد')
                                ->modalSubmitActionLabel('إضافة المؤلف')
                                ->modalWidth('xl');
                        })
                        ->required()
                        ->columnSpan(2),
                    
                    Select::make('role')
                        ->label('الدور')
                        ->options([
                            'author' => 'مؤلف',
                            'co_author' => 'مؤلف مشارك',
                            'editor' => 'محرر',
                            'translator' => 'مترجم',
                            'reviewer' => 'مراجع',
                            'commentator' => 'معلق',
                        ])
                        ->required()
                        ->default('author')
                        ->columnSpan(1),
                    
                    Select::make('is_main')
                        ->label('مؤلف رئيسي')
                        ->options([
                            true => 'نعم',
                            false => 'لا'
                        ])
                        ->helperText('حدد المؤلف الرئيسي للكتاب')
                        ->default(true)
                        ->columnSpan(1),
                ]),
                
                TextInput::make('display_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0)
                    ->helperText('ترتيب ظهور المؤلف في قائمة المؤلفين'),
            ])
            ->addActionLabel('إضافة مؤلف')
            ->reorderableWithButtons()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => self::getAuthorItemLabel($state))
            ->defaultItems(1)
            ->minItems(1)
            ->columnSpanFull();
    }

    private static function getVolumesRepeater(): Repeater
    {
        return Repeater::make('volumes')
            ->label('مجلدات الكتاب')
            ->relationship('volumes')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('number')
                        ->label('رقم المجلد')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->default(1),
                    
                    TextInput::make('title')
                        ->label('عنوان المجلد')
                        ->maxLength(255)
                        ->placeholder('مثال: الجزء الأول'),
                ]),
                
                Textarea::make('description')
                    ->label('وصف المجلد')
                    ->rows(2)
                    ->columnSpanFull(),

                self::getChaptersRepeater(),
            ])
            ->addActionLabel('إضافة مجلد جديد')
            ->reorderableWithButtons()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => 
                'مجلد ' . ($state['number'] ?? 'جديد') . 
                ($state['title'] ? ' - ' . $state['title'] : '')
            )
            ->maxItems(100)
            ->defaultItems(1)
            ->columnSpanFull();
    }

    private static function getChaptersRepeater(): Repeater
    {
        return Repeater::make('chapters')
            ->label('فصول هذا المجلد')
            ->relationship('chapters')
            ->schema([
                TextInput::make('title')
                    ->label('عنوان الفصل')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('مثال: المقدمة')
                    ->columnSpanFull(),
                
                // إضافة صفحات داخل الفصل
                self::getPagesRepeater('صفحات هذا الفصل'),
            ])
            ->addActionLabel('إضافة فصل جديد')
            ->reorderableWithButtons()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => 
                ($state['title'] ?? 'فصل جديد')
            )
            ->maxItems(200)
            ->defaultItems(0);
    }

    private static function getPagesRepeater(string $label = 'الصفحات'): Repeater
    {
        return Repeater::make('pages')
            ->label($label)
            ->relationship('pages')
            ->schema([
                Grid::make(3)->schema([
                    TextInput::make('page_number')
                        ->label('رقم الصفحة')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('1')
                        ->helperText('رقم الصفحة في الكتاب المطبوع'),
                    
                    TextInput::make('page_order')
                        ->label('ترتيب الصفحة')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->placeholder('0')
                        ->helperText('ترتيب ظهور الصفحة'),
                    
                    Toggle::make('is_indexed')
                        ->label('مفهرسة')
                        ->default(false)
                        ->helperText('هل تم فهرسة الصفحة في البحث؟'),
                ]),
                
                RichEditor::make('content')
                    ->label('محتوى الصفحة')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'bulletList',
                        'orderedList',
                        'h2',
                        'h3',
                        'redo',
                        'undo',
                    ])
                    ->placeholder('أدخل محتوى الصفحة هنا...')
                    ->columnSpanFull(),
                
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(2)
                    ->placeholder('ملاحظات إضافية عن الصفحة (اختياري)')
                    ->columnSpanFull(),
            ])
            ->addActionLabel('إضافة صفحة جديدة')
            ->reorderableWithButtons()
            ->collapsible()
            ->collapsed()
            ->itemLabel(fn (array $state): ?string => 
                'صفحة ' . ($state['page_number'] ?? 'جديدة') . 
                (isset($state['content']) && strlen($state['content']) > 50 
                    ? ' - ' . Str::limit(strip_tags($state['content']), 30) 
                    : '')
            )
            ->maxItems(500)
            ->defaultItems(0)
            ->columnSpanFull();
    }

    private static function getAuthorForm(): array
    {
        return [
            TextInput::make('full_name')
                ->label('الاسم الكامل')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            FileUpload::make('image')
                ->label('صورة المؤلف')
                ->image()
                ->directory('authors')
                ->visibility('public')
                ->columnSpanFull(),

            Grid::make(2)->schema([
                Select::make('madhhab')
                    ->label('المذهب')
                    ->options([
                        'المذهب الحنفي' => 'المذهب الحنفي',
                        'المذهب المالكي' => 'المذهب المالكي',
                        'المذهب الشافعي' => 'المذهب الشافعي',
                        'المذهب الحنبلي' => 'المذهب الحنبلي',
                        'آخرون' => 'آخرون',
                    ])
                    ->placeholder('اختر المذهب'),
                
                Select::make('is_living')
                    ->label('حالة المؤلف')
                    ->options([
                        true => 'على قيد الحياة',
                        false => 'متوفى',
                    ])
                    ->default(true)
                    ->live(),
            ]),

            Textarea::make('biography')
                ->label('السيرة الذاتية')
                ->rows(4)
                ->columnSpanFull(),
            
            Grid::make(2)->schema([
                Select::make('birth_year_type')
                    ->label('نوع تقويم الميلاد')
                    ->options([
                        'gregorian' => 'ميلادي',
                        'hijri' => 'هجري',
                    ])
                    ->default('gregorian')
                    ->live(),
                
                TextInput::make('birth_year')
                    ->label(fn ($get) => $get('birth_year_type') === 'hijri' ? 'سنة الميلاد (هجري)' : 'سنة الميلاد (ميلادي)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(fn ($get) => $get('birth_year_type') === 'hijri' ? 1500 : date('Y')),
            ]),
            
            Grid::make(2)->schema([
                Select::make('death_year_type')
                    ->label('نوع تقويم الوفاة')
                    ->options([
                        'gregorian' => 'ميلادي',
                        'hijri' => 'هجري',
                    ])
                    ->default('gregorian')
                    ->live()
                    ->visible(fn ($get) => !$get('is_living')),
                
                TextInput::make('death_year')
                    ->label(fn ($get) => $get('death_year_type') === 'hijri' ? 'سنة الوفاة (هجري)' : 'سنة الوفاة (ميلادي)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(fn ($get) => $get('death_year_type') === 'hijri' ? 1500 : date('Y'))
                    ->visible(fn ($get) => !$get('is_living'))
                    ->nullable(),
            ]),
        ];
    }

    private static function getPublisherForm(): array
    {
        return [
            TextInput::make('name')
                ->label('اسم الناشر')
                ->required()
                ->maxLength(255),
            
            TextInput::make('address')
                ->label('العنوان')
                ->maxLength(255),
            
            TextInput::make('phone')
                ->label('رقم الهاتف')
                ->tel()
                ->maxLength(20),
            
            TextInput::make('email')
                ->label('البريد الإلكتروني')
                ->email()
                ->maxLength(255),
            
            TextInput::make('website_url')
                ->label('الموقع الإلكتروني')
                ->url()
                ->maxLength(255),
            
            Textarea::make('description')
                ->label('وصف الناشر')
                ->rows(3)
                ->maxLength(1000)
                ->columnSpanFull(),
            
            FileUpload::make('image')
                ->label('صورة الناشر')
                ->image()
                ->imageEditor()
                ->maxSize(2048)
                ->directory('publishers')
                ->visibility('public')
                ->columnSpanFull(),
        ];
    }

    private static function getBookSectionForm(): array
    {
        return [
            TextInput::make('name')
                ->label('اسم القسم')
                ->required()
                ->maxLength(255),
            
            TextInput::make('slug')
                ->label('الرابط الثابت')
                ->maxLength(255)
                ->unique(BookSection::class, 'slug', ignoreRecord: true)
                ->rules(['alpha_dash']),
            
            Textarea::make('description')
                ->label('وصف القسم')
                ->rows(3)
                ->columnSpanFull(),
        ];
    }

    private static function getAuthorItemLabel(array $state): ?string
    {
        if (!isset($state['author_id'])) {
            return 'مؤلف جديد';
        }
        
        $author = Author::find($state['author_id']);
        $role = $state['role'] ?? 'author';
        
        $roleLabels = [
            'author' => 'مؤلف',
            'co_author' => 'مؤلف مشارك',
            'editor' => 'محرر',
            'translator' => 'مترجم',
            'reviewer' => 'مراجع',
            'commentator' => 'معلق',
        ];
        
        return ($author ? $author->full_name : 'غير محدد') . ' - ' . ($roleLabels[$role] ?? $role);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // الأعمدة الثابتة (غير قابلة للإخفاء)
                //ImageColumn::make('cover_image')
                //    ->label('الغلاف')
                //    ->circular()
                //    ->size(60)
                //    ->defaultImageUrl(url('/images/default-book-cover.png'))
                //    ->toggleable(),
                
                TextColumn::make('title')
                    ->label('عنوان الكتاب')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(),
                
                TextColumn::make('mainAuthors')
                    ->label('المؤلف الرئيسي')
                    ->getStateUsing(function ($record) {
                        $mainAuthor = $record->authorBooks
                            ->where('is_main', true)
                            ->first();
                        
                        if ($mainAuthor && $mainAuthor->author) {
                            return $mainAuthor->author->full_name;
                        }
                        
                        $firstAuthor = $record->authorBooks->first();
                        return $firstAuthor?->author?->full_name ?? 'غير محدد';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('authorBooks.author', function (Builder $query) use ($search) {
                            $query->where('full_name', 'like', "%{$search}%");
                        });
                    })
                    ->limit(30)
                    ->toggleable(),
                
                TextColumn::make('bookSection.name')
                    ->label('القسم')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->toggleable()
                    ->color('info'),
                
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'archived' => 'مؤرشف',
                        default => $state,
                    })
                    ->toggleable(),
                
                // الأعمدة القابلة للإخفاء/الإظهار
                TextColumn::make('publisher.name')
                    ->label('الناشر')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                
                TextColumn::make('edition')
                    ->label('الطبعة')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (?int $state): string => $state ? "الطبعة {$state}" : 'غير محدد')
                    ->toggleable(),
                
                TextColumn::make('edition_DATA')
                    ->label('سنة الطباعة')
                    ->sortable()
                    ->badge()
                    ->color('secondary')
                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state}" : 'غير محدد')
                    ->toggleable(),
                
                TextColumn::make('volumes_count')
                    ->label('المجلدات')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(fn ($record) => $record->volumes_count ?? 0)
                    ->toggleable(),
                
                TextColumn::make('pages_count')
                    ->label('الصفحات')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn ($record) => $record->pages_count ?? 0)
                    ->toggleable(),
                
                TextColumn::make('visibility')
                    ->label('الرؤية')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'private' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'public' => 'عام',
                        'private' => 'خاص',
                        default => $state,
                    })
                    ->toggleable(),
                
                // الأعمدة الإضافية المخفية افتراضياً
                TextColumn::make('shamela_id')
                    ->label('معرف شاملة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (?string $state): string => $state ? $state : 'غير محدد')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('source_url')
                    ->label('رابط المصدر')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'متوفر' : 'غير متوفر')
                    ->badge()
                    ->color(fn (?string $state): string => $state ? 'success' : 'danger')
                    ->url(fn ($record) => $record->source_url)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(100)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!$state || strlen($state) <= 100) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('slug')
                    ->label('الرابط الثابت')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('id')
                    ->label('المعرف')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('has_cover')
                    ->label('صورة الغلاف')
                    ->getStateUsing(fn ($record) => $record->cover_image ? 'متوفرة' : 'غير متوفرة')
                    ->badge()
                    ->color(fn ($record) => $record->cover_image ? 'success' : 'danger')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('authors_count')
                    ->label('عدد المؤلفين')
                    ->getStateUsing(fn ($record) => $record->authorBooks->count())
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // فلاتر مرئية فوق الجدول
                SelectFilter::make('book_section_id')
                    ->label('قسم الكتاب')
                    ->relationship('bookSection', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('جميع الأقسام'),

                SelectFilter::make('publisher_id')
                    ->label('الناشر')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('جميع الناشرين'),

                SelectFilter::make('author')
                    ->label('المؤلف')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas(
                                'authorBooks.author',
                                fn (Builder $query): Builder => $query->where('id', $value)
                            )
                        );
                    })
                    ->options(function (): array {
                        return Author::whereHas('books')
                            ->orderBy('full_name')
                            ->pluck('full_name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->placeholder('جميع المؤلفين'),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'archived' => 'مؤرشف',
                    ])
                    ->placeholder('جميع الحالات'),

                SelectFilter::make('visibility')
                    ->label('الرؤية')
                    ->options([
                        'public' => 'عام',
                        'private' => 'خاص',
                    ])
                    ->placeholder('جميع أنواع الرؤية'),

                SelectFilter::make('author_role')
                    ->label('دور المؤلف')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas(
                                'authorBooks',
                                fn (Builder $query): Builder => $query->where('role', $value)
                            )
                        );
                    })
                    ->options([
                        'author' => 'مؤلف',
                        'co_author' => 'مؤلف مشارك',
                        'editor' => 'محرر',
                        'translator' => 'مترجم',
                        'reviewer' => 'مراجع',
                        'commentator' => 'معلق',
                    ])
                    ->placeholder('جميع الأدوار'),


                TernaryFilter::make('has_source_url')
                    ->label('رابط المصدر')
                    ->placeholder('الكل')
                    ->trueLabel('مع رابط مصدر')
                    ->falseLabel('بدون رابط مصدر')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('source_url')->where('source_url', '!=', ''),
                        false: fn (Builder $query) => $query->where(function ($query) {
                            $query->whereNull('source_url')->orWhere('source_url', '');
                        }),
                        blank: fn (Builder $query) => $query,
                    ),

                Filter::make('pages_count_range')
                    ->form([
                        Grid::make(2)->schema([
                            TextInput::make('pages_from')
                                ->label('عدد الصفحات من')
                                ->numeric()
                                ->placeholder('10'),
                            TextInput::make('pages_to')
                                ->label('إلى')
                                ->numeric()
                                ->placeholder('1000'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['pages_from'],
                                fn (Builder $query, $value): Builder => $query->whereHas('pages', function ($query) use ($value) {
                                    $query->havingRaw('COUNT(*) >= ?', [$value]);
                                })
                            )
                            ->when(
                                $data['pages_to'],
                                fn (Builder $query, $value): Builder => $query->whereHas('pages', function ($query) use ($value) {
                                    $query->havingRaw('COUNT(*) <= ?', [$value]);
                                })
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['pages_from'] || $data['pages_to']) {
                            return 'عدد الصفحات: ' . ($data['pages_from'] ?? '∞') . ' - ' . ($data['pages_to'] ?? '∞');
                        }
                        return null;
                    }),

                Filter::make('pages_count_exact')
                    ->form([
                        TextInput::make('exact_pages')
                            ->label('عدد الصفحات المحدد')
                            ->numeric()
                            ->placeholder('أدخل عدد الصفحات')
                            ->helperText('مثال: 100'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['exact_pages'],
                            fn (Builder $query, $value): Builder => $query->whereHas('pages', function ($query) use ($value) {
                                $query->havingRaw('COUNT(*) = ?', [$value]);
                            })
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['exact_pages']) {
                            return 'عدد الصفحات المحدد: ' . $data['exact_pages'];
                        }
                        return null;
                    }),

                TernaryFilter::make('has_pages')
                    ->label('وجود صفحات')
                    ->placeholder('الكل')
                    ->trueLabel('لديه صفحات')
                    ->falseLabel('بدون صفحات')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('pages'),
                        false: fn (Builder $query) => $query->whereDoesntHave('pages'),
                        blank: fn (Builder $query) => $query,
                    ),
            ], layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label('تصدير البيانات')
                    ->color('success')
                    ->fileName('books-export')
                    ->defaultFormat('xlsx')
                    ->defaultPageOrientation('landscape')
                    ->fileNameFieldLabel('اسم الملف')
                    ->formatFieldLabel('التنسيق')
                    ->pageOrientationFieldLabel('اتجاه الصفحة')
                    ->filterColumnsFieldLabel('تصفية الأعمدة')
                    ->additionalColumnsFieldLabel('أعمدة إضافية')
                    ->additionalColumnsTitleFieldLabel('العنوان')
                    ->additionalColumnsDefaultValueFieldLabel('القيمة الافتراضية')
                    ->additionalColumnsAddButtonLabel('إضافة عمود'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('عرض')
                    ->color('info'),
                EditAction::make()
                    ->label('تعديل')
                    ->color('warning'),
                DeleteAction::make()
                    ->label('حذف')
                    ->color('danger'),
                Tables\Actions\Action::make('view_book')
                    ->label('عرض الكتاب')
                    ->icon('heroicon-o-book-open')
                    ->color('success')
                    ->url(fn (Book $record): string => 'https://home.anwaralolmaa.com/book/' . $record->id)
                    ->openUrlInNewTab(),
                
                
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    FilamentExportBulkAction::make('export')
                        ->label('تصدير المحدد')
                        ->color('success')
                        ->fileName('selected-books-export')
                        ->defaultFormat('xlsx')
                        ->defaultPageOrientation('landscape')
                        ->fileNameFieldLabel('اسم الملف')
                        ->formatFieldLabel('التنسيق')
                        ->pageOrientationFieldLabel('اتجاه الصفحة')
                        ->filterColumnsFieldLabel('تصفية الأعمدة')
                        ->additionalColumnsFieldLabel('أعمدة إضافية')
                        ->additionalColumnsTitleFieldLabel('العنوان')
                        ->additionalColumnsDefaultValueFieldLabel('القيمة الافتراضية')
                        ->additionalColumnsAddButtonLabel('إضافة عمود'),
                    DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                    
                    BulkAction::make('publish')
                        ->label('نشر المحدد')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'published']);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    BulkAction::make('archive')
                        ->label('أرشفة المحدد')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'archived']);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    BulkAction::make('assign_to_section')
                        ->label('تعيين إلى قسم')
                        ->icon('heroicon-o-folder')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('book_section_id')
                                ->label('اختر القسم الجديد')
                                ->relationship('bookSection', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder('اختر القسم المطلوب')
                                ->helperText('سيتم تعيين جميع الكتب المحددة إلى هذا القسم'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['book_section_id' => $data['book_section_id']]);
                            });
                            
                            Notification::make()
                                ->title('تم تعيين الكتب بنجاح')
                                ->body('تم تعيين ' . $records->count() . ' كتاب إلى القسم الجديد')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('تعيين الكتب إلى قسم جديد')
                        ->modalDescription('هل أنت متأكد من تعيين الكتب المحددة إلى القسم الجديد؟')
                        ->modalSubmitActionLabel('تعيين الكتب')
                        ->modalCancelActionLabel('إلغاء'),
                ])
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('60s')
            ->deferLoading()
            ->toggleColumnsTriggerAction(
                fn (\Filament\Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('إظهار/إخفاء الأعمدة')
                    ->icon('heroicon-o-view-columns')
                    ->color('gray')
            );
    }

    public static function getRelations(): array
    {
        return [
            BookResource\RelationManagers\PagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'view' => Pages\ViewBook::route('/{record}'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'isbn'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        $mainAuthor = $record->authorBooks->where('is_main', true)->first()
            ?? $record->authorBooks->first();
        
        return [
            'المؤلف' => $mainAuthor?->author?->full_name ?? 'غير محدد',
            'القسم' => $record->bookSection?->name ?? 'غير محدد',
            'الناشر' => $record->publisher?->name ?? 'غير محدد',
        ];
    }

    /**
     * تنظيف عنوان الكتاب للبحث عن التشابه
     * يزيل الكلمات الشائعة والرموز غير المهمة
     */
    private static function cleanTitleForSimilarity(string $title): string
    {
        // تحويل إلى أحرف صغيرة
        $title = mb_strtolower($title, 'UTF-8');
        
        // إزالة علامات الترقيم والرموز
        $title = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $title);
        
        // إزالة الكلمات الشائعة في العربية
        $commonWords = [
            'في', 'من', 'إلى', 'على', 'عن', 'مع', 'كتاب', 'شرح', 'تفسير',
            'الـ', 'ال', 'و', 'أو', 'لكن', 'غير', 'سوى', 'إلا', 'بل',
            'ط', 'طبعة', 'الطبعة', 'مطبعة', 'دار', 'مؤسسة', 'منشورات'
        ];
        
        foreach ($commonWords as $word) {
            $title = preg_replace('/\b' . preg_quote($word, '/') . '\b/u', ' ', $title);
        }
        
        // إزالة المسافات الزائدة
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title);
        
        return $title;
    }
}
