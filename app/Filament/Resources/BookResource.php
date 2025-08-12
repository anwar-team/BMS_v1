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

                    // Tab 4: الصفحات (تظهر فقط إذا كان الكتاب محفوظ)
                    Tab::make('الصفحات')
                        ->icon('heroicon-o-document-text')
                        ->visible(fn ($livewire) => $livewire->record && $livewire->record->exists)
                        ->schema([
                            self::getPagesRepeater(),
                        ]),

                    // Tab 4b: رسالة ودية إذا لم يكن الكتاب محفوظاً
                    Tab::make('الصفحات')
                        ->icon('heroicon-o-document-text')
                        ->visible(fn ($livewire) => !$livewire->record || !$livewire->record->exists)
                        ->schema([
                            \Filament\Forms\Components\Placeholder::make('save_book_first')
                                ->label('تنبيه')
                                ->content('يرجى حفظ الكتاب أولاً لإضافة الصفحات. بعد الحفظ ستتمكن من إضافة الصفحات وربطها بالمجلدات والفصول.')
                                ->columnSpanFull(),
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
            ])
            ->addActionLabel('إضافة فصل جديد')
            ->reorderableWithButtons()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => 
                'فصل ' . ($state['chapter_number'] ?? 'جديد') . 
                ($state['title'] ? ' - ' . $state['title'] : '')
            )
            ->defaultItems(0);
    }

    private static function getPagesRepeater(): Repeater
    {
        return Repeater::make('pages')
            ->label('صفحات الكتاب')
            ->relationship('pages')
            ->schema([
                TextInput::make('page_number')
                    ->label('رقم الصفحة')
                    ->required()
                    ->numeric(),
                RichEditor::make('content')
                    ->label('محتوى الصفحة')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'h2',
                        'h3',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'link',
                    ])
                    ->columnSpanFull(),
            ])
            ->addActionLabel('إضافة صفحة جديدة')
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
                        $mainAuthors = $record->authorBooks()
                            ->where('is_main', true)
                            ->with('author')
                            ->get()
                            ->pluck('author.full_name')
                            ->filter()
                            ->join('، ');
                        return $mainAuthors ?: $record->authorBooks()
                            ->with('author')
                            ->first()?->author?->full_name ?? 'غير محدد';
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
                TextColumn::make('edition')
                    ->label('الطبعة')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (?int $state): string => $state ? "الطبعة {$state}" : 'غير محدد')
                    ->toggleable(),
                TextColumn::make('edition_DATA')
                    ->label('بيانات الطبعة')
                    ->badge()
                    ->color('secondary')
                    ->formatStateUsing(fn (?int $state): string => $state ? "رقم {$state}" : 'غير محدد')
                    ->toggleable(),
                TextColumn::make('volumes_count')
                    ->label('المجلدات')
                    ->getStateUsing(fn ($record) => $record->volumes()->count())
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                TextColumn::make('pages_count')
                    ->label('الصفحات')
                    ->getStateUsing(fn ($record) => $record->pages()->count())
                    ->badge()
                    ->color('warning')
                    ->toggleable(),
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
                SelectFilter::make('book_section_id')
                    ->label('قسم الكتاب')
                    ->relationship('bookSection', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'archived' => 'مؤرشف',
                    ]),
                SelectFilter::make('visibility')
                    ->label('الرؤية')
                    ->options([
                        'public' => 'عام',
                        'private' => 'خاص',
                    ]),
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
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            BookResource\RelationManagers\AuthorsRelationManager::class,
            BookResource\RelationManagers\VolumesRelationManager::class,
            BookResource\RelationManagers\ChaptersRelationManager::class,


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
        return [
            'المؤلف' => $record->authorBooks()->with('author')->first()?->author?->full_name ?? 'غير محدد',
            'القسم' => $record->bookSection?->name ?? 'غير محدد',
            'الناشر' => $record->publisher?->name ?? 'غير محدد',
        ];
    }
}
