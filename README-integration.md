# Auto-HTML Integration Guide

## Overview
This guide documents the integration of the Auto-HTML design bundle into the Laravel BMS project. The integration provides a pixel-perfect implementation of the Figma design with full RTL Arabic support and "Tajawal" font family.

## Asset Locations

### CSS Files
- `resources/css/auto-html/style.css` - Main Auto-HTML styles
- `resources/css/auto-html/vars.css` - CSS variables from Figma
- `resources/css/base.css` - Global RTL styles and font definitions
- `resources/css/tables.scss` - Filament table styling to match theme

### Assets
- `public/assets/auto-html/` - All SVG icons, images, and patterns
  - Pattern SVG files for background decorations
  - Navigation icons (send, search, arrows)
  - Category icons (group*.svg)
  - Mask groups for visual elements
  - Logo images (untitled-design-*.png)
  - Hero background image

### Views
- `resources/views/components/superduper/pages/home.blade.php` - Main home page
- `resources/views/components/superduper/header.blade.php` - Updated header with Auto-HTML nav
- `resources/views/components/superduper/footer.blade.php` - Updated footer with Auto-HTML design
- `resources/views/components/layouts/app.blade.php` - Main layout with CSS imports

### Controllers
- `app/Http/Controllers/HomeController.php` - Handles dynamic data for home page

## Build Commands

### Development
```bash
npm run dev
```

### Production Build
```bash
npm run build
```

### CSS Compilation
The following CSS files are automatically compiled via Vite:
- `resources/css/app.css`
- `resources/css/base.css`
- `resources/css/auto-html/style.css`
- `resources/css/auto-html/vars.css`
- `resources/css/tables.scss`

## Font Configuration

### Tajawal Font Family
The integration uses the Tajawal font family which should be available in:
- `public/superduper/fonts/tajawal/Tajawal-Regular.ttf`
- `public/superduper/fonts/tajawal/Tajawal-Bold.ttf`
- `public/superduper/fonts/tajawal/Tajawal-Medium.ttf`

### Additional Arabic Support
- `public/superduper/fonts/NotoSansArabic-Regular.ttf`

## RTL Support

### Global RTL Configuration
- All pages inherit `direction: rtl` from `base.css`
- Text alignment is set to `right` by default
- Layout components are designed for right-to-left reading

### Override for LTR Content
Use the `.ltr` class or `dir="ltr"` attribute for content that should be left-to-right.

## Dynamic Data Integration

### Home Page Data
The home page controller (`HomeController`) provides:
- `$bookCounts` - Statistics for each book category
- `$books` - Recent books with author information
- `$authors` - Featured authors with their books

### Template Variables
```php
// Book category counts
$bookCounts = [
    'aqeedah' => 1035,
    'fiqh' => 1194,
    'quran' => 1386,
    'islamic' => 1412,
    'adhkar' => 123,
    'research' => 3126,
];

// Recent books
$books = collect([...]);

// Featured authors
$authors = collect([...]);
```

## Extending Pages

### Creating New Pages
1. Create a new Blade component in `resources/views/components/superduper/pages/`
2. Use the main layout: `<x-layouts.app>`
3. Include header and footer: `<x-superduper.header />` and `<x-superduper.footer />`
4. Apply Auto-HTML CSS classes for consistent styling

### Example Page Structure
```blade
<x-layouts.app>
    <x-superduper.header />
    
    <div class="page-content">
        <!-- Your content here -->
        <!-- Use Auto-HTML CSS classes for styling -->
    </div>
    
    <x-superduper.footer />
</x-layouts.app>
```

### Adding New CSS
1. Create new CSS files in `resources/css/`
2. Add to `vite.config.js` input array
3. Import in the main layout or specific pages

## Table Styling

### Filament Tables
Tables automatically inherit the Auto-HTML theme through `tables.scss`:
- RTL layout and text alignment
- Tajawal font family
- Theme colors (#2c6e4a primary, #f1f8f3 header background)
- Consistent padding and spacing

### Custom Table Styling
Override specific table styles by targeting Filament CSS classes:
```scss
.fi-ta-table {
    // Custom table styles
}
```

## Responsive Design

### Breakpoints
- Mobile: ≤ 375px
- Desktop: ≥ 1440px
- Tablet: 376px - 1439px

### Background Patterns
Background patterns scale responsively:
- Mobile: 94px × 129px
- Desktop: 250px × 345px
- Default: 188px × 259px

## Background Configuration

### Global Background
The site-wide background is configured in `base.css`:
```css
body {
    background-image: url('/assets/auto-html/pattern-ff-18-e-023-20.svg');
    background-repeat: repeat;
    background-attachment: fixed;
    background-size: 188px 259px;
}
```

### Customizing Background
To change the background pattern:
1. Replace the SVG file in `public/assets/auto-html/`
2. Update the `background-image` URL in `base.css`
3. Adjust `background-size` as needed

## Performance Optimization

### Asset Optimization
- SVG files are optimized for web delivery
- CSS is minified in production builds
- Images use appropriate compression

### Caching
- Assets are versioned through Vite for browser caching
- CSS and JS files include content hashes in production

## Troubleshooting

### Common Issues
1. **Missing Assets**: Ensure all files from `autohtml-project/` are copied to `public/assets/auto-html/`
2. **Font Loading**: Check that Tajawal font files exist in `public/superduper/fonts/tajawal/`
3. **RTL Issues**: Verify `direction: rtl` is applied to parent containers
4. **CSS Not Loading**: Run `npm run build` to compile assets

### Development Tips
- Use browser DevTools to inspect CSS class applications
- Check console for missing asset errors
- Verify Vite compilation completes successfully
- Use `npm run dev` for hot reloading during development

## File Structure Summary
```
├── public/assets/auto-html/          # All design assets
├── resources/css/auto-html/          # Auto-HTML CSS files
├── resources/css/base.css            # Global RTL styles
├── resources/css/tables.scss         # Filament table styles
├── resources/views/components/
│   └── superduper/
│       ├── header.blade.php          # Auto-HTML header
│       ├── footer.blade.php          # Auto-HTML footer
│       └── pages/home.blade.php      # Auto-HTML home page
├── app/Http/Controllers/HomeController.php  # Home page controller
└── vite.config.js                   # Asset compilation config
```

## Maintenance

### Updating Styles
1. Modify CSS files in `resources/css/`
2. Run `npm run build` to compile
3. Test across different screen sizes
4. Verify RTL layout remains intact

### Adding New Components
1. Follow existing component structure
2. Use Auto-HTML CSS classes for consistency
3. Ensure RTL compatibility
4. Test with dynamic data

This integration provides a solid foundation for the BMS project with pixel-perfect design implementation and full Arabic RTL support.