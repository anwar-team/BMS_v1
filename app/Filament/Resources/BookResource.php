<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use App\Support\DateHelper;
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
use Illuminate\Support\Str;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('book_tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('المعلومات الأساسية')
                        ->icon('heroicon-o-book-open')
                        ->schema([
                            Forms\Components\Section::make('المعلومات الأساسية')
                                ->description('أدخل العنوان والوصف والمعرف الفريد للكتاب')
                                ->icon('heroicon-o-identification')
                                ->schema([
                                    Forms\Components\Grid::make(2)->schema([
                                        TextInput::make('title')
                                            ->label('عنوان الكتاب')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('subtitle')
                                            ->label('العنوان الفرعي')
                                            ->maxLength(255),
                                        Textarea::make('description')
                                            ->label('وصف الكتاب')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        TextInput::make('isbn')
                                            ->label('رقم ISBN')
                                            ->maxLength(20),
                                        TextInput::make('publisher')
                                            ->label('الناشر')
                                            ->maxLength(255),
                                        TextInput::make('published_year')
                                            ->label('سنة النشر')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(date('Y')),
                                        TextInput::make('pages_count')
                                            ->label('عدد الصفحات')
                                            ->numeric()
                                            ->minValue(1),
                                        TextInput::make('volumes_count')
                                            ->label('عدد المجلدات')
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(1),
                                        FileUpload::make('cover_image')
                                            ->label('صورة الغلاف')
                                            ->image()
                                            ->directory('book-covers')
                                            ->columnSpanFull(),
                                    ]),
                                ])
                                ->collapsible(),
                        ]),
                    
                    Forms\Components\Tabs\Tab::make('التصنيفات والمؤلفين')
                        ->icon('heroicon-o-tag')
                        ->schema([
                            Select::make('book_section_id')
                                ->relationship('bookSection', 'name')
                                ->label('قسم الكتاب')
                                ->searchable()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->label('اسم القسم')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->label('وصف القسم')
                                        ->rows(3),
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
                                        ->default(true),
                                    TextInput::make('slug')
                                        ->label('الرابط الثابت')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true)
                                        ->live(onBlur: true)
                                        ->suffixAction(
                                            Action::make('generateSlug')
                                                ->icon('heroicon-m-sparkles')
                                                ->tooltip('توليد تلقائي من الاسم')
                                                ->action(function ($state, callable $set, callable $get) {
                                                    if ($get('name')) {
                                                        $slug = Str::slug($get('name'));
                                                        $set('slug', $slug);
                                                    }
                                                })
                                        )
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            if (empty($state) && $get('name')) {
                                                $slug = Str::slug($get('name'));
                                                $set('slug', $slug);
                                            }
                                        }),
                                ])
                                ->required(),
                            
                            Repeater::make('authorBooks')
                                ->label('المؤلفون ودورهم')
                                ->relationship('authorBooks')
                                ->schema([
                                    Forms\Components\Grid::make(4)->schema([
                                        Select::make('author_id')
                                            ->label('المؤلف')
                                            ->relationship('author', 'fname')
                                            ->searchable()
                                            ->preload()
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
                                                            $mname = $get('mname');
                                                            $lname = $get('lname');
                                                            if ($fname && $lname && $mname) {
                                                                $set('full_name', trim($fname . ' ' . $mname . ' ' . $lname));
                                                            }
                                                        }),
                                                    TextInput::make('mname')
                                                        ->label('اسم الأب')
                                                        ->maxLength(100)
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                            $fname = $get('fname');
                                                            $mname = $get('mname');
                                                            $lname = $get('lname');
                                                            if ($fname && $lname && $mname) {
                                                                $set('full_name', trim($fname . ' ' . $mname . ' ' . $lname));
                                                            }
                                                        }),
                                                    TextInput::make('lname')
                                                        ->label('الاسم الأخير')
                                                        ->required()
                                                        ->maxLength(100)
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                            $fname = $get('fname');
                                                            $mname = $get('mname');
                                                            $lname = $get('lname');
                                                            if ($fname && $lname && $mname) {
                                                                $set('full_name', trim($fname . ' ' . $mname . ' ' . $lname));
                                                            }
                                                        }),
                                                    TextInput::make('full_name')
                                                        ->label('الاسم الكامل')
                                                        ->maxLength(255)
                                                        ->columnSpanFull(),
                                                    Textarea::make('bio')
                                                        ->label('نبذة عن المؤلف')
                                                        ->rows(3)
                                                        ->columnSpanFull(),
                                                    TextInput::make('birth_year')
                                                        ->label('سنة الميلاد')
                                                        ->numeric()
                                                        ->minValue(1)
                                                        ->maxValue(date('Y')),
                                                    TextInput::make('death_year')
                                                        ->label('سنة الوفاة')
                                                        ->numeric()
                                                        ->minValue(1)
                                                        ->maxValue(date('Y')),
                                                ]),
                                            ])
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
                                        
                                        Forms\Components\Toggle::make('is_main')
                                            ->label('مؤلف رئيسي')
                                            ->helperText('حدد المؤلف الرئيسي للكتاب')
                                            ->default(false)
                                            ->columnSpan(1),
                                        
                                        TextInput::make('display_order')
                                            ->label('ترتيب العرض')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('ترتيب ظهور المؤلف في قائمة المؤلفين')
                                            ->columnSpan(1),
                                    ]),
                                ])
                                ->addActionLabel('إضافة مؤلف')
                                ->reorderableWithButtons()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string =>
                                    isset($state['author_id']) && $state['author_id']
                                        ? \App\Models\Author::find($state['author_id'])?->fname . ' ' . \App\Models\Author::find($state['author_id'])?->lname . ' (' . ($state['role'] ?? 'مؤلف') . ')' . ($state['is_main'] ? ' - رئيسي' : '')
                                        : 'مؤلف جديد'
                                )
                                ->defaultItems(1)
                                ->minItems(1)
                                ->columnSpanFull(),
                        ]),
                    
                    Forms\Components\Tabs\Tab::make('معلومات إضافية')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Select::make('status')
                                    ->label('حالة الكتاب')
                                    ->options([
                                        'draft' => 'مسودة',
                                        'published' => 'منشور',
                                        'archived' => 'مؤرشف',
                                    ])
                                    ->default('draft')
                                    ->required(),
                                
                                Select::make('visibility')
                                    ->label('مستوى الرؤية')
                                    ->options([
                                        'public' => 'عام',
                                        'private' => 'خاص',
                                    ])
                                    ->default('public')
                                    ->required(),
                                
                                TextInput::make('source_url')
                                    ->label('رابط المصدر')
                                    ->url()
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                
                                RichEditor::make('notes')
                                    ->label('ملاحظات')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                        'link',
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        ]),
                ])
                ->columnSpanFull(),
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