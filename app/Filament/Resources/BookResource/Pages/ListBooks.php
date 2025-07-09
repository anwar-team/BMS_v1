<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBooks extends ListRecords
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة كتاب جديد')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'الكل' => Tab::make()
                ->badge(fn () => \App\Models\Book::count()),
                
            'منشور' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published'))
                ->badge(fn () => \App\Models\Book::where('status', 'published')->count())
                ->badgeColor('success'),
                
            'مسودة' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->badge(fn () => \App\Models\Book::where('status', 'draft')->count())
                ->badgeColor('gray'),
                
            'مؤرشف' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived'))
                ->badge(fn () => \App\Models\Book::where('status', 'archived')->count())
                ->badgeColor('warning'),
        ];
    }
}
