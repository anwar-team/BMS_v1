<?php

namespace App\Filament\Resources\BookResource\RelationManagers;

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
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('page_number')
                        ->label('Page Number')
                        ->required()
                        ->numeric()
                        ->minValue(1),
                    
                    Forms\Components\Select::make('volume_id')
                        ->label('Volume')
                        ->relationship('volume', 'title', fn (Builder $query, $get) => 
                            $query->where('book_id', $this->getOwnerRecord()->id)
                        )
                        ->searchable()
                        ->preload(),
                ]),
                
                Forms\Components\Select::make('chapter_id')
                    ->label('Chapter')
                    ->relationship('chapter', 'title', fn (Builder $query, $get) => 
                        $query->where('book_id', $this->getOwnerRecord()->id)
                            ->when($get('volume_id'), fn ($q, $volumeId) => 
                                $q->where('volume_id', $volumeId)
                            )
                    )
                    ->searchable()
                    ->preload(),
                
                Forms\Components\RichEditor::make('content')
                    ->label('Page Content')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'h2',
                        'h3',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                    ])
                    ->columnSpanFull(),
                
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('word_count')
                        ->label('Word Count')
                        ->numeric()
                        ->disabled(),
                    
                    Forms\Components\TextInput::make('character_count')
                        ->label('Character Count')
                        ->numeric()
                        ->disabled(),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('page_number')
            ->columns([
                Tables\Columns\TextColumn::make('page_number')
                    ->label('Page Number')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('volume.title')
                    ->label('Volume')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('chapter.title')
                    ->label('Chapter')
                    ->sortable()
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('content')
                    ->label('Content')
                    ->limit(100) // Show only first 100 characters for performance
                    ->html()
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('word_count')
                    ->label('Words')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('volume_id')
                    ->label('Volume')
                    ->relationship('volume', 'title')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('chapter_id')
                    ->label('Chapter')
                    ->relationship('chapter', 'title')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('has_content')
                    ->label('Has Content')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('content')->where('content', '!=', '')
                    ),
                
                Tables\Filters\Filter::make('page_range')
                    ->label('Page Range')
                    ->form([
                        Forms\Components\TextInput::make('from')
                            ->label('From Page')
                            ->numeric(),
                        Forms\Components\TextInput::make('to')
                            ->label('To Page')
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
                    ->label('Add New Page'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected'),
                ]),
            ])
            ->defaultSort('page_number', 'asc')
            ->defaultPaginationPageOption(25) // Performance improvement: paginate to 25 pages
            ->poll('60s') // Update every minute instead of 30 seconds
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
