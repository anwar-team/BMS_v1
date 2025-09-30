<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestUsersTableWidget extends BaseWidget
{
    protected static ?string $heading = null;
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';
    
    // Disable automatic refresh
    protected static ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('dashboard.widgets.tables.latest_users'))
            ->query(
                User::query()
                    ->with('roles')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('users.fields.first_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('users.fields.last_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('username')
                    ->label(__('users.fields.username'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('users.fields.email'))
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('users.fields.roles'))
                    ->badge()
                    ->separator(', ')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'super_admin' => __('users.roles.super_admin'),
                        'admin' => __('users.roles.admin'),
                        'editor' => __('users.roles.editor'),
                        'author' => __('users.roles.author'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'editor' => 'info',
                        'author' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('users.fields.registration_date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label(__('dashboard.actions.edit'))
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (User $record): string => route('filament.admin.resources.users.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}