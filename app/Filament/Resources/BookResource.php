<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\BookManagement;
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
use Filament\Tables\Filters\DateRangeFilter;
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

class BookResource extends Resource
{
    protected static ?string $model = Book::class;
    protected static ?string $cluster = BookManagement::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'الكتب';
    protected static ?string $modelLabel = 'كتاب';
    protected static ?string $pluralModelLabel = 'الكتب';
    protected static ?int $navigationSort = 1;

    /**
     * تحسين الاستعلامات لتجنب N+1 Query Problem
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
                    // Tab 1: معلومات الكتاب الأساسية
                    Tab::make('معلومات الكتاب')
                        ->icon('heroicon-o-book-open')
                        ->schema([
                            self::getBasicInfoSection(),
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

                Grid::make(3)->schema([
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
                    
                    //TextInput::make('pages_count')
                    //    ->label('عدد الصفحات')
                    //    ->numeric()
                    //    ->minValue(1)
                    //    ->placeholder('300'),
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
            ->maxItems(100) // حد أقصى 100 مجلد لتحسين الأداء
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
            ])
            ->addActionLabel('إضافة فصل جديد')
            ->reorderableWithButtons()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => 
                ($state['title'] ?? 'فصل جديد')
            )
            ->maxItems(200) // حد أقصى 200 فصل لتحسين الأداء
            ->defaultItems(0);
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
                    ->maxValue(fn ($get) => $get('birth_year_type') === 'hijri' ? 1500 : date('Y')),
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
                ImageColumn::make('cover_image')
                    ->label('الغلاف')
                    ->circular()
                    ->size(60)
                    ->defaultImageUrl(url('/images/default-book-cover.png')),
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
                    }),
                TextColumn::make('mainAuthors')
                    ->label('المؤلف الرئيسي')
                    ->getStateUsing(function ($record) {
                        // استخدام البيانات المحملة مسبقاً من getEloquentQuery()
                        $mainAuthor = $record->authorBooks
                            ->where('is_main', true)
                            ->first();
                        
                        if ($mainAuthor && $mainAuthor->author) {
                            return $mainAuthor->author->full_name;
                        }
                        
                        // في حالة عدم وجود مؤلف رئيسي، أخذ أول مؤلف
                        $firstAuthor = $record->authorBooks->first();
                        return $firstAuthor?->author?->full_name ?? 'غير محدد';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('authorBooks.author', function (Builder $query) use ($search) {
                            $query->where('full_name', 'like', "%{$search}%");
                        });
                    })
                    ->limit(30),
                TextColumn::make('bookSection.name')
                    ->label('القسم')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
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
                    ->badge()
                    ->color('secondary')
                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state}" : 'غير محدد')
                    ->toggleable(),
                TextColumn::make('volumes_count')
                    ->label('المجلدات')
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                TextColumn::make('pages_count')
                    ->label('الصفحات')
                    ->badge()
                    ->color('warning')
                    ->toggleable(),
                TextColumn::make('source_url')
                    ->label('رابط المصدر')
                    ->limit(50)
                    ->url(fn ($record) => $record->source_url)
                    ->openUrlInNewTab()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'متوفر' : 'غير متوفر')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(100)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 100) {
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
                    }),
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
                TextColumn::make('authorBooks.role')
                    ->label('أدوار المؤلفين')
                    ->formatStateUsing(function ($record) {
                        $roles = $record->authorBooks->pluck('role')->unique()->map(function ($role) {
                            return match ($role) {
                                'author' => 'مؤلف',
                                'co_author' => 'مؤلف مشارك',
                                'editor' => 'محرر',
                                'translator' => 'مترجم',
                                'reviewer' => 'مراجع',
                                'commentator' => 'معلق',
                                default => $role,
                            };
                        })->implode(', ');
                        return $roles ?: 'غير محدد';
                    })
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('all_authors')
                    ->label('جميع المؤلفين')
                    ->formatStateUsing(function ($record) {
                        $authors = $record->authorBooks->map(function ($authorBook) {
                            $role = match ($authorBook->role) {
                                'author' => 'مؤلف',
                                'co_author' => 'مؤلف مشارك',
                                'editor' => 'محرر',
                                'translator' => 'مترجم',
                                'reviewer' => 'مراجع',
                                'commentator' => 'معلق',
                                default => $authorBook->role,
                            };
                            return $authorBook->author?->full_name . " ({$role})";
                        })->implode(' | ');
                        return $authors ?: 'غير محدد';
                    })
                    ->limit(100)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 100) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('volumes.title')
                    ->label('عناوين المجلدات')
                    ->formatStateUsing(function ($record) {
                        $volumes = $record->volumes->map(function ($volume) {
                            return "مجلد {$volume->number}" . ($volume->title ? ": {$volume->title}" : '');
                        })->implode(' | ');
                        return $volumes ?: 'لا توجد مجلدات';
                    })
                    ->limit(150)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 150) {
                            return null;
                        }
                        return $state;
                    })
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
                TextColumn::make('id')
                    ->label('المعرف')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('has_cover')
                    ->label('صورة الغلاف')
                    ->formatStateUsing(fn ($record) => $record->cover_image ? 'متوفرة' : 'غير متوفرة')
                    ->badge()
                    ->color(fn ($record) => $record->cover_image ? 'success' : 'danger')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('has_description')
                    ->label('وجود وصف')
                    ->formatStateUsing(fn ($record) => $record->description ? 'متوفر' : 'غير متوفر')
                    ->badge()
                    ->color(fn ($record) => $record->description ? 'success' : 'danger')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('authors_count')
                    ->label('عدد المؤلفين')
                    ->getStateUsing(fn ($record) => $record->authorBooks->count())
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('main_author_status')
                    ->label('المؤلف الرئيسي')
                    ->formatStateUsing(function ($record) {
                        $hasMainAuthor = $record->authorBooks->where('is_main', true)->isNotEmpty();
                        return $hasMainAuthor ? 'محدد' : 'غير محدد';
                    })
                    ->badge()
                    ->color(function ($record) {
                        $hasMainAuthor = $record->authorBooks->where('is_main', true)->isNotEmpty();
                        return $hasMainAuthor ? 'success' : 'warning';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // 1. مرشحات أساسية
                SelectFilter::make('book_section_id')
                    ->label('قسم الكتاب')
                    ->relationship('bookSection', 'name')
                    ->searchable()
                    ->preload()
                    ->indicator('القسم'),
                
                SelectFilter::make('publisher_id')
                    ->label('الناشر')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload()
                    ->indicator('الناشر'),
                
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
                    ->indicator('المؤلف'),

                // 2. مرشحات الحالة والرؤية
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'archived' => 'مؤرشف',
                    ])
                    ->multiple()
                    ->indicator('الحالة'),
                
                SelectFilter::make('visibility')
                    ->label('الرؤية')
                    ->options([
                        'public' => 'عام',
                        'private' => 'خاص',
                    ])
                    ->indicator('الرؤية'),

                // 3. مرشحات دور المؤلف
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
                    ->indicator('دور المؤلف'),

                // 4. مرشحات رقمية للطبعة والسنة
                Filter::make('edition_range')
                    ->form([
                        Grid::make(2)->schema([
                            TextInput::make('edition_from')
                                ->label('الطبعة من')
                                ->numeric()
                                ->placeholder('1'),
                            TextInput::make('edition_to')
                                ->label('إلى')
                                ->numeric()
                                ->placeholder('10'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['edition_from'],
                                fn (Builder $query, $value): Builder => $query->where('edition', '>=', $value)
                            )
                            ->when(
                                $data['edition_to'],
                                fn (Builder $query, $value): Builder => $query->where('edition', '<=', $value)
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['edition_from'] || $data['edition_to']) {
                            return 'نطاق الطبعة: ' . ($data['edition_from'] ?? '∞') . ' - ' . ($data['edition_to'] ?? '∞');
                        }
                        return null;
                    }),

                Filter::make('edition_year_range')
                    ->form([
                        Grid::make(2)->schema([
                            TextInput::make('year_from')
                                ->label('سنة الطباعة من')
                                ->numeric()
                                ->placeholder('1400'),
                            TextInput::make('year_to')
                                ->label('إلى')
                                ->numeric()
                                ->placeholder('2025'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['year_from'],
                                fn (Builder $query, $value): Builder => $query->where('edition_DATA', '>=', $value)
                            )
                            ->when(
                                $data['year_to'],
                                fn (Builder $query, $value): Builder => $query->where('edition_DATA', '<=', $value)
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['year_from'] || $data['year_to']) {
                            return 'نطاق السنة: ' . ($data['year_from'] ?? '∞') . ' - ' . ($data['year_to'] ?? '∞');
                        }
                        return null;
                    }),

                // 5. مرشحات عدد المجلدات والصفحات
                Filter::make('volumes_range')
                    ->form([
                        Grid::make(2)->schema([
                            TextInput::make('volumes_from')
                                ->label('عدد المجلدات من')
                                ->numeric()
                                ->placeholder('1'),
                            TextInput::make('volumes_to')
                                ->label('إلى')
                                ->numeric()
                                ->placeholder('20'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['volumes_from'],
                                fn (Builder $query, $value): Builder => $query->has('volumes', '>=', $value)
                            )
                            ->when(
                                $data['volumes_to'],
                                fn (Builder $query, $value): Builder => $query->has('volumes', '<=', $value)
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['volumes_from'] || $data['volumes_to']) {
                            return 'عدد المجلدات: ' . ($data['volumes_from'] ?? '∞') . ' - ' . ($data['volumes_to'] ?? '∞');
                        }
                        return null;
                    }),

                Filter::make('pages_range')
                    ->form([
                        Grid::make(2)->schema([
                            TextInput::make('pages_from')
                                ->label('عدد الصفحات من')
                                ->numeric()
                                ->placeholder('100'),
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
                                fn (Builder $query, $value): Builder => $query->has('pages', '>=', $value)
                            )
                            ->when(
                                $data['pages_to'],
                                fn (Builder $query, $value): Builder => $query->has('pages', '<=', $value)
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['pages_from'] || $data['pages_to']) {
                            return 'عدد الصفحات: ' . ($data['pages_from'] ?? '∞') . ' - ' . ($data['pages_to'] ?? '∞');
                        }
                        return null;
                    }),

                // 6. مرشحات وجود البيانات
                TernaryFilter::make('has_cover_image')
                    ->label('صورة الغلاف')
                    ->nullable()
                    ->trueLabel('مع صورة غلاف')
                    ->falseLabel('بدون صورة غلاف')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('cover_image'),
                        false: fn (Builder $query) => $query->whereNull('cover_image'),
                        blank: fn (Builder $query) => $query,
                    )
                    ->indicator('صورة الغلاف'),

                TernaryFilter::make('has_source_url')
                    ->label('رابط المصدر')
                    ->nullable()
                    ->trueLabel('مع رابط مصدر')
                    ->falseLabel('بدون رابط مصدر')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('source_url')->where('source_url', '!=', ''),
                        false: fn (Builder $query) => $query->where(function ($query) {
                            $query->whereNull('source_url')->orWhere('source_url', '');
                        }),
                        blank: fn (Builder $query) => $query,
                    )
                    ->indicator('رابط المصدر'),

                TernaryFilter::make('has_description')
                    ->label('وصف الكتاب')
                    ->nullable()
                    ->trueLabel('مع وصف')
                    ->falseLabel('بدون وصف')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('description')->where('description', '!=', ''),
                        false: fn (Builder $query) => $query->where(function ($query) {
                            $query->whereNull('description')->orWhere('description', '');
                        }),
                        blank: fn (Builder $query) => $query,
                    )
                    ->indicator('الوصف'),

                // 7. مرشح المؤلف الرئيسي
                TernaryFilter::make('has_main_author')
                    ->label('المؤلف الرئيسي')
                    ->nullable()
                    ->trueLabel('مع مؤلف رئيسي')
                    ->falseLabel('بدون مؤلف رئيسي')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('authorBooks', fn ($q) => $q->where('is_main', true)),
                        false: fn (Builder $query) => $query->whereDoesntHave('authorBooks', fn ($q) => $q->where('is_main', true)),
                        blank: fn (Builder $query) => $query,
                    )
                    ->indicator('المؤلف الرئيسي'),

                // 8. مرشحات تاريخية
                Filter::make('created_date_range')
                    ->form([
                        Grid::make(2)->schema([
                            DatePicker::make('created_from')
                                ->label('تاريخ الإنشاء من')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                            DatePicker::make('created_until')
                                ->label('إلى')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['created_from'] || $data['created_until']) {
                            return 'تاريخ الإنشاء: ' . 
                                ($data['created_from'] ? Carbon::parse($data['created_from'])->format('d/m/Y') : '∞') . 
                                ' - ' . 
                                ($data['created_until'] ? Carbon::parse($data['created_until'])->format('d/m/Y') : '∞');
                        }
                        return null;
                    }),

                Filter::make('updated_date_range')
                    ->form([
                        Grid::make(2)->schema([
                            DatePicker::make('updated_from')
                                ->label('تاريخ التحديث من')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                            DatePicker::make('updated_until')
                                ->label('إلى')
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['updated_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '>=', $date)
                            )
                            ->when(
                                $data['updated_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['updated_from'] || $data['updated_until']) {
                            return 'تاريخ التحديث: ' . 
                                ($data['updated_from'] ? Carbon::parse($data['updated_from'])->format('d/m/Y') : '∞') . 
                                ' - ' . 
                                ($data['updated_until'] ? Carbon::parse($data['updated_until'])->format('d/m/Y') : '∞');
                        }
                        return null;
                    }),

                // 9. مرشح نصي متقدم للبحث في الوصف
                Filter::make('description_search')
                    ->form([
                        TextInput::make('description_text')
                            ->label('البحث في الوصف')
                            ->placeholder('ابحث في وصف الكتاب...')
                            ->maxLength(255),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['description_text'],
                            fn (Builder $query, $text): Builder => $query->where('description', 'like', "%{$text}%")
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['description_text']) {
                            return 'البحث في الوصف: ' . $data['description_text'];
                        }
                        return null;
                    }),
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
                    ->url(fn (Book $record): string => 'https://home.anwaralolmaa.com/book?id=' . $record->id)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
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
                    
                    FilamentExportBulkAction::make('export')
                        ->label('تصدير'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]) // تقليل خيارات الصفحات لتحسين الأداء
            ->poll('60s') // تحديث كل دقيقة بدلاً من 30 ثانية
            ->deferLoading() // تأجيل التحميل لتحسين الأداء
            ->toggleColumnsTriggerAction(
                fn (\Filament\Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('إدارة الأعمدة')
                    ->icon('heroicon-o-view-columns')
                    ->color('gray')
                    ->tooltip('إظهار/إخفاء الأعمدة')
            );
    }

    public static function getRelations(): array
    {
        return [
            BookResource\RelationManagers\PagesRelationManager::class, // فقط RelationManager الصفحات المحسن
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
        // استخدام البيانات المحملة مسبقاً
        $mainAuthor = $record->authorBooks->where('is_main', true)->first()
            ?? $record->authorBooks->first();
        
        return [
            'المؤلف' => $mainAuthor?->author?->full_name ?? 'غير محدد',
            'القسم' => $record->bookSection?->name ?? 'غير محدد',
            'الناشر' => $record->publisher?->name ?? 'غير محدد',
        ];
    }
}
