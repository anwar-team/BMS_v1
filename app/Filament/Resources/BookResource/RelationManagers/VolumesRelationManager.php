<?php

namespace App\Filament\Resources\BookResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VolumesRelationManager extends RelationManager
{
    protected static string $relationship = 'volumes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان المجلد')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('وصف المجلد')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('volume_number')
                    ->label('رقم المجلد')
                    ->numeric()
                    ->required()
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
                Forms\Components\TextInput::make('total_pages')
                    ->label('إجمالي الصفحات')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Toggle::make('is_published')
                    ->label('منشور')
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
                Tables\Columns\TextColumn::make('volume_number')
                    ->label('رقم المجلد')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('start_page')
                    ->label('من صفحة')
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('end_page')
                    ->label('إلى صفحة')
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('total_pages')
                    ->label('إجمالي الصفحات')
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('chapters_count')
                    ->label('عدد الفصول')
                    ->counts('chapters')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('عدد الصفحات')
                    ->counts('pages')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('display_order')
                    ->label('الترتيب')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('منشور'),
                Tables\Filters\Filter::make('has_pages')
                    ->label('يحتوي على صفحات')
                    ->query(fn (Builder $query): Builder => $query->has('pages')),
                Tables\Filters\Filter::make('has_chapters')
                    ->label('يحتوي على فصول')
                    ->query(fn (Builder $query): Builder => $query->has('chapters')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة مجلد')
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        // Auto-set volume number if not provided
                        if (!isset($data['volume_number']) || !$data['volume_number']) {
                            $maxVolume = $livewire->getOwnerRecord()->volumes()->max('volume_number') ?? 0;
                            $data['volume_number'] = $maxVolume + 1;
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
                    ->modalHeading('حذف المجلد')
                    ->modalDescription('هل أنت متأكد من حذف هذا المجلد؟ سيتم حذف جميع الفصول والصفحات المرتبطة به.')
                    ->modalSubmitActionLabel('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('حذف المجلدات المحددة')
                        ->modalDescription('هل أنت متأكد من حذف المجلدات المحددة؟ سيتم حذف جميع الفصول والصفحات المرتبطة بها.')
                        ->modalSubmitActionLabel('حذف'),
                ]),
            ])
            ->defaultSort('volume_number')
            ->reorderable('display_order');
    }
}