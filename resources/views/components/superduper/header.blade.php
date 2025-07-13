<header class="nav-bar">
    <div class="nav-container">
        <div class="flex items-center gap-8">
            <!-- Header Logo -->
            <a href="{{ route('home') }}" class="relative z-10 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/figma/header_logo1.jpg') }}" alt="Logo" class="w-auto h-11">
                    <img src="{{ asset('images/figma/header_logo2.jpg') }}" alt="Logo" class="w-auto h-11">
                </div>
            </a>

            <!-- Header Navigation -->
            <div class="nav-links">
                <a href="{{ route('home') }}" class="nav-link active">الرئيسية</a>
                <div class="nav-divider"></div>
                <a href="#" class="nav-link">عن المكتبة</a>
                <div class="nav-divider"></div>
                <a href="#" class="nav-link">الأقسام</a>
                <div class="nav-divider"></div>
                <a href="#" class="nav-link">الكتب</a>
            </div>
        </div>
    </div>
</header>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>
@endpush
