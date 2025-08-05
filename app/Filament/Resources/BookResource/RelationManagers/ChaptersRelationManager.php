<?php

namespace App\Filament\Resources\BookResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان الفصل')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('وصف الفصل')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Select::make('volume_id')
                    ->label('المجلد')
                    ->relationship('volume', 'title', fn (Builder $query) => 
                        $query->where('book_id', $this->getOwnerRecord()->id)
                    )
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان المجلد')
                            ->required(),
                        Forms\Components\TextInput::make('volume_number')
                            ->label('رقم المجلد')
                            ->numeric()
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data, RelationManager $livewire): int {
                        $volume = $livewire->getOwnerRecord()->volumes()->create([
                            'title' => $data['title'],
                            'volume_number' => $data['volume_number'],
                            'display_order' => $data['volume_number'],
                        ]);
                        return $volume->id;
                    }),
                Forms\Components\Select::make('parent_id')
                    ->label('الفصل الأب')
                    ->relationship('parent', 'title', fn (Builder $query) => 
                        $query->where('book_id', $this->getOwnerRecord()->id)
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder('فصل رئيسي'),
                Forms\Components\TextInput::make('chapter_number')
                    ->label('رقم الفصل')
                    ->numeric()
                    ->minValue(1)
                    ->default(1),
                Forms\Components\TextInput::make('start_page')
                    ->label('الصفحة الأولى')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('end_page')
                    ->label('الصفحة الأخيرة')
                    ->numeric()
                    ->minValue(1)
                    ->gte('start_page'),
                Forms\Components\Toggle::make('is_main_chapter')
                    ->label('فصل رئيسي')
                    ->default(true)
                    ->helperText('إذا كان غير محدد، سيعتبر فصل فرعي'),
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
                Tables\Columns\TextColumn::make('chapter_number')
                    ->label('رقم الفصل')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->formatStateUsing(function ($state, $record) {
                        $prefix = $record->parent_id ? '└─ ' : '';
                        return $prefix . $state;
                    }),
                Tables\Columns\TextColumn::make('volume.title')
                    ->label('المجلد')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label('الفصل الأب')
                    ->sortable()
                    ->limit(30)
                    ->placeholder('فصل رئيسي'),
                Tables\Columns\TextColumn::make('start_page')
                    ->label('من صفحة')
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('end_page')
                    ->label('إلى صفحة')
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('عدد الصفحات')
                    ->counts('pages')
                    ->sortable(),
                Tables\Columns\TextColumn::make('children_count')
                    ->label('الفصول الفرعية')
                    ->counts('children')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_main_chapter')
                    ->label('رئيسي')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('display_order')
                    ->label('الترتيب')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_main_chapter')
                    ->label('فصل رئيسي'),
                Tables\Filters\SelectFilter::make('volume_id')
                    ->label('المجلد')
                    ->relationship('volume', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('has_parent')
                    ->label('له فصل أب')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('parent_id')),
                Tables\Filters\Filter::make('has_children')
                    ->label('له فصول فرعية')
                    ->query(fn (Builder $query): Builder => $query->has('children')),
                Tables\Filters\Filter::make('has_pages')
                    ->label('يحتوي على صفحات')
                    ->query(fn (Builder $query): Builder => $query->has('pages')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة فصل')
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        // Auto-set chapter number if not provided
                        if (!isset($data['chapter_number']) || !$data['chapter_number']) {
                            $maxChapter = $livewire->getOwnerRecord()->chapters()->max('chapter_number') ?? 0;
                            $data['chapter_number'] = $maxChapter + 1;
                        }
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
                    ->modalHeading('حذف الفصل')
                    ->modalDescription('هل أنت متأكد من حذف هذا الفصل؟ سيتم حذف جميع الفصول الفرعية والصفحات المرتبطة به.')
                    ->modalSubmitActionLabel('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('حذف الفصول المحددة')
                        ->modalDescription('هل أنت متأكد من حذف الفصول المحددة؟ سيتم حذف جميع الفصول الفرعية والصفحات المرتبطة بها.')
                        ->modalSubmitActionLabel('حذف'),
                ]),
            ])
            ->defaultSort('display_order')
            ->reorderable('display_order');
    }
}