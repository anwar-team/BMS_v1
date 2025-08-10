<?php

namespace App\Filament\Resources\BookResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookMetadataRelationManager extends RelationManager
{
    protected static string $relationship = 'metadata';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('المفتاح')
                    ->required()
                    ->maxLength(255)
                    ->helperText('مفتاح البيانات الوصفية (مثل: isbn, edition, language)'),
                Forms\Components\Select::make('type')
                    ->label('نوع البيانات')
                    ->options([
                        'string' => 'نص',
                        'number' => 'رقم',
                        'boolean' => 'منطقي (صحيح/خطأ)',
                        'date' => 'تاريخ',
                        'json' => 'JSON',
                    ])
                    ->default('string')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('value', null)),
                Forms\Components\TextInput::make('value')
                    ->label('القيمة')
                    ->required()
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['string', 'number']))
                    ->numeric(fn (Forms\Get $get) => $get('type') === 'number'),
                Forms\Components\Toggle::make('value')
                    ->label('القيمة')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'boolean'),
                Forms\Components\DatePicker::make('value')
                    ->label('القيمة')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'date'),
                Forms\Components\Textarea::make('value')
                    ->label('القيمة (JSON)')
                    ->rows(4)
                    ->visible(fn (Forms\Get $get) => $get('type') === 'json')
                    ->helperText('أدخل البيانات بصيغة JSON صحيحة'),
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->rows(2)
                    ->columnSpanFull()
                    ->helperText('وصف اختياري للبيانات الوصفية'),
                Forms\Components\Toggle::make('is_public')
                    ->label('عام')
                    ->default(true)
                    ->helperText('هل يمكن عرض هذه البيانات للعامة؟'),
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
            ->recordTitleAttribute('key')
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('المفتاح')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        // Translate common keys to Arabic
                        $translations = [
                            'isbn' => 'الرقم الدولي للكتاب',
                            'edition' => 'الطبعة',
                            'language' => 'اللغة',
                            'pages_count' => 'عدد الصفحات',
                            'publication_year' => 'سنة النشر',
                            'genre' => 'النوع الأدبي',
                            'subject' => 'الموضوع',
                            'keywords' => 'الكلمات المفتاحية',
                            'copyright' => 'حقوق النشر',
                            'license' => 'الترخيص',
                        ];
                        return $translations[$state] ?? $state;
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string' => 'gray',
                        'number' => 'info',
                        'boolean' => 'success',
                        'date' => 'warning',
                        'json' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'string' => 'نص',
                        'number' => 'رقم',
                        'boolean' => 'منطقي',
                        'date' => 'تاريخ',
                        'json' => 'JSON',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('typed_value')
                    ->label('القيمة')
                    ->searchable(['value'])
                    ->limit(50)
                    ->formatStateUsing(function ($record) {
                        return $record->getTypedValueAttribute();
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(30)
                    ->placeholder('لا يوجد وصف')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('عام')
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
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع البيانات')
                    ->options([
                        'string' => 'نص',
                        'number' => 'رقم',
                        'boolean' => 'منطقي',
                        'date' => 'تاريخ',
                        'json' => 'JSON',
                    ]),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('عام'),
                Tables\Filters\Filter::make('common_keys')
                    ->label('المفاتيح الشائعة')
                    ->form([
                        Forms\Components\CheckboxList::make('keys')
                            ->label('المفاتيح')
                            ->options([
                                'isbn' => 'الرقم الدولي للكتاب',
                                'edition' => 'الطبعة',
                                'language' => 'اللغة',
                                'pages_count' => 'عدد الصفحات',
                                'publication_year' => 'سنة النشر',
                                'genre' => 'النوع الأدبي',
                                'subject' => 'الموضوع',
                            ])
                            ->columns(2),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['keys'] ?? null,
                            fn (Builder $query, $keys): Builder => $query->whereIn('key', $keys)
                        );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة بيانات وصفية')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Convert value based on type
                        if (isset($data['type']) && isset($data['value'])) {
                            switch ($data['type']) {
                                case 'boolean':
                                    $data['value'] = $data['value'] ? '1' : '0';
                                    break;
                                case 'date':
                                    if ($data['value']) {
                                        $data['value'] = date('Y-m-d', strtotime($data['value']));
                                    }
                                    break;
                                case 'json':
                                    if ($data['value']) {
                                        // Validate JSON
                                        $decoded = json_decode($data['value'], true);
                                        if (json_last_error() !== JSON_ERROR_NONE) {
                                            throw new \Exception('صيغة JSON غير صحيحة');
                                        }
                                    }
                                    break;
                            }
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Convert value based on type for editing
                        if (isset($data['type']) && isset($data['value'])) {
                            switch ($data['type']) {
                                case 'boolean':
                                    $data['value'] = $data['value'] ? '1' : '0';
                                    break;
                                case 'date':
                                    if ($data['value']) {
                                        $data['value'] = date('Y-m-d', strtotime($data['value']));
                                    }
                                    break;
                                case 'json':
                                    if ($data['value']) {
                                        $decoded = json_decode($data['value'], true);
                                        if (json_last_error() !== JSON_ERROR_NONE) {
                                            throw new \Exception('صيغة JSON غير صحيحة');
                                        }
                                    }
                                    break;
                            }
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('display_order')
            ->reorderable('display_order');
    }
}