<?php

namespace App\Filament\Resources\VolumeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PagesRelationManager extends RelationManager
{
    protected static string $relationship = 'pages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('page_number')
                    ->label('رقم الصفحة')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                Forms\Components\TextInput::make('title')
                    ->label('عنوان الصفحة')
                    ->maxLength(255)
                    ->helperText('اختياري - عنوان خاص للصفحة'),
                Forms\Components\Select::make('chapter_id')
                    ->label('الفصل')
                    ->relationship('chapter', 'title', fn (Builder $query) => 
                        $query->where('volume_id', $this->getOwnerRecord()->id)
                    )
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان الفصل')
                            ->required(),
                        Forms\Components\TextInput::make('chapter_number')
                            ->label('رقم الفصل')
                            ->numeric()
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data, RelationManager $livewire): int {
                        $chapter = $livewire->getOwnerRecord()->chapters()->create([
                            'title' => $data['title'],
                            'chapter_number' => $data['chapter_number'],
                            'book_id' => $livewire->getOwnerRecord()->book_id,
                            'display_order' => $data['chapter_number'],
                        ]);
                        return $chapter->id;
                    }),
                Forms\Components\RichEditor::make('content')
                    ->label('محتوى الصفحة')
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'h2',
                        'h3',
                        'undo',
                        'redo',
                    ]),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('ملاحظات داخلية للمحررين'),
                Forms\Components\Toggle::make('is_published')
                    ->label('منشورة')
                    ->default(true),
                Forms\Components\Toggle::make('has_footnotes')
                    ->label('تحتوي على هوامش')
                    ->default(false)
                    ->disabled()
                    ->helperText('يتم تحديثها تلقائياً عند إضافة هوامش'),
                Forms\Components\TextInput::make('word_count')
                    ->label('عدد الكلمات')
                    ->numeric()
                    ->disabled()
                    ->helperText('يتم حسابها تلقائياً من المحتوى'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('page_number')
            ->columns([
                Tables\Columns\TextColumn::make('page_number')
                    ->label('رقم الصفحة')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('بدون عنوان')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('chapter.title')
                    ->label('الفصل')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('content')
                    ->label('المحتوى')
                    ->html()
                    ->limit(100)
                    ->placeholder('لا يوجد محتوى')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('footnotes_count')
                    ->label('الهوامش')
                    ->counts('footnotes')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state <= 5 => 'success',
                        $state <= 15 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('word_count')
                    ->label('عدد الكلمات')
                    ->sortable()
                    ->placeholder('غير محسوب')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشورة')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_footnotes')
                    ->label('لها هوامش')
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
                Tables\Filters\SelectFilter::make('chapter_id')
                    ->label('الفصل')
                    ->relationship('chapter', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('منشورة'),
                Tables\Filters\TernaryFilter::make('has_footnotes')
                    ->label('لها هوامش'),
                Tables\Filters\Filter::make('has_content')
                    ->label('لها محتوى')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('content')->where('content', '!=', '')),
                Tables\Filters\Filter::make('page_range')
                    ->label('نطاق الصفحات')
                    ->form([
                        Forms\Components\TextInput::make('from')
                            ->label('من صفحة')
                            ->numeric(),
                        Forms\Components\TextInput::make('to')
                            ->label('إلى صفحة')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $page): Builder => $query->where('page_number', '>=', $page),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $page): Builder => $query->where('page_number', '<=', $page),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة صفحة')
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        // Auto-set page number if not provided
                        if (!isset($data['page_number']) || !$data['page_number']) {
                            $maxPage = $livewire->getOwnerRecord()->pages()->max('page_number') ?? 0;
                            $data['page_number'] = $maxPage + 1;
                        }
                        // Set book_id from volume
                        $data['book_id'] = $livewire->getOwnerRecord()->book_id;
                        
                        // Calculate word count if content exists
                        if (!empty($data['content'])) {
                            $data['word_count'] = str_word_count(strip_tags($data['content']));
                        }
                        
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->modalContent(function ($record) {
                        return view('filament.components.page-content', [
                            'record' => $record,
                        ]);
                    }),
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Update word count if content changed
                        if (!empty($data['content'])) {
                            $data['word_count'] = str_word_count(strip_tags($data['content']));
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation()
                    ->modalHeading('حذف الصفحة')
                    ->modalDescription('هل أنت متأكد من حذف هذه الصفحة؟ سيتم حذف جميع الهوامش المرتبطة بها.')
                    ->modalSubmitActionLabel('حذف'),
                Tables\Actions\Action::make('duplicate')
                    ->label('نسخ')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record, RelationManager $livewire) {
                        $maxPage = $livewire->getOwnerRecord()->pages()->max('page_number') ?? 0;
                        $newPage = $record->replicate();
                        $newPage->page_number = $maxPage + 1;
                        $newPage->title = $record->title ? $record->title . ' (نسخة)' : null;
                        $newPage->save();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('نسخ الصفحة')
                    ->modalDescription('هل تريد إنشاء نسخة من هذه الصفحة؟')
                    ->modalSubmitActionLabel('نسخ'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('حذف الصفحات المحددة')
                        ->modalDescription('هل أنت متأكد من حذف الصفحات المحددة؟ سيتم حذف جميع الهوامش المرتبطة بها.')
                        ->modalSubmitActionLabel('حذف'),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('نشر المحدد')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('نشر الصفحات')
                        ->modalDescription('هل تريد نشر الصفحات المحددة؟')
                        ->modalSubmitActionLabel('نشر'),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('إلغاء نشر المحدد')
                        ->icon('heroicon-o-eye-slash')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('إلغاء نشر الصفحات')
                        ->modalDescription('هل تريد إلغاء نشر الصفحات المحددة؟')
                        ->modalSubmitActionLabel('إلغاء النشر'),
                ]),
            ])
            ->defaultSort('page_number')
            ->reorderable('page_number');
    }
}