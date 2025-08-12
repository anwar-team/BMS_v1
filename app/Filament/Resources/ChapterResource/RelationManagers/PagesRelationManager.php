<?php

namespace App\Filament\Resources\ChapterResource\RelationManagers;

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
                Forms\Components\Select::make('volume_id')
                    ->label('المجلد')
                    ->relationship('volume', 'title', fn (Builder $query) => 
                        $query->where('book_id', $this->getOwnerRecord()->book_id)
                    )
                    ->searchable()
                    ->preload()
                    ->helperText('المجلد الذي تنتمي إليه الصفحة'),
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
                Tables\Columns\TextColumn::make('volume.title')
                    ->label('المجلد')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder('غير محدد')
                    ->toggleable(),
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
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشورة')
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
                Tables\Filters\SelectFilter::make('volume_id')
                    ->label('المجلد')
                    ->relationship('volume', 'title')
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
                        
                        // Set book_id from chapter
                        $data['book_id'] = $livewire->getOwnerRecord()->book_id;
                        
                        // Set volume_id from chapter if not provided
                        if (!isset($data['volume_id']) && $livewire->getOwnerRecord()->volume_id) {
                            $data['volume_id'] = $livewire->getOwnerRecord()->volume_id;
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
                    ->label('تعديل'),
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
                Tables\Actions\Action::make('move_to_chapter')
                    ->label('نقل إلى فصل آخر')
                    ->icon('heroicon-o-arrow-right')
                    ->form([
                        Forms\Components\Select::make('new_chapter_id')
                            ->label('الفصل الجديد')
                            ->options(function (RelationManager $livewire) {
                                return $livewire->getOwnerRecord()->book->chapters()
                                    ->where('id', '!=', $livewire->getOwnerRecord()->id)
                                    ->pluck('title', 'id');
                            })
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['chapter_id' => $data['new_chapter_id']]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('نقل الصفحة')
                    ->modalDescription('هل تريد نقل هذه الصفحة إلى فصل آخر؟')
                    ->modalSubmitActionLabel('نقل'),
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
                    Tables\Actions\BulkAction::make('move_to_chapter')
                        ->label('نقل إلى فصل آخر')
                        ->icon('heroicon-o-arrow-right')
                        ->form([
                            Forms\Components\Select::make('new_chapter_id')
                                ->label('الفصل الجديد')
                                ->options(function (RelationManager $livewire) {
                                    return $livewire->getOwnerRecord()->book->chapters()
                                        ->where('id', '!=', $livewire->getOwnerRecord()->id)
                                        ->pluck('title', 'id');
                                })
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(fn ($record) => $record->update(['chapter_id' => $data['new_chapter_id']]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('نقل الصفحات')
                        ->modalDescription('هل تريد نقل الصفحات المحددة إلى فصل آخر؟')
                        ->modalSubmitActionLabel('نقل'),
                ]),
            ])
            ->defaultSort('page_number')
            ->reorderable('page_number');
    }
}