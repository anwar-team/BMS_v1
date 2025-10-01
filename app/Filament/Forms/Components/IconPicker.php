<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Select;

class IconPicker extends Select
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->searchable();
        $this->allowHtml();
        $this->getSearchResultsUsing(function (string $search, callable $get) {
            $library = $get('icon_library');
            $icons = $this->getIconsForLibrary($library);
            
            if (empty($search)) {
                return array_slice($icons, 0, 50, true); // عرض أول 50 أيقونة
            }
            
            return array_filter($icons, function($label, $value) use ($search) {
                return str_contains(strtolower($value), strtolower($search)) ||
                       str_contains(strtolower($label), strtolower($search));
            }, ARRAY_FILTER_USE_BOTH);
        });
        
        $this->getOptionLabelUsing(function ($value, callable $get) {
            $library = $get('icon_library');
            $color = $get('icon_color') ?? '#3B82F6';
            
            return $this->renderIconOption($library, $value, $color);
        });
        
        $this->optionsLimit(50);
        $this->preload();
    }
    
    protected function getIconsForLibrary(?string $library): array
    {
        return match($library) {
            'heroicons' => $this->getHeroicons(),
            'fontawesome' => $this->getFontAwesomeIcons(),
            default => []
        };
    }
    
    protected function getHeroicons(): array
    {
        return [
            'home' => 'الرئيسية - Home',
            'user' => 'المستخدم - User',
            'users' => 'المستخدمون - Users',
            'book' => 'كتاب - Book',
            'book-open' => 'كتاب مفتوح - Book Open',
            'academic-cap' => 'قبعة أكاديمية - Academic Cap',
            'star' => 'نجمة - Star',
            'heart' => 'قلب - Heart',
            'eye' => 'عين - Eye',
            'search' => 'بحث - Search',
            'plus' => 'إضافة - Plus',
            'minus' => 'طرح - Minus',
            'check' => 'صح - Check',
            'x' => 'إغلاق - X',
            'arrow-right' => 'سهم يمين - Arrow Right',
            'arrow-left' => 'سهم يسار - Arrow Left',
            'arrow-up' => 'سهم أعلى - Arrow Up',
            'arrow-down' => 'سهم أسفل - Arrow Down',
            'download' => 'تحميل - Download',
            'upload' => 'رفع - Upload',
            'folder' => 'مجلد - Folder',
            'folder-open' => 'مجلد مفتوح - Folder Open',
            'document' => 'مستند - Document',
            'document-text' => 'مستند نصي - Document Text',
            'clipboard' => 'حافظة - Clipboard',
            'calendar' => 'تقويم - Calendar',
            'clock' => 'ساعة - Clock',
            'mail' => 'بريد - Mail',
            'phone' => 'هاتف - Phone',
            'globe' => 'كرة أرضية - Globe',
            'map' => 'خريطة - Map',
            'location-marker' => 'علامة موقع - Location Marker',
            'camera' => 'كاميرا - Camera',
            'photograph' => 'صورة - Photograph',
            'film' => 'فيلم - Film',
            'music-note' => 'نوتة موسيقية - Music Note',
            'volume-up' => 'صوت عالي - Volume Up',
            'volume-off' => 'صوت مغلق - Volume Off',
            'cog' => 'إعدادات - Settings',
            'adjustments' => 'تعديلات - Adjustments',
            'key' => 'مفتاح - Key',
            'lock-closed' => 'قفل مغلق - Lock Closed',
            'lock-open' => 'قفل مفتوح - Lock Open',
            'shield-check' => 'درع محمي - Shield Check',
            'exclamation' => 'تعجب - Exclamation',
            'information-circle' => 'معلومات - Information',
            'question-mark-circle' => 'علامة استفهام - Question Mark',
            'light-bulb' => 'مصباح - Light Bulb',
            'fire' => 'نار - Fire',
            'sun' => 'شمس - Sun',
            'moon' => 'قمر - Moon',
            'cloud' => 'سحابة - Cloud',
            'lightning-bolt' => 'صاعقة - Lightning Bolt',
            'gift' => 'هدية - Gift',
            'shopping-cart' => 'عربة تسوق - Shopping Cart',
            'shopping-bag' => 'حقيبة تسوق - Shopping Bag',
            'credit-card' => 'بطاقة ائتمان - Credit Card',
            'currency-dollar' => 'دولار - Dollar',
            'chart-bar' => 'رسم بياني - Chart Bar',
            'chart-pie' => 'رسم دائري - Chart Pie',
            'trending-up' => 'اتجاه صاعد - Trending Up',
            'trending-down' => 'اتجاه هابط - Trending Down',
            'book-open' => 'كتاب مفتوح - Book Open',
            'library' => 'مكتبة - Library',
            'collection' => 'مجموعة - Collection',
            'bookmark' => 'إشارة مرجعية - Bookmark',
            'tag' => 'علامة - Tag',
            'flag' => 'علم - Flag',
            'bell' => 'جرس - Bell',
            'chat' => 'محادثة - Chat',
            'menu' => 'قائمة - Menu',
            'dots-vertical' => 'نقاط عمودية - Dots Vertical',
            'dots-horizontal' => 'نقاط أفقية - Dots Horizontal',
            'refresh' => 'تحديث - Refresh',
            'save' => 'حفظ - Save',
            'edit' => 'تحرير - Edit',
            'trash' => 'حذف - Trash',
            'duplicate' => 'نسخ - Duplicate',
            'share' => 'مشاركة - Share',
            'print' => 'طباعة - Print',
            'external-link' => 'رابط خارجي - External Link',
            'link' => 'رابط - Link',
            'paperclip' => 'مشبك ورق - Paperclip',
            'archive' => 'أرشيف - Archive',
            'inbox' => 'صندوق الوارد - Inbox',
            'reply' => 'رد - Reply',
            'forward' => 'إعادة توجيه - Forward',
            'filter' => 'تصفية - Filter',
            'sort-ascending' => 'ترتيب تصاعدي - Sort Ascending',
            'sort-descending' => 'ترتيب تنازلي - Sort Descending',
            'view-list' => 'عرض قائمة - View List',
            'view-grid' => 'عرض شبكة - View Grid',
            'zoom-in' => 'تكبير - Zoom In',
            'zoom-out' => 'تصغير - Zoom Out',
        ];
    }
    
    protected function getFontAwesomeIcons(): array
    {
        return [
            'home' => 'الرئيسية - Home',
            'user' => 'المستخدم - User',
            'users' => 'المستخدمون - Users',
            'book' => 'كتاب - Book',
            'book-open' => 'كتاب مفتوح - Book Open',
            'graduation-cap' => 'قبعة تخرج - Graduation Cap',
            'star' => 'نجمة - Star',
            'heart' => 'قلب - Heart',
            'eye' => 'عين - Eye',
            'search' => 'بحث - Search',
            'plus' => 'إضافة - Plus',
            'minus' => 'طرح - Minus',
            'check' => 'صح - Check',
            'times' => 'إغلاق - Times',
            'arrow-right' => 'سهم يمين - Arrow Right',
            'arrow-left' => 'سهم يسار - Arrow Left',
            'arrow-up' => 'سهم أعلى - Arrow Up',
            'arrow-down' => 'سهم أسفل - Arrow Down',
            'download' => 'تحميل - Download',
            'upload' => 'رفع - Upload',
            'folder' => 'مجلد - Folder',
            'folder-open' => 'مجلد مفتوح - Folder Open',
            'file' => 'ملف - File',
            'file-text' => 'ملف نصي - File Text',
            'clipboard' => 'حافظة - Clipboard',
            'calendar' => 'تقويم - Calendar',
            'clock' => 'ساعة - Clock',
            'envelope' => 'مظروف - Envelope',
            'phone' => 'هاتف - Phone',
            'globe' => 'كرة أرضية - Globe',
            'map' => 'خريطة - Map',
            'map-marker' => 'علامة موقع - Map Marker',
            'camera' => 'كاميرا - Camera',
            'image' => 'صورة - Image',
            'video' => 'فيديو - Video',
            'music' => 'موسيقى - Music',
            'volume-up' => 'صوت عالي - Volume Up',
            'volume-mute' => 'صوت مغلق - Volume Mute',
            'cog' => 'إعدادات - Cog',
            'sliders' => 'منزلقات - Sliders',
            'key' => 'مفتاح - Key',
            'lock' => 'قفل - Lock',
            'unlock' => 'فتح قفل - Unlock',
            'shield' => 'درع - Shield',
            'exclamation' => 'تعجب - Exclamation',
            'info' => 'معلومات - Info',
            'question' => 'سؤال - Question',
            'lightbulb' => 'مصباح - Lightbulb',
            'fire' => 'نار - Fire',
            'sun' => 'شمس - Sun',
            'moon' => 'قمر - Moon',
            'cloud' => 'سحابة - Cloud',
            'bolt' => 'صاعقة - Bolt',
            'gift' => 'هدية - Gift',
            'shopping-cart' => 'عربة تسوق - Shopping Cart',
            'shopping-bag' => 'حقيبة تسوق - Shopping Bag',
            'credit-card' => 'بطاقة ائتمان - Credit Card',
            'dollar-sign' => 'دولار - Dollar Sign',
            'chart-bar' => 'رسم بياني - Chart Bar',
            'chart-pie' => 'رسم دائري - Chart Pie',
            'arrow-trend-up' => 'اتجاه صاعد - Arrow Trend Up',
            'arrow-trend-down' => 'اتجاه هابط - Arrow Trend Down',
        ];
    }
    
    protected function renderIconOption(string $library, string $iconName, string $color): string
    {
        $iconHtml = '';
        
        if ($library === 'heroicons') {
            $iconHtml = $this->renderHeroicon($iconName, $color);
        } elseif ($library === 'fontawesome') {
            $iconHtml = $this->renderFontAwesome($iconName, $color);
        }
        
        $label = $this->getIconsForLibrary($library)[$iconName] ?? $iconName;
        
        return "<div class='flex items-center gap-3 py-1'><div class='flex-shrink-0'>{$iconHtml}</div><span class='text-sm'>{$label}</span></div>";
    }
    
    protected function renderHeroicon(string $iconName, string $color): string
    {
        $paths = [
            'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a4 4 0 11-8 0 4 4 0 018 0z',
            'book' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            'book-open' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            'academic-cap' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
            'star' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
            'heart' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
            'eye' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
            'search' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
            'plus' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
            'minus' => 'M20 12H4',
            'check' => 'M5 13l4 4L19 7',
            'x' => 'M6 18L18 6M6 6l12 12',
            'arrow-right' => 'M14 5l7 7m0 0l-7 7m7-7H3',
            'arrow-left' => 'M10 19l-7-7m0 0l7-7m-7 7h18',
            'arrow-up' => 'M5 10l7-7m0 0l7 7m-7-7v18',
            'arrow-down' => 'M19 14l-7 7m0 0l-7-7m7 7V3',
            'download' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
            'upload' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
            'folder' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
            'folder-open' => 'M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z',
            'document' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'document-text' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'clipboard' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'mail' => 'M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'phone' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
            'globe' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9',
            'map' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
            'location-marker' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
            'camera' => 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM15 13a3 3 0 11-6 0 3 3 0 016 0z',
            'photograph' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            'film' => 'M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m3 0V2a1 1 0 00-1-1h-2a1 1 0 00-1 1v2m0 0V2a1 1 0 00-1-1H9a1 1 0 00-1 1v2m12 0a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2h16z',
            'music-note' => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3',
            'volume-up' => 'M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M11 5L6 9H2v6h4l5 4V5z',
            'volume-off' => 'M5.586 15H2v-6h3.586l5.707-5.707A1 1 0 0113 4v16a1 1 0 01-1.707.707L5.586 15z M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2',
            'cog' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            'adjustments' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4',
            'key' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
            'lock-closed' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
            'lock-open' => 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z',
            'shield-check' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            'exclamation' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z',
            'information-circle' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'question-mark-circle' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'light-bulb' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
            'fire' => 'M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z',
            'sun' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
            'moon' => 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z',
            'cloud' => 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12',
            'lightning-bolt' => 'M13 10V3L4 14h7v7l9-11h-7z',
            'gift' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
            'shopping-cart' => 'M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8L6 5H3m4 8v6a1 1 0 001 1h1m0-7a1 1 0 011-1h2a1 1 0 011 1v7a1 1 0 01-1 1H9a1 1 0 01-1-1z',
            'shopping-bag' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l-1 12H6L5 9z',
            'credit-card' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H5a3 3 0 00-3 3v8a3 3 0 003 3z',
            'currency-dollar' => 'M12 8c-1.657 0-3 .895-3 2 0 1.105 1.343 2 3 2 1.657 0 3 .895 3 2 0 1.105-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
            'chart-bar' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'chart-pie' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z',
            'trending-up' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
            'trending-down' => 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6',
            'library' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z',
            'collection' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
            'bookmark' => 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z',
            'tag' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
            'flag' => 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 2H21l-3 6 3 6h-8.5l-1-2H5a2 2 0 00-2 2zm9-13.5V9',
            'bell' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
            'chat' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
            'menu' => 'M4 6h16M4 12h16M4 18h16',
            'dots-vertical' => 'M12 5v.01M12 12v.01M12 19v.01',
            'dots-horizontal' => 'M5 12h.01M12 12h.01M19 12h.01',
            'refresh' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
            'save' => 'M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4',
            'edit' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'trash' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
            'duplicate' => 'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z',
            'share' => 'M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z',
            'print' => 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z',
            'external-link' => 'M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14',
            'link' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
            'paperclip' => 'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13',
            'archive' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
            'inbox' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
            'reply' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
            'forward' => 'M13 5l7 7-7 7M5 5l7 7-7 7',
            'filter' => 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z',
            'sort-ascending' => 'M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12',
            'sort-descending' => 'M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4',
            'view-list' => 'M4 6h16M4 10h16M4 14h16M4 18h16',
            'view-grid' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
            'zoom-in' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7',
            'zoom-out' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7',
        ];
        
        $path = $paths[$iconName] ?? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
        
        return "<svg class='w-5 h-5 flex-shrink-0' fill='none' stroke='{$color}' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='{$path}'></path>
                </svg>";
    }
    
    protected function renderFontAwesome(string $iconName, string $color): string
    {
        return "<i class='fas fa-{$iconName} w-5 h-5' style='color: {$color}'></i>";
    }
}