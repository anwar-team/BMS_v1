<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
        'slug',
        'logo_path',
        'icon_type',
        'icon_url',
        'icon_name',
        'icon_color',
        'icon_size',
        'icon_library',
    ];// إضافة logo_path
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع القسم الأب
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(BookSection::class, 'parent_id');
    }

    /**
     * العلاقة مع الأقسام الفرعية
     */
    public function children(): HasMany
    {
        return $this->hasMany(BookSection::class, 'parent_id');
    }

    /**
     * العلاقة مع الكتب
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'book_section_id');
    }

    /**
     * scope للأقسام النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * scope للأقسام الرئيسية
     */
    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get sections with book count for homepage
     */
    public static function getForHomepage($limit = 6)
    {
        return self::where('is_active', true)
            ->whereNull('parent_id')
            ->withCount('books')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all active sections with book count for categories page
     */
    public static function getAllWithBookCount()
    {
        return self::where('is_active', true)
            ->withCount('books')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get section by slug
     */
    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->where('is_active', true)->first();
    }

    /**
     * Get the icon URL based on icon type
     */
    public function getIconUrlAttribute()
    {
        switch ($this->icon_type) {
            case 'upload':
                return $this->icon_url ? asset('storage/' . $this->icon_url) : null;
            case 'url':
                return $this->icon_url;
            case 'library':
                return $this->getLibraryIconUrl();
            case 'color':
                return null; // Color icons don't have URLs
            default:
                return $this->logo_path ? asset('images/' . $this->logo_path) : null;
        }
    }

    /**
     * Get library icon URL based on library type
     */
    protected function getLibraryIconUrl()
    {
        if (!$this->icon_name || !$this->icon_library) {
            return null;
        }

        switch ($this->icon_library) {
            case 'heroicons':
                return "https://heroicons.com/24/outline/{$this->icon_name}";
            case 'fontawesome':
                return "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/svgs/solid/{$this->icon_name}.svg";
            default:
                return null;
        }
    }

    /**
     * Get icon HTML for display
     */
    public function getIconHtmlAttribute()
    {
        $size = $this->getIconSizeClass();
        
        switch ($this->icon_type) {
            case 'upload':
            case 'url':
                $url = $this->getIconUrlAttribute();
                return $url ? "<img src='{$url}' class='{$size}' alt='{$this->name}'>" : null;
                
            case 'library':
                return $this->getLibraryIconHtml($size);
                
            case 'color':
                return $this->getColorIconHtml($size);
                
            default:
                // Fallback to logo_path
                $logoUrl = $this->logo_path ? asset('images/' . $this->logo_path) : null;
                return $logoUrl ? "<img src='{$logoUrl}' class='{$size}' alt='{$this->name}'>" : null;
        }
    }

    /**
     * Get icon size CSS class
     */
    protected function getIconSizeClass()
    {
        switch ($this->icon_size) {
            case 'sm': return 'w-4 h-4';
            case 'md': return 'w-6 h-6';
            case 'lg': return 'w-8 h-8';
            case 'xl': return 'w-12 h-12';
            default: return 'w-6 h-6';
        }
    }

    /**
     * Get library icon HTML
     */
    protected function getLibraryIconHtml($sizeClass)
    {
        if (!$this->icon_name || !$this->icon_library) {
            return null;
        }

        $color = $this->icon_color ?: '#000000';
        
        switch ($this->icon_library) {
            case 'heroicons':
                // For Heroicons, we'll use SVG with proper classes
                return "<svg class='{$sizeClass}' fill='none' stroke='{$color}' stroke-width='2' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'>
                    <path stroke-linecap='round' stroke-linejoin='round' d='{$this->getHeroiconPath()}'></path>
                </svg>";
            case 'fontawesome':
                // For FontAwesome, we'll use the proper class structure
                $faClass = $this->getFontAwesomeClass();
                return "<i class='{$faClass} {$sizeClass}' style='color: {$color}'></i>";
            default:
                return null;
        }
    }

    /**
     * Get Heroicon SVG path based on icon name
     */
    protected function getHeroiconPath()
    {
        // Common Heroicons paths - you can expand this
        $paths = [
            'home' => 'm3 12 2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6',
            'book-open' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            'academic-cap' => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
            'star' => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
        ];
        
        return $paths[$this->icon_name] ?? $paths['book-open']; // Default to book-open
    }

    /**
     * Get FontAwesome class based on icon name
     */
    protected function getFontAwesomeClass()
    {
        // Map common icon names to FontAwesome classes
        $faClasses = [
            'home' => 'fas fa-home',
            'book' => 'fas fa-book',
            'book-open' => 'fas fa-book-open',
            'graduation-cap' => 'fas fa-graduation-cap',
            'star' => 'fas fa-star',
            'heart' => 'fas fa-heart',
            'user' => 'fas fa-user',
            'users' => 'fas fa-users',
            'cog' => 'fas fa-cog',
            'search' => 'fas fa-search',
        ];
        
        return $faClasses[$this->icon_name] ?? 'fas fa-book'; // Default to book
    }

    /**
     * Get color icon HTML (simple colored circle)
     */
    protected function getColorIconHtml($sizeClass)
    {
        $color = $this->icon_color ?: '#3B82F6';
        return "<div class='{$sizeClass} rounded-full' style='background-color: {$color}'></div>";
    }

    /**
     * Check if section has any icon
     */
    public function hasIcon()
    {
        return !empty($this->icon_type) || !empty($this->logo_path);
    }
}