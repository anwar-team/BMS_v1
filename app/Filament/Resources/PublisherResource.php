<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublisherResource\Pages;
use App\Filament\Resources\PublisherResource\RelationManagers;
use App\Models\Publisher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PublisherResource extends Resource
{
    protected static ?string $model = Publisher::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    
    protected static ?string $navigationLabel = 'الناشرين';
    
    protected static ?string $modelLabel = 'ناشر';
    
    protected static ?string $pluralModelLabel = 'الناشرين';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('المعلومات الأساسية')
                    ->description('أدخل المعلومات الأساسية للناشر')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('اسم الناشر')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('مثال: دار الكتب العلمية')
                                ->columnSpan(1),
                            
                            Forms\Components\TextInput::make('country')
                                ->label('البلد')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('مثال: لبنان، السعودية، مصر')
                                ->columnSpan(1),
                        ]),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('وصف الناشر')
                            ->rows(4)
                            ->maxLength(1000)
                            ->placeholder('نبذة عن الناشر وتخصصاته...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('معلومات الاتصال')
                    ->description('بيانات الاتصال والتواصل مع الناشر')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('email')
                                ->label('البريد الإلكتروني')
                                ->email()
                                ->maxLength(255)
                                ->placeholder('info@publisher.com')
                                ->prefixIcon('heroicon-o-envelope')
                                ->columnSpan(1),
                            
                            Forms\Components\TextInput::make('phone')
                                ->label('رقم الهاتف')
                                ->tel()
                                ->maxLength(255)
                                ->placeholder('+966 11 123 4567')
                                ->prefixIcon('heroicon-o-phone')
                                ->columnSpan(1),
                        ]),
                        
                        Forms\Components\TextInput::make('website_url')
                            ->label('رابط الموقع الإلكتروني')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.publisher.com')
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('إعدادات الحالة')
                    ->description('إعدادات نشاط الناشر في النظام')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->helperText('تحديد ما إذا كان الناشر نشطاً في النظام أم لا')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الناشر')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('country')
                    ->label('البلد')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ البريد الإلكتروني')
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-o-envelope')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الهاتف')
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-o-phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('website_url')
                    ->label('الموقع الإلكتروني')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->website_url)
                    ->url(fn ($record) => $record->website_url)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-globe-alt')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('books_count')
                    ->label('عدد الكتب')
                    ->counts('books')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
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
                Tables\Filters\SelectFilter::make('country')
                    ->label('البلد')
                    ->options(function () {
                        return Publisher::distinct()
                            ->pluck('country', 'country')
                            ->filter()
                            ->sort();
                    })
                    ->multiple(),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('جميع الناشرين')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
                
                Tables\Filters\Filter::make('has_books')
                    ->label('لديه كتب')
                    ->query(fn (Builder $query): Builder => $query->has('books'))
                    ->toggle(),
                
                Tables\Filters\Filter::make('no_books')
                    ->label('ليس لديه كتب')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('books'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Publisher $record) {
                        if ($record->books()->count() > 0) {
                            throw new \Exception('لا يمكن حذف الناشر لأنه مرتبط بكتب. يرجى حذف الكتب أولاً أو تغيير ناشرها.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->books()->count() > 0) {
                                    throw new \Exception('لا يمكن حذف الناشر "' . $record->name . '" لأنه مرتبط بكتب.');
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('إلغاء تفعيل المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublishers::route('/'),
            'create' => Pages\CreatePublisher::route('/create'),
            'view' => Pages\ViewPublisher::route('/{record}'),
            'edit' => Pages\EditPublisher::route('/{record}/edit'),
        ];
    }
}