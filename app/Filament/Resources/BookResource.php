<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use App\Models\Author;
use App\Models\BookSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationLabel = 'الكتب';
    
    protected static ?string $modelLabel = 'كتاب';
    
    protected static ?string $pluralModelLabel = 'الكتب';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('المعلومات الأساسية')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make('معلومات الكتاب الأساسية')
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->label('عنوان الكتاب')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                            if (!$get('slug') && $state) {
                                                $set('slug', Str::slug($state));
                                            }
                                        }),
                                        
                                    Forms\Components\TextInput::make('slug')
                                        ->label('الرابط')
                                        ->required()
                                        ->maxLength(200)
                                        ->unique(ignoreRecord: true)
                                        ->helperText('سيتم إنشاء الرابط تلقائياً من العنوان'),
                                        
                                    Forms\Components\Textarea::make('description')
                                        ->label('وصف الكتاب')
                                        ->rows(4)
                                        ->columnSpanFull(),
                                        
                                    Forms\Components\FileUpload::make('cover_image')
                                        ->label('صورة الغلاف')
                                        ->image()
                                        ->imageEditor()
                                        ->directory('books/covers')
                                        ->visibility('public'),
                                ])->columns(2),
                        ]),

                    Wizard\Step::make('التصنيف والمؤلفين')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Section::make('تصنيف الكتاب')
                                ->schema([
                                    Forms\Components\Select::make('book_section_id')
                                        ->label('قسم الكتاب')
                                        ->relationship('bookSection', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')
                                                ->label('اسم القسم')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\Textarea::make('description')
                                                ->label('وصف القسم'),
                                            Forms\Components\Select::make('parent_id')
                                                ->label('القسم الأب')
                                                ->relationship('parent', 'name')
                                                ->searchable(),
                                            Forms\Components\TextInput::make('slug')
                                                ->label('الرابط')
                                                ->maxLength(255),
                                            Forms\Components\Toggle::make('is_active')
                                                ->label('نشط')
                                                ->default(true),
                                        ]),
                                ])->columns(1),
                                
                            Section::make('المؤلفين')
                                ->schema([
                                    Forms\Components\Select::make('authors')
                                        ->label('المؤلفين')
                                        ->relationship('authors', 'fname')
                                        ->multiple()
                                        ->preload()
                                        ->searchable()
                                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('fname')
                                                ->label('الاسم الأول')
                                                ->required()
                                                ->maxLength(100),
                                            Forms\Components\TextInput::make('mname')
                                                ->label('الاسم الأوسط')
                                                ->maxLength(100),
                                            Forms\Components\TextInput::make('lname')
                                                ->label('اسم العائلة')
                                                ->maxLength(100),
                                            Forms\Components\Textarea::make('biography')
                                                ->label('السيرة الذاتية')
                                                ->rows(3),
                                            Forms\Components\TextInput::make('nationality')
                                                ->label('الجنسية')
                                                ->maxLength(100),
                                            Forms\Components\TextInput::make('madhhab')
                                                ->label('المذهب')
                                                ->maxLength(100),
                                            Forms\Components\DatePicker::make('birth_date')
                                                ->label('تاريخ الولادة'),
                                            Forms\Components\DatePicker::make('death_date')
                                                ->label('تاريخ الوفاة'),
                                        ])
                                        ->pivotData([
                                            'role' => 'author',
                                            'is_main' => true,
                                            'display_order' => 1,
                                        ]),
                                ])->columns(1),
                        ]),

                    Wizard\Step::make('تفاصيل النشر')
                        ->icon('heroicon-o-newspaper')
                        ->schema([
                            Section::make('معلومات النشر')
                                ->schema([
                                    Forms\Components\TextInput::make('published_year')
                                        ->label('سنة النشر')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(date('Y')),
                                        
                                    Forms\Components\TextInput::make('publisher')
                                        ->label('دار النشر')
                                        ->maxLength(200),
                                        
                                    Forms\Components\TextInput::make('pages_count')
                                        ->label('عدد الصفحات الإجمالي')
                                        ->numeric()
                                        ->minValue(1)
                                        ->helperText('سيتم حسابه تلقائياً من الصفحات المُدخلة'),
                                        
                                    Forms\Components\TextInput::make('volumes_count')
                                        ->label('عدد المجلدات')
                                        ->required()
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1),
                                        
                                    Forms\Components\Select::make('status')
                                        ->label('حالة الكتاب')
                                        ->required()
                                        ->options([
                                            'draft' => 'مسودة',
                                            'published' => 'منشور',
                                            'archived' => 'مؤرشف',
                                        ])
                                        ->default('draft'),
                                        
                                    Forms\Components\Select::make('visibility')
                                        ->label('مستوى الرؤية')
                                        ->required()
                                        ->options([
                                            'public' => 'عام',
                                            'private' => 'خاص',
                                            'restricted' => 'محدود',
                                        ])
                                        ->default('public'),
                                        
                                    Forms\Components\TextInput::make('source_url')
                                        ->label('رابط المصدر')
                                        ->url()
                                        ->maxLength(255),
                                ])->columns(2),
                        ]),

                    Wizard\Step::make('هيكل الكتاب')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('المجلدات والفصول')
                                ->schema([
                                    Repeater::make('volumes')
                                        ->label('المجلدات')
                                        ->relationship()
                                        ->schema([
                                            Forms\Components\TextInput::make('number')
                                                ->label('رقم المجلد')
                                                ->required()
                                                ->numeric()
                                                ->minValue(1),
                                                
                                            Forms\Components\TextInput::make('title')
                                                ->label('عنوان المجلد')
                                                ->maxLength(255),
                                                
                                            Forms\Components\TextInput::make('page_start')
                                                ->label('بداية الصفحات')
                                                ->numeric()
                                                ->minValue(1),
                                                
                                            Forms\Components\TextInput::make('page_end')
                                                ->label('نهاية الصفحات')
                                                ->numeric()
                                                ->minValue(1),
                                                
                                            Repeater::make('chapters')
                                                ->label('الفصول')
                                                ->relationship()
                                                ->schema([
                                                    Forms\Components\TextInput::make('chapter_number')
                                                        ->label('رقم الفصل')
                                                        ->required()
                                                        ->numeric()
                                                        ->minValue(1),
                                                        
                                                    Forms\Components\TextInput::make('title')
                                                        ->label('عنوان الفصل')
                                                        ->required()
                                                        ->maxLength(255),
                                                        
                                                    Forms\Components\TextInput::make('order')
                                                        ->label('ترتيب الفصل')
                                                        ->numeric()
                                                        ->minValue(1),
                                                        
                                                    Forms\Components\TextInput::make('page_start')
                                                        ->label('بداية الصفحات')
                                                        ->numeric()
                                                        ->minValue(1),
                                                        
                                                    Forms\Components\TextInput::make('page_end')
                                                        ->label('نهاية الصفحات')
                                                        ->numeric()
                                                        ->minValue(1),
                                                        
                                                    Forms\Components\Select::make('chapter_type')
                                                        ->label('نوع الفصل')
                                                        ->options([
                                                            'chapter' => 'فصل',
                                                            'section' => 'قسم',
                                                            'part' => 'جزء',
                                                        ])
                                                        ->default('chapter'),
                                                        
                                                    Repeater::make('pages')
                                                        ->label('الصفحات')
                                                        ->relationship()
                                                        ->schema([
                                                            Forms\Components\TextInput::make('page_number')
                                                                ->label('رقم الصفحة')
                                                                ->required()
                                                                ->numeric()
                                                                ->minValue(1),
                                                                
                                                            Forms\Components\RichEditor::make('content')
                                                                ->label('محتوى الصفحة')
                                                                ->columnSpanFull()
                                                                ->toolbarButtons([
                                                                    'bold',
                                                                    'italic',
                                                                    'underline',
                                                                    'strike',
                                                                    'bulletList',
                                                                    'orderedList',
                                                                    'h2',
                                                                    'h3',
                                                                    'blockquote',
                                                                ]),
                                                        ])
                                                        ->columns(1)
                                                        ->collapsed()
                                                        ->itemLabel(fn (array $state): ?string => 
                                                            isset($state['page_number']) ? "صفحة {$state['page_number']}" : null
                                                        )
                                                        ->addActionLabel('إضافة صفحة')
                                                        ->defaultItems(0),
                                                ])
                                                ->columns(2)
                                                ->collapsed()
                                                ->itemLabel(fn (array $state): ?string => 
                                                    isset($state['title']) ? $state['title'] : null
                                                )
                                                ->addActionLabel('إضافة فصل')
                                                ->defaultItems(0),
                                        ])
                                        ->columns(2)
                                        ->collapsed()
                                        ->itemLabel(fn (array $state): ?string => 
                                            isset($state['number']) ? "المجلد {$state['number']}" . 
                                            (isset($state['title']) ? " - {$state['title']}" : '') : null
                                        )
                                        ->addActionLabel('إضافة مجلد')
                                        ->defaultItems(1),
                                ])
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull()
                ->persistStepInQueryString()
                ->submitAction(new \Filament\Actions\Action('submit'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('الغلاف')
                    ->square()
                    ->size(60),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الكتاب')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('authors.fname')
                    ->label('المؤلف')
                    ->searchable()
                    ->formatStateUsing(function ($record) {
                        return $record->authors->pluck('full_name')->join(', ');
                    })
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('bookSection.name')
                    ->label('القسم')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('volumes_count')
                    ->label('المجلدات')
                    ->numeric()
                    ->sortable()
                    ->alignment('center'),
                    
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('الصفحات')
                    ->numeric()
                    ->sortable()
                    ->alignment('center')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) : '-'),
                    
                Tables\Columns\TextColumn::make('published_year')
                    ->label('سنة النشر')
                    ->sortable()
                    ->alignment('center'),
                    
                Tables\Columns\TextColumn::make('status')
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
                    
                Tables\Columns\TextColumn::make('visibility')
                    ->label('الرؤية')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'private' => 'danger',
                        'restricted' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'public' => 'عام',
                        'private' => 'خاص',
                        'restricted' => 'محدود',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('book_section_id')
                    ->label('القسم')
                    ->relationship('bookSection', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'archived' => 'مؤرشف',
                    ]),
                    
                Tables\Filters\SelectFilter::make('visibility')
                    ->label('الرؤية')
                    ->options([
                        'public' => 'عام',
                        'private' => 'خاص',
                        'restricted' => 'محدود',
                    ]),
                    
                Tables\Filters\Filter::make('published_year')
                    ->label('سنة النشر')
                    ->form([
                        Forms\Components\TextInput::make('from')
                            ->label('من سنة')
                            ->numeric(),
                        Forms\Components\TextInput::make('to')
                            ->label('إلى سنة')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->where('published_year', '>=', $date))
                            ->when($data['to'], fn (Builder $query, $date): Builder => $query->where('published_year', '<=', $date));
                    }),
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
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('البحث في الكتب...')
            ->emptyStateHeading('لا توجد كتب')
            ->emptyStateDescription('ابدأ بإضافة كتابك الأول إلى المكتبة')
            ->emptyStateIcon('heroicon-o-book-open');
    }

    public static function getRelations(): array
    {
        return [
            // يمكن إضافة RelationManagers هنا لاحقاً
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
            'view' => Pages\ViewBook::route('/{record}'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['authors', 'bookSection']);
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'authors.fname', 'authors.lname', 'bookSection.name'];
    }
    
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];
        
        if ($record->bookSection) {
            $details['القسم'] = $record->bookSection->name;
        }
        
        if ($record->authors->count() > 0) {
            $details['المؤلف'] = $record->authors->pluck('full_name')->join(', ');
        }
        
        if ($record->published_year) {
            $details['سنة النشر'] = $record->published_year;
        }
        
        return $details;
    }
}
