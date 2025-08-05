<?php

namespace App\Filament\Resources\AuthorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان الكتاب')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('subtitle')
                    ->label('العنوان الفرعي')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('وصف الكتاب')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Select::make('publisher_id')
                    ->label('الناشر')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الناشر')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email(),
                        Forms\Components\TextInput::make('phone')
                            ->label('الهاتف'),
                        Forms\Components\Textarea::make('address')
                            ->label('العنوان'),
                    ]),
                Forms\Components\Select::make('book_section_id')
                    ->label('قسم الكتاب')
                    ->relationship('bookSection', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('isbn')
                    ->label('الرقم الدولي للكتاب')
                    ->maxLength(20),
                Forms\Components\TextInput::make('publication_year')
                    ->label('سنة النشر')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(date('Y')),
                Forms\Components\TextInput::make('edition')
                    ->label('الطبعة')
                    ->maxLength(100),
                Forms\Components\TextInput::make('total_pages')
                    ->label('إجمالي الصفحات')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Select::make('language')
                    ->label('اللغة')
                    ->options([
                        'ar' => 'العربية',
                        'en' => 'الإنجليزية',
                        'fr' => 'الفرنسية',
                        'de' => 'الألمانية',
                        'es' => 'الإسبانية',
                        'tr' => 'التركية',
                        'fa' => 'الفارسية',
                        'ur' => 'الأردية',
                    ])
                    ->default('ar'),
                Forms\Components\Toggle::make('is_published')
                    ->label('منشور')
                    ->default(true),
                Forms\Components\Toggle::make('is_featured')
                    ->label('مميز')
                    ->default(false),
                // Pivot fields
                Forms\Components\Select::make('role')
                    ->label('دور المؤلف')
                    ->options([
                        'author' => 'مؤلف',
                        'editor' => 'محقق',
                        'translator' => 'مترجم',
                        'commentator' => 'شارح',
                    ])
                    ->default('author')
                    ->required(),
                Forms\Components\Toggle::make('is_main')
                    ->label('مؤلف رئيسي')
                    ->default(true),
                Forms\Components\TextInput::make('display_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('الغلاف')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl('/images/default-book-cover.png'),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->title . ($record->subtitle ? ' - ' . $record->subtitle : '');
                    }),
                Tables\Columns\TextColumn::make('publisher.name')
                    ->label('الناشر')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('publication_year')
                    ->label('سنة النشر')
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('الدور')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'author' => 'success',
                        'editor' => 'info',
                        'translator' => 'warning',
                        'commentator' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'author' => 'مؤلف',
                        'editor' => 'محقق',
                        'translator' => 'مترجم',
                        'commentator' => 'شارح',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('pivot.is_main')
                    ->label('رئيسي')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('volumes_count')
                    ->label('المجلدات')
                    ->counts('volumes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('chapters_count')
                    ->label('الفصول')
                    ->counts('chapters')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('الصفحات')
                    ->counts('pages')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('مميز')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('publisher_id')
                    ->label('الناشر')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('book_section_id')
                    ->label('قسم الكتاب')
                    ->relationship('bookSection', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('pivot.role')
                    ->label('دور المؤلف')
                    ->options([
                        'author' => 'مؤلف',
                        'editor' => 'محقق',
                        'translator' => 'مترجم',
                        'commentator' => 'شارح',
                    ]),
                Tables\Filters\TernaryFilter::make('pivot.is_main')
                    ->label('مؤلف رئيسي'),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('منشور'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('مميز'),
                Tables\Filters\Filter::make('publication_year')
                    ->label('سنة النشر')
                    ->form([
                        Forms\Components\TextInput::make('from')
                            ->label('من سنة')
                            ->numeric(),
                        Forms\Components\TextInput::make('until')
                            ->label('إلى سنة')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->where('publication_year', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->where('publication_year', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة كتاب')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Extract pivot data
                        $pivotData = [
                            'role' => $data['role'] ?? 'author',
                            'is_main' => $data['is_main'] ?? true,
                            'display_order' => $data['display_order'] ?? 1,
                        ];
                        
                        // Remove pivot data from main data
                        unset($data['role'], $data['is_main'], $data['display_order']);
                        
                        // Store pivot data for later use
                        session(['pivot_data' => $pivotData]);
                        
                        return $data;
                    })
                    ->after(function ($record, RelationManager $livewire) {
                        // Attach with pivot data
                        $pivotData = session('pivot_data', []);
                        $livewire->getOwnerRecord()->books()->attach($record->id, $pivotData);
                        session()->forget('pivot_data');
                    }),
                Tables\Actions\AttachAction::make()
                    ->label('ربط كتاب موجود')
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label('الكتاب')
                            ->relationship('books', 'title')
                            ->searchable()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->title . ' (' . ($record->publisher->name ?? 'بدون ناشر') . ')'),
                        Forms\Components\Select::make('role')
                            ->label('دور المؤلف')
                            ->options([
                                'author' => 'مؤلف',
                                'editor' => 'محقق',
                                'translator' => 'مترجم',
                                'commentator' => 'شارح',
                            ])
                            ->default('author')
                            ->required(),
                        Forms\Components\Toggle::make('is_main')
                            ->label('مؤلف رئيسي')
                            ->default(true),
                        Forms\Components\TextInput::make('display_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        return [
                            'role' => $data['role'],
                            'is_main' => $data['is_main'],
                            'display_order' => $data['display_order'],
                        ];
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->url(fn ($record) => route('filament.admin.resources.books.view', $record)),
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->url(fn ($record) => route('filament.admin.resources.books.edit', $record)),
                Tables\Actions\DetachAction::make()
                    ->label('إلغاء الربط')
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء ربط الكتاب')
                    ->modalDescription('هل أنت متأكد من إلغاء ربط هذا الكتاب بالمؤلف؟')
                    ->modalSubmitActionLabel('إلغاء الربط'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('إلغاء ربط المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('إلغاء ربط الكتب المحددة')
                        ->modalDescription('هل أنت متأكد من إلغاء ربط الكتب المحددة بالمؤلف؟')
                        ->modalSubmitActionLabel('إلغاء الربط'),
                    Tables\Actions\BulkAction::make('mark_as_main')
                        ->label('تحديد كمؤلف رئيسي')
                        ->icon('heroicon-o-star')
                        ->action(function ($records, RelationManager $livewire) {
                            foreach ($records as $record) {
                                $livewire->getOwnerRecord()->books()->updateExistingPivot(
                                    $record->id,
                                    ['is_main' => true]
                                );
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تحديد كمؤلف رئيسي')
                        ->modalDescription('هل تريد تحديد المؤلف كمؤلف رئيسي للكتب المحددة؟')
                        ->modalSubmitActionLabel('تحديد'),
                ]),
            ])
            ->defaultSort('pivot.display_order')
            ->reorderable('pivot.display_order');
    }
}