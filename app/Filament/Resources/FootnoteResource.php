<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\BookManagement;

use App\Filament\Resources\FootnoteResource\Pages;
use App\Filament\Resources\FootnoteResource\RelationManagers;
use App\Models\Footnote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FootnoteResource extends Resource
{
    protected static ?string $model = Footnote::class;
    protected static ?string $cluster = BookManagement::class;
    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Book Management';
    
    protected static ?string $navigationLabel = 'الحواشي';
    
    protected static ?string $modelLabel = 'حاشية';
    
    protected static ?string $pluralModelLabel = 'الحواشي';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->label('الكتاب')
                    ->relationship('book', 'title')
                    ->required(),
                Forms\Components\Select::make('volume_id')
                    ->label('المجلد')
                    ->relationship('volume', 'title')
                    ->nullable(),
                Forms\Components\Select::make('chapter_id')
                    ->label('الفصل')
                    ->relationship('chapter', 'title')
                    ->nullable(),
                Forms\Components\Select::make('page_id')
                    ->label('الصفحة')
                    ->relationship('page', 'page_number')
                    ->nullable(),
                Forms\Components\TextInput::make('footnote_number')
                    ->label('رقم الحاشية')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->label('محتوى الحاشية')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('reference_text')
                    ->label('النص المرجعي')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('position_in_page')
                    ->label('الموقع في الصفحة')
                    ->numeric(),
                Forms\Components\TextInput::make('order_in_page')
                    ->label('الترتيب في الصفحة')
                    ->numeric(),
                Forms\Components\Select::make('type')
                    ->label('نوع الحاشية')
                    ->options([
                        'explanation' => 'شرح',
                        'reference' => 'مرجع',
                        'translation' => 'ترجمة',
                        'note' => 'ملاحظة',
                    ])
                    ->nullable(),
                Forms\Components\Toggle::make('is_original')
                    ->label('حاشية أصلية'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->label('الكتاب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('page.page_number')
                    ->label('رقم الصفحة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('footnote_number')
                    ->label('رقم الحاشية')
                    ->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('محتوى الحاشية')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع الحاشية')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'explanation' => 'info',
                        'reference' => 'success',
                        'translation' => 'warning',
                        'note' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_original')
                    ->label('أصلية')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order_in_page')
                    ->label('الترتيب')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFootnotes::route('/'),
            'create' => Pages\CreateFootnote::route('/create'),
            'edit' => Pages\EditFootnote::route('/{record}/edit'),
        ];
    }
}
