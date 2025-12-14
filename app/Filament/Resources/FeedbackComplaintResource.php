<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackComplaintResource\Pages;
use App\Filament\Resources\FeedbackComplaintResource\RelationManagers;
use App\Filament\Resources\FeedbackComplaintResource\Widgets;
use App\Models\FeedbackComplaint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class FeedbackComplaintResource extends Resource
{
    protected static ?string $model = FeedbackComplaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    protected static ?string $navigationLabel = 'الملاحظات والشكاوى';
    
    protected static ?string $modelLabel = 'ملاحظة/شكوى';
    
    protected static ?string $pluralModelLabel = 'الملاحظات والشكاوى';
    
    protected static ?string $navigationGroup = 'إدارة الموقع';
    
    protected static ?int $navigationSort = 10;

    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الرسالة')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('نوع الرسالة')
                            ->options([
                                'feedback' => '⭐ ملاحظة/اقتراح',
                                'complaint' => '⚠️ شكوى/مشكلة',
                            ])
                            ->required()
                            ->native(false)
                            ->disabled()
                            ->dehydrated(true),
                        
                        Forms\Components\TextInput::make('subject')
                            ->label('الموضوع')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(true),
                        
                        Forms\Components\Textarea::make('message')
                            ->label('المحتوى')
                            ->required()
                            ->rows(6)
                            ->disabled()
                            ->dehydrated(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('حالة المعالجة')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => '⏳ معلقة',
                                'in_progress' => '🔄 قيد المعالجة',
                                'resolved' => '✅ محلولة',
                            ])
                            ->required()
                            ->native(false)
                            ->default('pending'),
                        
                        Forms\Components\Select::make('priority')
                            ->label('الأولوية')
                            ->options([
                                'low' => 'منخفضة',
                                'medium' => 'متوسطة',
                                'high' => 'عالية',
                            ])
                            ->required()
                            ->native(false)
                            ->default('medium'),
                        
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('ملاحظات الإدارة')
                            ->rows(4)
                            ->placeholder('أضف ملاحظات حول الإجراءات المتخذة...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('معلومات تقنية')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->label('عنوان IP')
                            ->disabled()
                            ->dehydrated(true),
                        
                        Forms\Components\Textarea::make('user_agent')
                            ->label('User Agent')
                            ->rows(2)
                            ->disabled()
                            ->dehydrated(true)
                            ->columnSpanFull(),
                        
                        Forms\Components\Placeholder::make('created_at')
                            ->label('تاريخ الإرسال')
                            ->content(fn ($record) => $record?->created_at?->format('Y/m/d - H:i:s') ?? '-'),
                        
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('آخر تحديث')
                            ->content(fn ($record) => $record?->updated_at?->format('Y/m/d - H:i:s') ?? '-'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn ($state) => $state === 'feedback' ? '⭐ ملاحظة' : '⚠️ شكوى')
                    ->colors([
                        'success' => 'feedback',
                        'danger' => 'complaint',
                    ]),
                
                Tables\Columns\TextColumn::make('subject')
                    ->label('الموضوع')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),
                
                Tables\Columns\TextColumn::make('message')
                    ->label('المحتوى')
                    ->searchable()
                    ->limit(50)
                    ->wrap()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'pending' => '⏳ معلقة',
                            'in_progress' => '🔄 قيد المعالجة',
                            'resolved' => '✅ محلولة',
                            default => $state,
                        };
                    })
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'in_progress',
                        'success' => 'resolved',
                    ]),
                
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('الأولوية')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'low' => 'منخفضة',
                            'medium' => 'متوسطة',
                            'high' => 'عالية',
                            default => $state,
                        };
                    })
                    ->colors([
                        'secondary' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                    ]),
                
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإرسال')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($state) => $state->format('Y/m/d - H:i:s')),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'feedback' => '⭐ ملاحظة',
                        'complaint' => '⚠️ شكوى',
                    ])
                    ->native(false),
                
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => '⏳ معلقة',
                        'in_progress' => '🔄 قيد المعالجة',
                        'resolved' => '✅ محلولة',
                    ])
                    ->native(false),
                
                SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options([
                        'low' => 'منخفضة',
                        'medium' => 'متوسطة',
                        'high' => 'عالية',
                    ])
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ]);
    }

    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        
        if ($count > 10) {
            return 'danger';
        }
        
        if ($count > 5) {
            return 'warning';
        }
        
        return 'success';
    }
    
    public static function getWidgets(): array
    {
        return [
            Widgets\FeedbackStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedbackComplaints::route('/'),
            'edit' => Pages\EditFeedbackComplaint::route('/{record}/edit'),
        ];
    }
}
