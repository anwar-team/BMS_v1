<?php

namespace App\Filament\Resources\PageResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FootnotesRelationManager extends RelationManager
{
    protected static string $relationship = 'footnotes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('footnote_number')
                    ->label('رقم الهامش')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->helperText('رقم الهامش في الصفحة'),
                Forms\Components\TextInput::make('reference_text')
                    ->label('النص المرجعي')
                    ->maxLength(255)
                    ->helperText('النص الذي يشير إليه الهامش في المتن'),
                Forms\Components\Select::make('footnote_type')
                    ->label('نوع الهامش')
                    ->options([
                        'reference' => 'مرجع',
                        'explanation' => 'توضيح',
                        'translation' => 'ترجمة',
                        'comment' => 'تعليق',
                        'cross_reference' => 'إحالة',
                        'biographical' => 'ترجمة شخصية',
                        'linguistic' => 'لغوي',
                        'historical' => 'تاريخي',
                        'other' => 'أخرى',
                    ])
                    ->required()
                    ->default('reference'),
                Forms\Components\RichEditor::make('content')
                    ->label('محتوى الهامش')
                    ->required()
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'link',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'undo',
                        'redo',
                    ]),
                Forms\Components\TextInput::make('source_reference')
                    ->label('المرجع')
                    ->maxLength(500)
                    ->helperText('المرجع الأصلي للهامش إن وجد'),
                Forms\Components\TextInput::make('page_reference')
                    ->label('رقم الصفحة في المرجع')
                    ->maxLength(50)
                    ->helperText('رقم الصفحة في المرجع المذكور'),
                Forms\Components\TextInput::make('volume_reference')
                    ->label('رقم المجلد في المرجع')
                    ->maxLength(50)
                    ->helperText('رقم المجلد في المرجع المذكور'),
                Forms\Components\Textarea::make('editor_notes')
                    ->label('ملاحظات المحرر')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('ملاحظات داخلية للمحررين'),
                Forms\Components\Toggle::make('is_verified')
                    ->label('تم التحقق')
                    ->default(false)
                    ->helperText('هل تم التحقق من صحة الهامش؟'),
                Forms\Components\Toggle::make('is_published')
                    ->label('منشور')
                    ->default(true),
                Forms\Components\TextInput::make('display_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->helperText('ترتيب الهامش في الصفحة (اختياري)'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('footnote_number')
            ->columns([
                Tables\Columns\TextColumn::make('footnote_number')
                    ->label('رقم الهامش')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('reference_text')
                    ->label('النص المرجعي')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('غير محدد')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('footnote_type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reference' => 'success',
                        'explanation' => 'info',
                        'translation' => 'warning',
                        'comment' => 'gray',
                        'cross_reference' => 'primary',
                        'biographical' => 'purple',
                        'linguistic' => 'orange',
                        'historical' => 'blue',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'reference' => 'مرجع',
                        'explanation' => 'توضيح',
                        'translation' => 'ترجمة',
                        'comment' => 'تعليق',
                        'cross_reference' => 'إحالة',
                        'biographical' => 'ترجمة شخصية',
                        'linguistic' => 'لغوي',
                        'historical' => 'تاريخي',
                        default => 'أخرى',
                    }),
                Tables\Columns\TextColumn::make('content')
                    ->label('المحتوى')
                    ->html()
                    ->limit(80)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('source_reference')
                    ->label('المرجع')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('غير محدد')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('page_reference')
                    ->label('صفحة المرجع')
                    ->searchable()
                    ->placeholder('غير محدد')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('تم التحقق')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('display_order')
                    ->label('الترتيب')
                    ->sortable()
                    ->placeholder('تلقائي')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('footnote_type')
                    ->label('نوع الهامش')
                    ->options([
                        'reference' => 'مرجع',
                        'explanation' => 'توضيح',
                        'translation' => 'ترجمة',
                        'comment' => 'تعليق',
                        'cross_reference' => 'إحالة',
                        'biographical' => 'ترجمة شخصية',
                        'linguistic' => 'لغوي',
                        'historical' => 'تاريخي',
                        'other' => 'أخرى',
                    ])
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('تم التحقق'),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('منشور'),
                Tables\Filters\Filter::make('has_source')
                    ->label('له مرجع')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('source_reference')->where('source_reference', '!=', '')),
                Tables\Filters\Filter::make('has_page_reference')
                    ->label('له رقم صفحة')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('page_reference')->where('page_reference', '!=', '')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة هامش')
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        // Auto-set footnote number if not provided
                        if (!isset($data['footnote_number']) || !$data['footnote_number']) {
                            $maxFootnote = $livewire->getOwnerRecord()->footnotes()->max('footnote_number') ?? 0;
                            $data['footnote_number'] = $maxFootnote + 1;
                        }
                        
                        // Set display_order if not provided
                        if (!isset($data['display_order']) || !$data['display_order']) {
                            $data['display_order'] = $data['footnote_number'];
                        }
                        
                        return $data;
                    })
                    ->after(function ($record, RelationManager $livewire) {
                        // Update page's has_footnotes flag
                        $page = $livewire->getOwnerRecord();
                        $page->update(['has_footnotes' => $page->footnotes()->exists()]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->modalContent(function ($record) {
                        return view('filament.components.footnote-content', [
                            'record' => $record,
                        ]);
                    }),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation()
                    ->modalHeading('حذف الهامش')
                    ->modalDescription('هل أنت متأكد من حذف هذا الهامش؟')
                    ->modalSubmitActionLabel('حذف')
                    ->after(function ($record, RelationManager $livewire) {
                        // Update page's has_footnotes flag
                        $page = $livewire->getOwnerRecord();
                        $page->update(['has_footnotes' => $page->footnotes()->exists()]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('نسخ')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record, RelationManager $livewire) {
                        $maxFootnote = $livewire->getOwnerRecord()->footnotes()->max('footnote_number') ?? 0;
                        $newFootnote = $record->replicate();
                        $newFootnote->footnote_number = $maxFootnote + 1;
                        $newFootnote->display_order = $newFootnote->footnote_number;
                        $newFootnote->is_verified = false;
                        $newFootnote->save();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('نسخ الهامش')
                    ->modalDescription('هل تريد إنشاء نسخة من هذا الهامش؟')
                    ->modalSubmitActionLabel('نسخ'),
                Tables\Actions\Action::make('verify')
                    ->label('تحقق')
                    ->icon('heroicon-o-check-circle')
                    ->action(fn ($record) => $record->update(['is_verified' => true]))
                    ->visible(fn ($record) => !$record->is_verified)
                    ->requiresConfirmation()
                    ->modalHeading('تحقق من الهامش')
                    ->modalDescription('هل تريد تأكيد التحقق من صحة هذا الهامش؟')
                    ->modalSubmitActionLabel('تحقق'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('حذف الهوامش المحددة')
                        ->modalDescription('هل أنت متأكد من حذف الهوامش المحددة؟')
                        ->modalSubmitActionLabel('حذف')
                        ->after(function (RelationManager $livewire) {
                            // Update page's has_footnotes flag
                            $page = $livewire->getOwnerRecord();
                            $page->update(['has_footnotes' => $page->footnotes()->exists()]);
                        }),
                    Tables\Actions\BulkAction::make('verify')
                        ->label('تحقق من المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_verified' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تحقق من الهوامش')
                        ->modalDescription('هل تريد تأكيد التحقق من صحة الهوامش المحددة؟')
                        ->modalSubmitActionLabel('تحقق'),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('نشر المحدد')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('نشر الهوامش')
                        ->modalDescription('هل تريد نشر الهوامش المحددة؟')
                        ->modalSubmitActionLabel('نشر'),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('إلغاء نشر المحدد')
                        ->icon('heroicon-o-eye-slash')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('إلغاء نشر الهوامش')
                        ->modalDescription('هل تريد إلغاء نشر الهوامش المحددة؟')
                        ->modalSubmitActionLabel('إلغاء النشر'),
                ]),
            ])
            ->defaultSort('footnote_number')
            ->reorderable('display_order');
    }
}