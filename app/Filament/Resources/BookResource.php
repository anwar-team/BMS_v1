<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Step::make('معلومات الكتاب')
                    ->description('أدخل المعلومات الأساسية للكتاب')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('عنوان الكتاب')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $set('slug', \Str::slug($state));
                                    }
                                })
                                ->columnSpan(2),
                            
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
                                        ->action(function ($state, callable $set, callable $get) {
                                            if ($get('title')) {
                                                $set('slug', \Str::slug($get('title')));
                                            }
                                        })
                                )
                                ->columnSpan(2),
                        ]),
                        
                        Textarea::make('description')
                            ->label('وصف الكتاب')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        
                        Forms\Components\Grid::make(3)->schema([
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
                                ->columnSpan(1),
                            
                            TextInput::make('published_year')
                                ->label('سنة النشر')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(date('Y'))
                                ->placeholder('مثال: ' . date('Y'))
                                ->columnSpan(1),
                            
                            TextInput::make('publisher')
                                ->label('الناشر')
                                ->maxLength(255)
                                ->placeholder('مثال: دار النشر')
                                ->columnSpan(1),
                        ]),
                        
                        Forms\Components\Grid::make(2)->schema([
                            TextInput::make('isbn')
                                ->label('رقم ISBN')
                                ->maxLength(20)
                                ->placeholder('978-0-123456-78-9'),
                            
                            TextInput::make('pages_count')
                                ->label('عدد الصفحات')
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('مثال: 300'),
                        ]),
                        
                        Forms\Components\Toggle::make('is_published')
                            ->label('منشور')
                            ->default(true)
                            ->inline(false),
                    ]),

                Step::make('التصنيفات والمؤلفين')
                    ->description('اختر قسم الكتاب والمؤلفين')
                    ->icon('heroicon-o-tag')
                    ->schema([
                    Select::make('book_section_id')
                        ->relationship('bookSection', 'name')
                        ->label('قسم الكتاب')
                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('name')->label('اسم القسم')->required(),
                            Textarea::make('description')->label('وصف القسم'),
                            Forms\Components\Select::make('parent_id')
                                ->relationship('parent', 'name')
                                ->label('القسم الرئيسي')
                                ->default(null),
                            TextInput::make('sort_order')
                                ->label('ترتيب القسم')
                                ->required()
                                ->numeric()
                                ->default(0),
                            Forms\Components\Toggle::make('is_active')
                                ->label('نشط')  
                                ->required(),
                            TextInput::make('slug')
                              ->label('الرابط الثابت')
                              ->required()
                              ->maxLength(255)
                              ->unique(ignoreRecord: true)
                              ->lazy()
                              ->suffixAction(
                                  Action::make('generateSlug')
                                      ->icon('heroicon-m-sparkles')
                                      ->tooltip('توليد تلقائي من الاسم')
                                      ->action(function ($state, callable $set, callable $get) {
                                          if ($get('name')) {
                                              $slug = \Str::slug($get('name'));
                                              $set('slug', $slug);
                                          }
                                      })
                              )
                              ->afterStateUpdated(function ($state, callable $set, $get) {
                                  if (empty($state) && $get('name')) {
                                      $slug = \Str::slug($get('name'));
                                      $set('slug', $slug);
                                  }
                              }),
                        ])
                        ->required(),

                    Select::make('authors')
                        ->multiple()
                        ->relationship('authors', 'fname')
                        ->label('المؤلفون')
                        ->searchable()
                        ->preload()
                        ->optionsLimit(50)
                        ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->fname . ' ' . $record->lname))
                        ->createOptionForm([
                            Forms\Components\Grid::make(2)->schema([
                                TextInput::make('fname')
                                    ->label('الاسم الأول')
                                    ->required()
                                    ->maxLength(100)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $fname = $get('fname');
                                        $lname = $get('lname');
                                        if ($fname && $lname) {
                                            $set('full_name', trim($fname . ' ' . $lname));
                                        }
                                    }),
                                TextInput::make('lname')
                                    ->label('الكنية')
                                    ->required()
                                    ->maxLength(100)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $fname = $get('fname');
                                        $lname = $get('lname');
                                        if ($fname && $lname) {
                                            $set('full_name', trim($fname . ' ' . $lname));
                                        }
                                    }),
                            ]),
                            TextInput::make('mname')
                                ->label('اسم الأب')
                                ->maxLength(100),
                            TextInput::make('full_name')
                                ->label('الاسم الكامل')
                                ->required()
                                ->maxLength(255)
                                ->disabled()
                                ->dehydrated(),
                            Textarea::make('biography')
                                ->label('السيرة الذاتية')
                                ->rows(4)
                                ->columnSpanFull(),
                            TextInput::make('birth_year')
                                ->label('سنة الميلاد')
                                ->numeric()
                                ->minValue(1000)
                                ->maxValue(date('Y')),
                            TextInput::make('death_year')
                                ->label('سنة الوفاة')
                                ->numeric()
                                ->minValue(1000)
                                ->maxValue(date('Y'))
                                ->nullable(),
                        ])
                        ->createOptionAction(function (Action $action) {
                            return $action
                                ->modalHeading('إضافة مؤلف جديد')
                                ->modalSubmitActionLabel('إضافة المؤلف')
                                ->modalWidth('lg');
                        })
                        ->required(),
                ]),

                Step::make('المجلدات والفصول')
                    ->description('نظم الكتاب إلى مجلدات وفصول')
                    ->icon('heroicon-o-folder-open')
                    ->schema([
                        Repeater::make('volumes')
                            ->label('مجلدات الكتاب')
                            ->relationship('volumes')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
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

                                Repeater::make('chapters')
                                    ->label('فصول هذا المجلد')
                                    ->relationship('chapters')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
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
                                        
                                        Forms\Components\Grid::make(2)->schema([
                                            TextInput::make('start_page')
                                                ->label('الصفحة الأولى')
                                                ->numeric()
                                                ->minValue(1),
                                            
                                            TextInput::make('end_page')
                                                ->label('الصفحة الأخيرة')
                                                ->numeric()
                                                ->minValue(1),
                                        ]),
                                    ])
                                    ->addActionLabel('إضافة فصل جديد')
                                    ->reorderableWithButtons()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'فصل جديد')
                                    ->defaultItems(0)
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel('إضافة مجلد جديد')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                ($state['number'] ?? '') . ' - ' . ($state['title'] ?? 'مجلد جديد')
                            )
                            ->defaultItems(1)
                            ->columnSpanFull(),
                    ]),

                Step::make('الصفحات')
                    ->description('أضف صفحات الكتاب ومحتواها')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Repeater::make('pages')
                            ->label('صفحات الكتاب')
                            ->relationship('pages')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
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
                                
                                Forms\Components\Grid::make(2)->schema([
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
                                
                                Forms\Components\Toggle::make('is_published')
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
                            ->columnSpanFull(),
                    ]),
            ])->columnSpanFull(),
        ]);
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
                
                Tables\Columns\TextColumn::make('authors.fname')
                    ->label('المؤلفون')
                    ->badge()
                    ->separator(',')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->authors->map(fn ($author) => trim($author->fname . ' ' . $author->lname))->join(', ')),
                
                Tables\Columns\TextColumn::make('bookSection.name')
                    ->label('القسم')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('published_year')
                    ->label('سنة النشر')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                
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
                
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
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
                
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('منشور')
                    ->placeholder('الكل')
                    ->trueLabel('منشور')
                    ->falseLabel('غير منشور'),
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
                                $record->update(['is_published' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('إلغاء نشر المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['is_published' => false]);
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
