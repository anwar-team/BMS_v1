<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Blog\Post;
use App\Models\Blog\Category;
use App\Models\ContactUs;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class UsersAndBlogStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    // تعطيل التحديث التلقائي
    protected static ?string $pollingInterval = null;
    
    // Cache للبيانات لمدة 20 دقيقة
    protected static string $cacheKey = 'users_blog_stats_widget_data';
    protected static int $cacheDuration = 1200; // 20 دقيقة

    protected function getStats(): array
    {
        // استخدام Cache لتجنب إعادة حساب البيانات في كل مرة
        return Cache::remember(static::$cacheKey, static::$cacheDuration, function () {
            // استخدام queries محسنة
            $userStats = User::selectRaw('COUNT(*) as total_users')->first();
            $postStats = Post::selectRaw('
                COUNT(*) as total_posts,
                SUM(CASE WHEN status = "published" THEN 1 ELSE 0 END) as published_posts
            ')->first();
            $totalContacts = ContactUs::count();

            return [
                Stat::make(__('resource.stats.total_users'), $userStats->total_users)
                    ->description(__('resource.stats.total_users'))
                    ->descriptionIcon('heroicon-m-users')
                    ->color('primary'),

                Stat::make(__('resource.stats.blog_posts'), $postStats->total_posts)
                    ->description(__('resource.stats.blog_posts'))
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('success'),

                Stat::make(__('resource.stats.published_posts'), $postStats->published_posts)
                    ->description(__('resource.stats.published_posts'))
                    ->descriptionIcon('heroicon-m-eye')
                    ->color('info'),

                Stat::make(__('resource.contact.contact_us'), $totalContacts)
                    ->description(__('resource.stats.visitor_messages'))
                    ->descriptionIcon('heroicon-m-envelope')
                    ->color('warning'),
            ];
        });
    }
}