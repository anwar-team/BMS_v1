<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\BookSection;
use App\Support\DateHelper;
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
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'الكتب';
    protected static ?string $modelLabel = 'كتاب';
    protected static ?string $pluralModelLabel = 'الكتب';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('BookTabs')
                ->tabs([
                    // Tab 1: معلومات الكتاب الأساسية
                    Tab::make('معلومات الكتاب')
                        ->icon('heroicon-o-book-open')
                        ->schema([
                            self::getBasicInfoSection(),
                            self::getPublishingDetailsSection(),
                            self::getBookPropertiesSection(),
                            self::getCoverImageSection(),
                        ]),

                    // Tab 2: التصنيفات والمؤلفين
                    Tab::make('التصنيفات والمؤلفين')
                        ->icon('heroicon-o-tag')
                        ->schema([
                            self::getBookSectionSelect(),
                            self::getAuthorsRepeater(),
                        ]),

                    // Tab 3: المجلدات والفصول
                    Tab::make('المجلدات والفصول')
                        ->icon('heroicon-o-folder-open')
                        ->schema([
                            self::getVolumesRepeater(),
                        ]),

                    // Tab 4: الصفحات
                    Tab::make('الصفحات')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            self::getPagesRepeater(),
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
                        ->label('عنوان الكتاب')
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
                    ->label('وصف الكتاب')
                    ->rows(4)
                    ->maxLength(1000)
                    ->columnSpanFull(),

                Grid::make(2)->schema([
                    TextInput::make('slug')
                        ->label('الرابط الثابت')
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
                    
                    TextInput::make('isbn')
                        ->label('رقم ISBN')
                        ->maxLength(20)
                        ->placeholder('978-0-123456-78-9')
                        ->rule('isbn'),
                ]),
            ])
            ->collapsible();
    }

    private static function getPublishingDetailsSection(): Section
    {
        return Section::make('تفاصيل النشر')
            ->description('معلومات النشر والناشر وتاريخ الإصدار')
            ->icon('heroicon-o-calendar')
            ->schema([
                Grid::make(3)->schema([
                    Select::make('published_year_type')
                        ->label('نوع التقويم')
                        ->options([
                            'gregorian' => 'ميلادي',
                            'hijri' => 'هجري',
                        ])
                        ->default('gregorian')
                        ->live()
                        ->columnSpan(1),
                    
                    TextInput::make('published_year')
                        ->label(fn ($get) => $get('published_year_type') === 'hijri' ? 'سنة النشر (هجري)' : 'سنة النشر (ميلادي)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(fn ($get) => $get('published_year_type') === 'hijri' ? DateHelper::getCurrentHijriYear() : date('Y'))
                        ->placeholder(fn ($get) => $get('published_year_type') === 'hijri' ? 'مثال: ' . DateHelper::getCurrentHijriYear() : 'مثال: ' . date('Y'))
                        ->helperText(fn ($get) => $get('published_year_type') === 'hijri' ? 'أدخل السنة بالتقويم الهجري' : 'أدخل السنة بالتقويم الميلادي')
                        ->rules([
                            fn ($get) => function (string $attribute, $value, callable $fail) use ($get) {
                                if (!$value) return;
                                
                                $type = $get('published_year_type') ?? 'gregorian';
                                
                                if ($type === 'hijri' && !DateHelper::isValidHijriYear((int) $value)) {
                                    $fail('السنة الهجرية غير صحيحة. يجب أن تكون بين 1 و ' . DateHelper::getCurrentHijriYear());
                                } elseif ($type === 'gregorian' && !DateHelper::isValidGregorianYear((int) $value)) {
                                    $fail('السنة الميلادية غير صحيحة. يجب أن تكون بين 1 و ' . date('Y'));
                                }
                            },
                        ])
                        ->columnSpan(2),
                ]),

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
            ])
            ->collapsible();
    }

    private static function getBookPropertiesSection(): Section
    {
        return Section::make('خصائص الكتاب')
            ->description('الخصائص الفيزيائية والرقمية للكتاب')
            ->icon('heroicon-o-book-open')
            ->schema([
                Grid::make(4)->schema([
                    TextInput::make('pages_count')
                        ->label('عدد الصفحات')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('300'),
                    
                    TextInput::make('volumes_count')
                        ->label('عدد المجلدات')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('5')
                        ->default(1),
                    
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
                        ->relationship('author', 'fname')
                        ->searchable(['fname', 'lname'])
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->fname . ' ' . $record->lname))
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
                    
                    Toggle::make('is_main')
                        ->label('مؤلف رئيسي')
                        ->helperText('حدد المؤلف الرئيسي للكتاب')
                        ->default(false)
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
                Grid::make(3)->schema([
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
                    
                    TextInput::make('pages_count')
                        ->label('عدد الصفحات')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('300'),
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
            ->defaultItems(1)
            ->columnSpanFull();
    }

    private static function getChaptersRepeater(): Repeater
    {
        return Repeater::make('chapters')
            ->label('فصول هذا المجلد')
            ->relationship('chapters')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('chapter_number')
                        ->label('رقم الفصل')
                        ->numeric()
                        ->minValue(1)
                        ->default(1),
                    
                    TextInput::make('title')
                        ->label('عنوان الفصل')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('مثال: المقدمة'),
                ]),
                
                Textarea::make('summary')
                    ->label('ملخص الفصل')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),
                
                Grid::make(2)->schema([
                    TextInput::make('start_page')
                        ->label('الصفحة الأولى')
                        ->numeric()
                        ->minValue(1),
                    
                    TextInput::make('end_page')
                        ->label('الصفحة الأخيرة')
                        ->numeric()
                        ->minValue(1)
                        ->gte('start_page'),
                ]),
            ])
            ->addActionLabel('إضافة فصل جديد')
            ->reorderableWithButtons()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => 
                'فصل ' . ($state['chapter_number'] ?? '') . 
                ($state['title'] ? ' - ' . $state['title'] : 'جديد')
            )
            ->defaultItems(0)
            ->columnSpanFull();
    }

    private static function getPagesRepeater(): Repeater
    {
        return Repeater::make('pages')
            ->label('صفحات الكتاب')
            ->relationship('pages')
            ->schema([
                Grid::make(3)->schema([
                    TextInput::make('page_number')
                        ->label('رقم الصفحة')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->default(1),
                    
                    Select::make('volume_id')
                        ->label('المجلد')
                        ->relationship('volume', 'title')
                        ->searchable()
                        ->preload(),
                    
                    Select::make('chapter_id')
                        ->label('الفصل')
                        ->relationship('chapter', 'title')
                        ->searchable()
                        ->preload(),
                ]),
                
                TextInput::make('title')
                    ->label('عنوان الصفحة')
                    ->maxLength(255)
                    ->placeholder('عنوان اختياري للصفحة')
                    ->columnSpanFull(),
                
                RichEditor::make('content')
                    ->label('محتوى الصفحة')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'heading',
                        'subheading',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'codeBlock',
                        'attachFiles',
                    ])
                    ->columnSpanFull(),
                
                Grid::make(2)->schema([
                    FileUpload::make('image')
                        ->label('صورة الصفحة')
                        ->image()
                        ->maxSize(5120)
                        ->directory('books/pages')
                        ->visibility('public'),
                    
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('ملاحظات خاصة بالصفحة'),
                ]),
                
                Toggle::make('is_published')
                    ->label('منشورة')
                    ->default(true)
                    ->inline(false),
            ])
            ->addActionLabel('إضافة صفحة جديدة')
            ->reorderableWithButtons()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => 
                'صفحة ' . ($state['page_number'] ?? 'جديدة') . 
                ($state['title'] ? ' - ' . $state['title'] : '')
            )
            ->defaultItems(0)
            ->columnSpanFull();
    }

    // Helper methods for forms
    private static function getPublisherForm(): array
    {
        return [
            TextInput::make('name')->label('اسم الناشر')->required(),
            TextInput::make('country')->label('البلد')->required(),
            TextInput::make('email')->label('البريد الإلكتروني')->email(),
            TextInput::make('phone')->label('رقم الهاتف'),
            Textarea::make('description')->label('الوصف'),
            TextInput::make('website_url')->label('رابط الموقع')->url(),
            Toggle::make('is_active')->label('نشط')->default(true),
        ];
    }

    private static function getBookSectionForm(): array
    {
        return [
            TextInput::make('name')->label('اسم القسم')->required(),
            Textarea::make('description')->label('وصف القسم'),
            Select::make('parent_id')
                ->relationship('parent', 'name')
                ->label('القسم الرئيسي')
                ->default(null),
            TextInput::make('sort_order')
                ->label('ترتيب القسم')
                ->required()
                ->numeric()
                ->default(0),
            Toggle::make('is_active')
                ->label('نشط')
                ->default(true),
            TextInput::make('slug')
                ->label('الرابط الثابت')
                ->required()
                ->maxLength(255)
                ->unique(BookSection::class, 'slug', ignoreRecord: true)
                ->live(onBlur: true)
                ->suffixAction(
                    Action::make('generateSlug')
                        ->icon('heroicon-m-sparkles')
                        ->tooltip('توليد تلقائي من الاسم')
                        ->action(function (callable $set, callable $get) {
                            if ($get('name')) {
                                $set('slug', Str::slug($get('name')));
                            }
                        })
                ),
        ];
    }

    private static function getAuthorForm(): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('fname')
                    ->label('الاسم الأول')
                    ->required()
                    ->maxLength(100),
                TextInput::make('mname')
                    ->label('اسم الأب')
                    ->required()
                    ->maxLength(100),
                TextInput::make('lname')
                    ->label('الاسم الأخير')
                    ->required()
                    ->maxLength(100),
                TextInput::make('nationality')
                    ->label('الجنسية')
                    ->maxLength(100)
                    ->placeholder('مثال: سعودي، مصري، سوري'),
            ]),

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
                
                Toggle::make('is_living')
                    ->label('هل المؤلف على قيد الحياة؟')
                    ->default(true)
                    ->live(),
            ]),

            Textarea::make('biography')
                ->label('السيرة الذاتية')
                ->rows(4)
                ->columnSpanFull(),
            
            // Birth year fields
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
                    ->maxValue(fn ($get) => $get('birth_year_type') === 'hijri' ? DateHelper::getCurrentHijriYear() : date('Y')),
            ]),
            
            // Death year fields (conditional)
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
                    ->maxValue(fn ($get) => $get('death_year_type') === 'hijri' ? DateHelper::getCurrentHijriYear() : date('Y'))
                    ->visible(fn ($get) => !$get('is_living'))
                    ->nullable(),
            ]),
        ];
    }

    private static function getAuthorItemLabel(array $state): ?string
    {
        if (isset($state['author_id']) && $state['author_id']) {
            $author = Author::find($state['author_id']);
            if ($author) {
                $name = trim($author->fname . ' ' . $author->lname);
                $role = match($state['role'] ?? 'author') {
                    'author' => 'مؤلف',
                    'co_author' => 'مؤلف مشارك',
                    'editor' => 'محرر',
                    'translator' => 'مترجم',
                    'reviewer' => 'مراجع',
                    'commentator' => 'معلق',
                    default => $state['role'] ?? 'مؤلف'
                };
                $mainIndicator = ($state['is_main'] ?? false) ? ' - رئيسي' : '';
                return $name . ' (' . $role . ')' . $mainIndicator;
            }
        }
        return 'مؤلف جديد';
    }
        
 
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('الغلاف')
                    ->circular()
                    ->size(60),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الكتاب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('authors')
                    ->label('المؤلفون')
                    ->formatStateUsing(function ($record) {
                        return $record->authorBooks->sortBy('display_order')->map(function ($authorBook) {
                            $author = $authorBook->author;
                            $name = trim($author->fname . ' ' . $author->lname);
                            $role = match($authorBook->role) {
                                'author' => 'مؤلف',
                                'co_author' => 'مؤلف مشارك',
                                'editor' => 'محرر',
                                'translator' => 'مترجم',
                                'reviewer' => 'مراجع',
                                'commentator' => 'معلق',
                                default => $authorBook->role
                            };
                            $mainIndicator = $authorBook->is_main ? ' ⭐' : '';
                            return '<div class="text-sm"><strong>' . $name . '</strong><br><span class="text-gray-500">' . $role . $mainIndicator . '</span></div>';
                        })->join('<br>');
                    })
                    ->html()
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('authors', function ($query) use ($search) {
                            $query->where('fname', 'like', "%{$search}%")
                                  ->orWhere('lname', 'like', "%{$search}%");
                        });
                    })
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('bookSection.name')
                    ->label('القسم')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('published_year')
                    ->label('سنة النشر')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($record) =>
                        $record->published_year ?
                        DateHelper::formatYear($record->published_year, $record->published_year_type ?? 'gregorian') :
                        '-'
                    ),
                
                Tables\Columns\TextColumn::make('publisher')
                    ->label('الناشر')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('عدد الصفحات')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('volumes_count')
                    ->label('عدد المجلدات')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('visibility')
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
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'منشور',
                        'draft' => 'مسودة',
                        'archived' => 'مؤرشف',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('source_url')
                    ->label('رابط المصدر')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->source_url)
                    ->url(fn ($record) => $record->source_url)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('book_section_id')
                    ->label('القسم')
                    ->relationship('bookSection', 'name')
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('authors')
                    ->label('المؤلف')
                    ->relationship('authors', 'fname')
                    ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->fname . ' ' . $record->lname))
                    ->multiple(),
                
                Tables\Filters\Filter::make('published_year')
                    ->form([
                        Forms\Components\DatePicker::make('published_from')
                            ->label('من سنة'),
                        Forms\Components\DatePicker::make('published_until')
                            ->label('إلى سنة'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['published_from'],
                                fn (Builder $query, $date): Builder => $query->whereYear('published_year', '>=', $date),
                            )
                            ->when(
                                $data['published_until'],
                                fn (Builder $query, $date): Builder => $query->whereYear('published_year', '<=', $date),
                            );
                    }),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'archived' => 'مؤرشف',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('visibility')
                    ->label('الرؤية')
                    ->options([
                        'public' => 'عام',
                        'private' => 'خاص',
                    ])
                    ->multiple(),
                
                Tables\Filters\Filter::make('volumes_count')
                    ->form([
                        Forms\Components\TextInput::make('volumes_from')
                            ->label('من عدد المجلدات')
                            ->numeric(),
                        Forms\Components\TextInput::make('volumes_until')
                            ->label('إلى عدد المجلدات')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['volumes_from'],
                                fn (Builder $query, $count): Builder => $query->where('volumes_count', '>=', $count),
                            )
                            ->when(
                                $data['volumes_until'],
                                fn (Builder $query, $count): Builder => $query->where('volumes_count', '<=', $count),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('نشر المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'published']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('إلغاء نشر المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'draft']);
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}