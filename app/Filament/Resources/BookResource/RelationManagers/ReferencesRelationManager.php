<?php

namespace App\Filament\Resources\BookResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReferencesRelationManager extends RelationManager
{
    protected static string $relationship = 'references';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان المرجع')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('author')
                    ->label('المؤلف')
                    ->maxLength(255),
                Forms\Components\TextInput::make('publisher')
                    ->label('الناشر')
                    ->maxLength(255),
                Forms\Components\TextInput::make('publication_year')
                    ->label('سنة النشر')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(date('Y')),
                Forms\Components\TextInput::make('edition')
                    ->label('الطبعة')
                    ->maxLength(100),
                Forms\Components\TextInput::make('volume')
                    ->label('المجلد')
                    ->maxLength(100),
                Forms\Components\TextInput::make('page_numbers')
                    ->label('أرقام الصفحات')
                    ->maxLength(255)
                    ->helperText('مثال: 123-145 أو 23, 45, 67'),
                Forms\Components\Select::make('reference_type')
                    ->label('نوع المرجع')
                    ->options([
                        'book' => 'كتاب',
                        'article' => 'مقال',
                        'journal' => 'مجلة',
                        'website' => 'موقع إلكتروني',
                        'thesis' => 'رسالة علمية',
                        'conference' => 'مؤتمر',
                        'manuscript' => 'مخطوطة',
                        'other' => 'أخرى',
                    ])
                    ->default('book')
                    ->required(),
                Forms\Components\TextInput::make('isbn')
                    ->label('الرقم الدولي للكتاب (ISBN)')
                    ->maxLength(20),
                Forms\Components\TextInput::make('doi')
                    ->label('المعرف الرقمي (DOI)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->label('الرابط الإلكتروني')
                    ->url()
                    ->maxLength(500),
                Forms\Components\DatePicker::make('access_date')
                    ->label('تاريخ الوصول')
                    ->helperText('للمراجع الإلكترونية'),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('citation_count')
                    ->label('عدد الاستشهادات')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->helperText('يتم حسابه تلقائياً'),
                Forms\Components\Toggle::make('is_primary')
                    ->label('مرجع أساسي')
                    ->default(false)
                    ->helperText('هل هذا مرجع أساسي للكتاب؟'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->tooltip(function ($record) {
                        return $record->title;
                    }),
                Tables\Columns\TextColumn::make('author')
                    ->label('المؤلف')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'book' => 'success',
                        'article' => 'info',
                        'journal' => 'warning',
                        'website' => 'danger',
                        'thesis' => 'gray',
                        'conference' => 'primary',
                        'manuscript' => 'secondary',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'book' => 'كتاب',
                        'article' => 'مقال',
                        'journal' => 'مجلة',
                        'website' => 'موقع إلكتروني',
                        'thesis' => 'رسالة علمية',
                        'conference' => 'مؤتمر',
                        'manuscript' => 'مخطوطة',
                        'other' => 'أخرى',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('publisher')
                    ->label('الناشر')
                    ->searchable()
                    ->limit(25)
                    ->placeholder('غير محدد')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('publication_year')
                    ->label('سنة النشر')
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('page_numbers')
                    ->label('الصفحات')
                    ->limit(20)
                    ->placeholder('غير محدد')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('citation_count')
                    ->label('الاستشهادات')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state <= 5 => 'warning',
                        $state <= 15 => 'success',
                        default => 'primary',
                    }),
                Tables\Columns\IconColumn::make('is_primary')
                    ->label('أساسي')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('reference_type')
                    ->label('نوع المرجع')
                    ->options([
                        'book' => 'كتاب',
                        'article' => 'مقال',
                        'journal' => 'مجلة',
                        'website' => 'موقع إلكتروني',
                        'thesis' => 'رسالة علمية',
                        'conference' => 'مؤتمر',
                        'manuscript' => 'مخطوطة',
                        'other' => 'أخرى',
                    ]),
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('مرجع أساسي'),
                Tables\Filters\Filter::make('has_citations')
                    ->label('له استشهادات')
                    ->query(fn (Builder $query): Builder => $query->where('citation_count', '>', 0)),
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
                    ->label('إضافة مرجع'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->modalHeading(fn ($record) => 'عرض المرجع: ' . $record->title)
                    ->modalContent(function ($record) {
                        $citation = $record->getFormattedCitationAttribute();
                        return view('filament.components.reference-details', [
                            'record' => $record,
                            'citation' => $citation,
                        ]);
                    }),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation()
                    ->modalHeading('حذف المرجع')
                    ->modalDescription('هل أنت متأكد من حذف هذا المرجع؟ سيتم حذف جميع الاستشهادات المرتبطة به.')
                    ->modalSubmitActionLabel('حذف'),
                Tables\Actions\Action::make('copy_citation')
                    ->label('نسخ الاستشهاد')
                    ->icon('heroicon-o-clipboard')
                    ->action(function ($record) {
                        $citation = $record->getFormattedCitationAttribute();
                        // This would typically use JavaScript to copy to clipboard
                        // For now, we'll show it in a modal
                        return redirect()->back()->with('citation', $citation);
                    })
                    ->modalContent(fn ($record) => 
                        '<div class="p-4 bg-gray-50 rounded-lg">' . 
                        '<p class="text-sm text-gray-600 mb-2">الاستشهاد المنسق:</p>' .
                        '<p class="font-mono text-sm">' . $record->getFormattedCitationAttribute() . '</p>' .
                        '</div>'
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('حذف المراجع المحددة')
                        ->modalDescription('هل أنت متأكد من حذف المراجع المحددة؟ سيتم حذف جميع الاستشهادات المرتبطة بها.')
                        ->modalSubmitActionLabel('حذف'),
                    Tables\Actions\BulkAction::make('mark_primary')
                        ->label('تحديد كأساسي')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_primary' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تحديد المراجع كأساسية')
                        ->modalDescription('هل تريد تحديد المراجع المحددة كمراجع أساسية؟')
                        ->modalSubmitActionLabel('تحديد'),
                ]),
            ])
            ->defaultSort('is_primary', 'desc')
            ->secondarySort('citation_count', 'desc');
    }
}