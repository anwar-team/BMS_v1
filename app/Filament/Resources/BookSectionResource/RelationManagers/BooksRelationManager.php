<?php

namespace App\Filament\Resources\BookSectionResource\RelationManagers;

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
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Select::make('publisher_id')
                    ->label('دار النشر')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم دار النشر')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email(),
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف'),
                        Forms\Components\Textarea::make('address')
                            ->label('العنوان'),
                    ]),
                Forms\Components\TextInput::make('isbn')
                    ->label('رقم ISBN')
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                Forms\Components\DatePicker::make('publication_date')
                    ->label('تاريخ النشر'),
                Forms\Components\TextInput::make('publication_year')
                    ->label('سنة النشر')
                    ->numeric()
                    ->minValue(1000)
                    ->maxValue(date('Y') + 10),
                Forms\Components\TextInput::make('edition')
                    ->label('الطبعة')
                    ->maxLength(50),
                Forms\Components\TextInput::make('pages')
                    ->label('عدد الصفحات')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Select::make('language')
                    ->label('اللغة')
                    ->options([
                        'ar' => 'العربية',
                        'en' => 'الإنجليزية',
                        'fr' => 'الفرنسية',
                        'es' => 'الإسبانية',
                        'de' => 'الألمانية',
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
                Forms\Components\FileUpload::make('cover_image')
                    ->label('صورة الغلاف')
                    ->image()
                    ->directory('book-covers')
                    ->visibility('public')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
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
                    ->defaultImageUrl(url('/images/default-book-cover.png'))
                    ->size(50),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الكتاب')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function ($record) {
                        return $record->subtitle ? $record->title . ' - ' . $record->subtitle : $record->title;
                    }),
                Tables\Columns\TextColumn::make('subtitle')
                    ->label('العنوان الفرعي')
                    ->searchable()
                    ->limit(30)
                    ->toggleable()
                    ->placeholder('بدون عنوان فرعي'),
                Tables\Columns\TextColumn::make('publisher.name')
                    ->label('دار النشر')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('authors_count')
                    ->label('المؤلفون')
                    ->counts('authors')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state === 1 => 'success',
                        $state <= 3 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('volumes_count')
                    ->label('المجلدات')
                    ->counts('volumes')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('chapters_count')
                    ->label('الفصول')
                    ->counts('chapters')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('الصفحات')
                    ->counts('pages')
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                Tables\Columns\TextColumn::make('publication_year')
                    ->label('سنة النشر')
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->copyable()
                    ->placeholder('غير محدد')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('language')
                    ->label('اللغة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ar' => 'العربية',
                        'en' => 'الإنجليزية',
                        'fr' => 'الفرنسية',
                        'es' => 'الإسبانية',
                        'de' => 'الألمانية',
                        'tr' => 'التركية',
                        'fa' => 'الفارسية',
                        'ur' => 'الأردية',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ar' => 'success',
                        'en' => 'info',
                        'fr' => 'warning',
                        default => 'gray',
                    }),
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
                Tables\Filters\SelectFilter::make('publisher_id')
                    ->label('دار النشر')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('language')
                    ->label('اللغة')
                    ->options([
                        'ar' => 'العربية',
                        'en' => 'الإنجليزية',
                        'fr' => 'الفرنسية',
                        'es' => 'الإسبانية',
                        'de' => 'الألمانية',
                        'tr' => 'التركية',
                        'fa' => 'الفارسية',
                        'ur' => 'الأردية',
                    ])
                    ->multiple(),
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
                        Forms\Components\TextInput::make('to')
                            ->label('إلى سنة')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $year): Builder => $query->where('publication_year', '>=', $year),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $year): Builder => $query->where('publication_year', '<=', $year),
                            );
                    }),
                Tables\Filters\Filter::make('has_isbn')
                    ->label('له رقم ISBN')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('isbn')->where('isbn', '!=', '')),
                Tables\Filters\Filter::make('has_cover')
                    ->label('له صورة غلاف')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('cover_image')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة كتاب')
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        // Set book_section_id from the owner record
                        $data['book_section_id'] = $livewire->getOwnerRecord()->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation()
                    ->modalHeading('حذف الكتاب')
                    ->modalDescription('هل أنت متأكد من حذف هذا الكتاب؟ سيتم حذف جميع المجلدات والفصول والصفحات المرتبطة به.')
                    ->modalSubmitActionLabel('حذف'),
                Tables\Actions\Action::make('duplicate')
                    ->label('نسخ')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record, RelationManager $livewire) {
                        $newBook = $record->replicate();
                        $newBook->title = $record->title . ' (نسخة)';
                        $newBook->isbn = null; // Clear ISBN for duplicate
                        $newBook->is_published = false;
                        $newBook->is_featured = false;
                        $newBook->book_section_id = $livewire->getOwnerRecord()->id;
                        $newBook->save();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('نسخ الكتاب')
                    ->modalDescription('هل تريد إنشاء نسخة من هذا الكتاب؟')
                    ->modalSubmitActionLabel('نسخ'),
                Tables\Actions\Action::make('toggle_featured')
                    ->label(fn ($record) => $record->is_featured ? 'إلغاء التمييز' : 'تمييز')
                    ->icon(fn ($record) => $record->is_featured ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn ($record) => $record->is_featured ? 'warning' : 'gray')
                    ->action(fn ($record) => $record->update(['is_featured' => !$record->is_featured]))
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->is_featured ? 'إلغاء تمييز الكتاب' : 'تمييز الكتاب')
                    ->modalDescription(fn ($record) => $record->is_featured ? 'هل تريد إلغاء تمييز هذا الكتاب؟' : 'هل تريد تمييز هذا الكتاب؟')
                    ->modalSubmitActionLabel(fn ($record) => $record->is_featured ? 'إلغاء التمييز' : 'تمييز'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('حذف الكتب المحددة')
                        ->modalDescription('هل أنت متأكد من حذف الكتب المحددة؟ سيتم حذف جميع المحتويات المرتبطة بها.')
                        ->modalSubmitActionLabel('حذف'),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('نشر المحدد')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('نشر الكتب')
                        ->modalDescription('هل تريد نشر الكتب المحددة؟')
                        ->modalSubmitActionLabel('نشر'),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('إلغاء نشر المحدد')
                        ->icon('heroicon-o-eye-slash')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('إلغاء نشر الكتب')
                        ->modalDescription('هل تريد إلغاء نشر الكتب المحددة؟')
                        ->modalSubmitActionLabel('إلغاء النشر'),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('تمييز المحدد')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_featured' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تمييز الكتب')
                        ->modalDescription('هل تريد تمييز الكتب المحددة؟')
                        ->modalSubmitActionLabel('تمييز'),
                    Tables\Actions\BulkAction::make('unfeature')
                        ->label('إلغاء تمييز المحدد')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_featured' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('إلغاء تمييز الكتب')
                        ->modalDescription('هل تريد إلغاء تمييز الكتب المحددة؟')
                        ->modalSubmitActionLabel('إلغاء التمييز'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}