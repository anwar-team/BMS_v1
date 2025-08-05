<?php

namespace App\Filament\Resources\BookResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AuthorsRelationManager extends RelationManager
{
    protected static string $relationship = 'authors';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم المؤلف')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('birth_year')
                    ->label('سنة الميلاد')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(date('Y')),
                Forms\Components\TextInput::make('death_year')
                    ->label('سنة الوفاة')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(date('Y'))
                    ->gte('birth_year'),
                Forms\Components\Textarea::make('biography')
                    ->label('السيرة الذاتية')
                    ->rows(4)
                    ->columnSpanFull(),
                // Pivot fields
                Forms\Components\Select::make('role')
                    ->label('دور المؤلف')
                    ->options([
                        'author' => 'مؤلف',
                        'editor' => 'محقق',
                        'translator' => 'مترجم',
                        'commentator' => 'شارح',
                    ])
                    ->default('author')
                    ->required(),
                Forms\Components\Toggle::make('is_main')
                    ->label('مؤلف رئيسي')
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
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المؤلف')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('birth_year')
                    ->label('سنة الميلاد')
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('death_year')
                    ->label('سنة الوفاة')
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('الدور')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'author' => 'مؤلف',
                        'editor' => 'محقق',
                        'translator' => 'مترجم',
                        'commentator' => 'شارح',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'author' => 'success',
                        'editor' => 'info',
                        'translator' => 'warning',
                        'commentator' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('pivot.is_main')
                    ->label('مؤلف رئيسي')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.display_order')
                    ->label('ترتيب العرض')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('books_count')
                    ->label('عدد الكتب')
                    ->counts('books')
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pivot.role')
                    ->label('دور المؤلف')
                    ->options([
                        'author' => 'مؤلف',
                        'editor' => 'محقق',
                        'translator' => 'مترجم',
                        'commentator' => 'شارح',
                    ]),
                Tables\Filters\TernaryFilter::make('pivot.is_main')
                    ->label('مؤلف رئيسي'),
                Tables\Filters\Filter::make('birth_year')
                    ->label('سنة الميلاد')
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
                                fn (Builder $query, $year): Builder => $query->where('birth_year', '>=', $year),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $year): Builder => $query->where('birth_year', '<=', $year),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة مؤلف')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Extract pivot data
                        $pivotData = [
                            'role' => $data['role'] ?? 'author',
                            'is_main' => $data['is_main'] ?? true,
                            'display_order' => $data['display_order'] ?? 1,
                        ];
                        
                        // Remove pivot data from main data
                        unset($data['role'], $data['is_main'], $data['display_order']);
                        
                        // Store pivot data for later use
                        session(['pivot_data' => $pivotData]);
                        
                        return $data;
                    })
                    ->after(function ($record, RelationManager $livewire) {
                        // Attach with pivot data
                        $pivotData = session('pivot_data', []);
                        $livewire->getOwnerRecord()->authors()->attach($record->id, $pivotData);
                        session()->forget('pivot_data');
                    }),
                Tables\Actions\AttachAction::make()
                    ->label('ربط مؤلف موجود')
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label('المؤلف')
                            ->relationship('authors', 'name')
                            ->searchable()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . ($record->birth_year ?? 'غير محدد') . ' - ' . ($record->death_year ?? 'غير محدد') . ')'),
                        Forms\Components\Select::make('role')
                            ->label('دور المؤلف')
                            ->options([
                                'author' => 'مؤلف',
                                'editor' => 'محقق',
                                'translator' => 'مترجم',
                                'commentator' => 'شارح',
                            ])
                            ->default('author')
                            ->required(),
                        Forms\Components\Toggle::make('is_main')
                            ->label('مؤلف رئيسي')
                            ->default(true),
                        Forms\Components\TextInput::make('display_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        return [
                            'role' => $data['role'],
                            'is_main' => $data['is_main'],
                            'display_order' => $data['display_order'],
                        ];
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->url(fn ($record) => route('filament.admin.resources.authors.view', $record)),
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->form([
                        Forms\Components\Select::make('role')
                            ->label('دور المؤلف')
                            ->options([
                                'author' => 'مؤلف',
                                'editor' => 'محقق',
                                'translator' => 'مترجم',
                                'commentator' => 'شارح',
                            ])
                            ->default(fn ($record) => $record->pivot->role)
                            ->required(),
                        Forms\Components\Toggle::make('is_main')
                            ->label('مؤلف رئيسي')
                            ->default(fn ($record) => $record->pivot->is_main),
                        Forms\Components\TextInput::make('display_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(fn ($record) => $record->pivot->display_order)
                            ->minValue(1),
                    ])
                    ->mutateFormDataUsing(function (array $data, $record, RelationManager $livewire): array {
                        // Update pivot data
                        $livewire->getOwnerRecord()->authors()->updateExistingPivot($record->id, [
                            'role' => $data['role'],
                            'is_main' => $data['is_main'],
                            'display_order' => $data['display_order'],
                        ]);
                        
                        return [];
                    }),
                Tables\Actions\DetachAction::make()
                    ->label('إلغاء الربط')
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء ربط المؤلف')
                    ->modalDescription('هل أنت متأكد من إلغاء ربط هذا المؤلف بالكتاب؟')
                    ->modalSubmitActionLabel('إلغاء الربط'),
                Tables\Actions\Action::make('set_main')
                    ->label('تعيين كمؤلف رئيسي')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(function ($record, RelationManager $livewire) {
                        // First, set all authors as non-main
                        $livewire->getOwnerRecord()->authors()->updateExistingPivot(
                            $livewire->getOwnerRecord()->authors()->pluck('id')->toArray(),
                            ['is_main' => false]
                        );
                        
                        // Then set this author as main
                        $livewire->getOwnerRecord()->authors()->updateExistingPivot($record->id, ['is_main' => true]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('تعيين مؤلف رئيسي')
                    ->modalDescription('هل تريد تعيين هذا المؤلف كمؤلف رئيسي للكتاب؟ سيتم إلغاء تعيين المؤلفين الآخرين كمؤلفين رئيسيين.')
                    ->modalSubmitActionLabel('تعيين')
                    ->visible(fn ($record) => !$record->pivot->is_main),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('إلغاء ربط المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('إلغاء ربط المؤلفين')
                        ->modalDescription('هل أنت متأكد من إلغاء ربط المؤلفين المحددين بالكتاب؟')
                        ->modalSubmitActionLabel('إلغاء الربط'),
                    Tables\Actions\BulkAction::make('set_role')
                        ->label('تغيير الدور')
                        ->icon('heroicon-o-user-group')
                        ->form([
                            Forms\Components\Select::make('role')
                                ->label('الدور الجديد')
                                ->options([
                                    'author' => 'مؤلف',
                                    'editor' => 'محقق',
                                    'translator' => 'مترجم',
                                    'commentator' => 'شارح',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records, RelationManager $livewire) {
                            foreach ($records as $record) {
                                $livewire->getOwnerRecord()->authors()->updateExistingPivot($record->id, [
                                    'role' => $data['role'],
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تغيير دور المؤلفين')
                        ->modalDescription('هل تريد تغيير دور المؤلفين المحددين؟')
                        ->modalSubmitActionLabel('تغيير'),
                ]),
            ])
            ->defaultSort('pivot.display_order')
            ->reorderable('pivot.display_order');
    }
}