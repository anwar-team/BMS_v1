<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class ContentManagement extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'إدارة المحتوى';
    
    protected static ?string $slug = 'content-management';
    
    protected static ?int $navigationSort = 2;
    
    public static function getNavigationBadge(): ?string
    {
        return null;
    }
}