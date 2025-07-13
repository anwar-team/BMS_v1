<div class="hero-section">
    <img src="{{ asset('images/figma/hero_background.jpg') }}" alt="Hero Background" class="hero-background">
    <div class="hero-overlay"></div>
    
    <div class="container relative z-10 px-6 mx-auto hero-content">
        <div class="mb-6">
            <h1 class="hero-title">مكتبة تكاملت موضوعاتها<br> و كتبها</h1>
            <p class="hero-description">اكتشف آلاف الكتب في الحديث، الفقه، الأدب، البلاغة، و التاريخ و الأنساب و غيرها الكثير متاحة لك في مكان واحد</p>
        </div>
        
        <div class="search-bar">
            <div class="send-icon">
                <img src="{{ asset('images/figma/send_icon.svg') }}" alt="Send">
            </div>
            <div class="flex items-center flex-1">
                <input type="text" placeholder="إبحث في محتوى الكتب ..." class="search-input">
                <div class="search-icon">
                    <img src="{{ asset('images/figma/search_icon.svg') }}" alt="Search">
                </div>
            </div>
        </div>
        
        <div class="tab-buttons">
            <button class="tab-button inactive">المؤلفين</button>
            <button class="tab-button active">محتوى الكتب</button>
            <button class="tab-button inactive">عناوين الكتب</button>
        </div>
    </div>
</div>
