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
                    ->label('View')
                    ->modalContent(function ($record) {
                        return view('filament.components.page-content', [
                            'record' => $record,
                        ]);
                    }),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Page')
                    ->modalDescription('Are you sure you want to delete this page? All associated footnotes will be deleted.')
                    ->modalSubmitActionLabel('Delete'),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record, RelationManager $livewire) {
                        $maxPage = $livewire->getOwnerRecord()->pages()->max('page_number') ?? 0;
                        $newPage = $record->replicate();
                        $newPage->page_number = $maxPage + 1;
                        $newPage->title = $record->title ? $record->title . ' (Copy)' : null;
                        $newPage->save();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Duplicate Page')
                    ->modalDescription('Do you want to create a copy of this page?')
                    ->modalSubmitActionLabel('Duplicate'),
                Tables\Actions\Action::make('move_to_chapter')
                    ->label('Move to Another Chapter')
                    ->icon('heroicon-o-arrow-right')
                    ->form([
                        Forms\Components\Select::make('new_chapter_id')
                            ->label('New Chapter')
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
                    ->modalHeading('Move Page')
                    ->modalDescription('Do you want to move this page to another chapter?')
                    ->modalSubmitActionLabel('Move'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Pages')
                        ->modalDescription('Are you sure you want to delete the selected pages? All associated footnotes will be deleted.')
                        ->modalSubmitActionLabel('Delete'),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Publish Pages')
                        ->modalDescription('Do you want to publish the selected pages?')
                        ->modalSubmitActionLabel('Publish'),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Unpublish Pages')
                        ->modalDescription('Do you want to unpublish the selected pages?')
                        ->modalSubmitActionLabel('Unpublish'),
                    Tables\Actions\BulkAction::make('move_to_chapter')
                        ->label('Move to Another Chapter')
                        ->icon('heroicon-o-arrow-right')
                        ->form([
                            Forms\Components\Select::make('new_chapter_id')
                                ->label('New Chapter')
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
                        ->modalHeading('Move Pages')
                        ->modalDescription('Do you want to move the selected pages to another chapter?')
                        ->modalSubmitActionLabel('Move'),
                ]),
            ])
            ->defaultSort('page_number')
            ->reorderable('page_number');
    }
}