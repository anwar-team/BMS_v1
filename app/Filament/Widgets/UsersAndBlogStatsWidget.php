<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Blog\Post;
use App\Models\Blog\Category;
use App\Models\ContactUs;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UsersAndBlogStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $totalPosts = Post::count();
        $publishedPosts = Post::where('status', 'published')->count();
        $totalContacts = ContactUs::count();

        return [
            Stat::make('إجمالي المستخدمين', $totalUsers)
                ->description('العدد الكلي للمستخدمين')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('مقالات المدونة', $totalPosts)
                ->description('إجمالي المقالات')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('المقالات المنشورة', $publishedPosts)
                ->description('المقالات المتاحة للقراء')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('رسائل التواصل', $totalContacts)
                ->description('رسائل من الزوار')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('warning'),
        ];
    }
}