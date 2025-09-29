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
    
    // Disable automatic refresh
    protected static ?string $pollingInterval = null;
    
    // Cache data for 15 minutes
    protected static string $cacheKey = 'users_blog_stats_widget_data';
    protected static int $cacheDuration = 900; // 15 minutes

    protected function getStats(): array
    {
        // Use Cache to avoid recalculating data every time
        return Cache::remember(static::$cacheKey, static::$cacheDuration, function () {
            // Use one optimized query instead of 4 separate queries
            $userStats = User::selectRaw('
                COUNT(*) as total_users,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as recent_users,
                COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_users
            ', [now()->subDays(30)])->first();
            
            $totalPosts = Post::count();

            return [
                Stat::make('Total Users', $userStats->total_users)
                    ->description('All users in the system')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('primary'),

                Stat::make('Verified Users', $userStats->verified_users)
                    ->description('Users with verified email addresses')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('Blog Posts', $totalPosts)
                    ->description('Published blog posts')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('info'),

                Stat::make('New Users', $userStats->recent_users)
                    ->description('New users in the last 30 days')
                    ->descriptionIcon('heroicon-m-user-plus')
                    ->color('warning'),
            ];
        });
    }
}