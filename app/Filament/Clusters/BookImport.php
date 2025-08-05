<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class BookImport extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    
    protected static ?string $navigationLabel = 'استيراد الكتب';
    
    protected static ?string $slug = 'book-import';
    
    protected static ?int $navigationSort = 3;
    
    public static function getNavigationBadge(): ?string
    {
        return null;
    }
}