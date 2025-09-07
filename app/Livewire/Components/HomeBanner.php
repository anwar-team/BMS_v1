<?php

namespace App\Livewire\Components;

use App\Models\Banner\Content as BannerContent;
use Livewire\Component;

class HomeBanner extends Component
{
    public $banners;
    
    /**
     * تهيئة المكون وجلب البانرز
     */
    public function mount()
    {
        $this->loadBanners();
    }
    
    /**
     * جلب البانرز من قاعدة البيانات
     */
    public function loadBanners()
    {
        $this->banners = BannerContent::whereHas('category', function($query) {
            $query->where('slug', 'home-banner');
        })
        ->active()
        ->orderBy('sort')
        ->with(['media'])
        ->take(5)
        ->get();
        
        // إرسال إشارة لإعادة تهيئة Swiper بعد تحديث البيانات
        $this->dispatch('init-swiper');
    }
    
    /**
     * رندر المكون
     */
    public function render()
    {
        return view('livewire.components.home-banner');
    }
}