document.addEventListener('DOMContentLoaded', function() {
    // Get the current locale from the session or app config
    const currentLocale = document.documentElement.getAttribute('lang') || 'en';
    
    // Set the direction based on the current locale
    if (currentLocale === 'ar') {
        document.documentElement.setAttribute('dir', 'rtl');
        document.body.classList.add('rtl');
    } else {
        document.documentElement.setAttribute('dir', 'ltr');
        document.body.classList.remove('rtl');
    }
});

// Listen for Livewire navigation events to maintain language settings
document.addEventListener('livewire:navigated', function () {
    const currentLocale = document.documentElement.getAttribute('lang') || 'en';
    
    if (currentLocale === 'ar') {
        document.documentElement.setAttribute('dir', 'rtl');
        document.body.classList.add('rtl');
    } else {
        document.documentElement.setAttribute('dir', 'ltr');
        document.body.classList.remove('rtl');
    }
});
