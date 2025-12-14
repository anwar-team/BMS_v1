<?php

namespace App\Filament\Resources\FeedbackComplaintResource\Pages;

use App\Filament\Resources\FeedbackComplaintResource;
use App\Models\FeedbackComplaint;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListFeedbackComplaints extends ListRecords
{
    protected static string $resource = FeedbackComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // لا نحتاج Create لأن الزوار يرسلون فقط
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            FeedbackComplaintResource\Widgets\FeedbackStatsOverview::class,
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(FeedbackComplaint::count()),
            
            'pending' => Tab::make('المعلقة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(FeedbackComplaint::where('status', 'pending')->count())
                ->badgeColor('warning'),
            
            'in_progress' => Tab::make('قيد المعالجة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress'))
                ->badge(FeedbackComplaint::where('status', 'in_progress')->count())
                ->badgeColor('primary'),
            
            'resolved' => Tab::make('المحلولة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'resolved'))
                ->badge(FeedbackComplaint::where('status', 'resolved')->count())
                ->badgeColor('success'),
            
            'feedbacks' => Tab::make('الملاحظات')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'feedback'))
                ->badge(FeedbackComplaint::where('type', 'feedback')->count())
                ->badgeColor('success'),
            
            'complaints' => Tab::make('الشكاوى')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'complaint'))
                ->badge(FeedbackComplaint::where('type', 'complaint')->count())
                ->badgeColor('danger'),
        ];
    }
}
