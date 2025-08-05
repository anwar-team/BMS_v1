<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class BookManagement extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationLabel = 'الكتب وإدراة المحتوى ';
    
    protected static ?string $slug = 'book-management';
    
    protected static ?int $navigationSort = 1;
    
    public static function getNavigationBadge(): ?string
    {
        return null;
    }
}