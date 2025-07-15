<x-superduper.main>
    <div class="page-wrapper relative z-[1]">
        <main class="relative overflow-hidden main-wrapper">
            <x-superduper.components.hero />
            
            <!-- Categories Section -->
            <section class="categories-section">
                <div class="container mx-auto px-4">
                    <div class="flex items-center justify-between mb-8">
                        <div class="section-title">
                            <span>أقسام الكتب</span>
                            <div class="ornament-icon">
                                <img src="{{ asset('public/images/figma/group1.svg') }}" alt="Ornament">
                            </div>
                        </div>
                    </div>
                    
                    <div class="category-grid">
                        <!-- Category 1 -->
                        <div class="category-card" style="background: linear-gradient(135deg, #FFFFFF, #FCF6F4);">
                            <div class="category-card-content">
                                <div class="category-info">
                                    <h3 class="category-name">العقيدة</h3>
                                    <p class="category-count">1035 كتاب</p>
                                </div>
                                <div class="category-icon">
                                    <img src="{{ asset('public/images/figma/group1.svg') }}" alt="العقيدة">
                                </div>
                            </div>
                            <div class="category-decoration">
                                <div style="background-color: rgba(0, 0, 0, 0.8); width: 100%; height: 100%; opacity: 0.8; border-radius: 10px;"></div>
                                <div style="background-color: #BA4749; width: 100%; height: 100%; position: absolute; bottom: -1px; left: -1px;"></div>
                            </div>
                        </div>
                        
                        <!-- Category 2 -->
                        <div class="category-card" style="background: linear-gradient(135deg, #FFFFFF, #FCF6F4);">
                            <div class="category-card-content">
                                <div class="category-info">
                                    <h3 class="category-name">فقه عام</h3>
                                    <p class="category-count">1194 كتاب</p>
                                </div>
                                <div class="category-icon">
                                    <img src="{{ asset('images/figma/fiqh_icon1.svg') }}" alt="فقه عام">
                                </div>
                            </div>
                            <div class="category-decoration">
                                <div style="background-color: rgba(0, 0, 0, 0.8); width: 100%; height: 100%; opacity: 0.8; border-radius: 10px;"></div>
                                <div style="background-color: #BA4749; width: 100%; height: 100%; position: absolute; bottom: -1px; left: -1px;"></div>
                            </div>
                        </div>
                        
                        <!-- Category 3 -->
                        <div class="category-card" style="background: linear-gradient(135deg, #FFFFFF, #FCF6F4);">
                            <div class="category-card-content">
                                <div class="category-info">
                                    <h3 class="category-name">علوم القرآن</h3>
                                    <p class="category-count">1386 كتاب</p>
                                </div>
                                <div class="category-icon">
                                    <img src="{{ asset('images/figma/quran_icon1.svg') }}" alt="علوم القرآن">
                                </div>
                            </div>
                            <div class="category-decoration">
                                <div style="background-color: rgba(0, 0, 0, 0.8); width: 100%; height: 100%; opacity: 0.8; border-radius: 10px;"></div>
                                <div style="background-color: #BA4749; width: 100%; height: 100%; position: absolute; bottom: -1px; left: -1px;"></div>
                            </div>
                        </div>
                        
                        <!-- Category 4 -->
                        <div class="category-card" style="background: linear-gradient(135deg, #FFFFFF, #FCF6F4);">
                            <div class="category-card-content">
                                <div class="category-info">
                                    <h3 class="category-name">كتب إسلامية عامة</h3>
                                    <p class="category-count">1412 كتاب</p>
                                </div>
                                <div class="category-icon">
                                    <img src="{{ asset('images/figma/islamic_books_icon.svg') }}" alt="كتب إسلامية عامة">
                                </div>
                            </div>
                            <div class="category-decoration">
                                <div style="background-color: rgba(0, 0, 0, 0.8); width: 100%; height: 100%; opacity: 0.8; border-radius: 10px;"></div>
                                <div style="background-color: #BA4749; width: 100%; height: 100%; position: absolute; bottom: -1px; left: -1px;"></div>
                            </div>
                        </div>
                        
                        <!-- Category 5 -->
                        <div class="category-card" style="background: linear-gradient(135deg, #FCF6F4, #FFFFFF);">
                            <div class="category-card-content">
                                <div class="category-info">
                                    <h3 class="category-name">الأذكار والأوراد والأدعية</h3>
                                    <p class="category-count">123 كتاب</p>
                                </div>
                                <div class="category-icon">
                                    <img src="{{ asset('images/figma/athkar_icon.svg') }}" alt="الأذكار والأوراد والأدعية">
                                </div>
                            </div>
                            <div class="category-decoration">
                                <div style="background-color: rgba(0, 0, 0, 0.8); width: 100%; height: 100%; opacity: 0.8; border-radius: 10px;"></div>
                                <div style="background-color: #BA4749; width: 100%; height: 100%; position: absolute; bottom: -1px; left: -1px;"></div>
                            </div>
                        </div>
                        
                        <!-- Category 6 -->
                        <div class="category-card" style="background: linear-gradient(135deg, #FCF6F4, #FFFFFF);">
                            <div class="category-card-content">
                                <div class="category-info">
                                    <h3 class="category-name">بحوث ومسائل فقهية</h3>
                                    <p class="category-count">3126 كتاب</p>
                                </div>
                                <div class="category-icon">
                                    <img src="{{ asset('images/figma/fiqh_research_icon1.svg') }}" alt="بحوث ومسائل فقهية">
                                </div>
                            </div>
                            <div class="category-decoration">
                                <div style="background-color: rgba(0, 0, 0, 0.8); width: 100%; height: 100%; opacity: 0.8; border-radius: 10px;"></div>
                                <div style="background-color: #BA4749; width: 100%; height: 100%; position: absolute; bottom: -1px; left: -1px;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="view-all-btn">
                        <span>عرض جميع الأقسام</span>
                        <img src="{{ asset('images/figma/arrow_circle_left.svg') }}" alt="Arrow" width="24" height="24">
                    </div>
                </div>
            </section>
            
            <!-- Books Section -->
            <section class="books-section">
                <div class="container mx-auto px-4">
                    <div class="flex items-center justify-between mb-8">
                        <div class="section-title">
                            <span>الكتب</span>
                            <div class="ornament-icon">
                                <img src="{{ asset('public/images/figma/group1.svg') }}" alt="Ornament">
                            </div>
                        </div>
                    </div>
                    
                    <div class="books-tabs">
                        <button class="books-tab inactive">الكتب المفتوحة مؤخراً</button>
                        <button class="books-tab inactive">أكثر الكتب قراءةً</button>
                        <button class="books-tab inactive">كتب مضافة حديثاً</button>
                        <button class="books-tab active">جميع الكتب</button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="books-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المؤلف</th>
                                    <th>اسم الكتاب</th>
                                    <th>التصنيف</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="table-footer">
                        <div class="pagination-controls">
                            <img src="{{ asset('images/figma/chevron_left.svg') }}" alt="Previous" width="24" height="24">
                            <img src="{{ asset('images/figma/chevron_right.svg') }}" alt="Next" width="24" height="24">
                        </div>
                        <div class="pagination-info">5-1 من 100</div>
                        <div class="rows-per-page">
                            <span>عدد الصفوف في الصفحة:</span>
                            <div class="flex items-center">
                                <span>10</span>
                                <img src="{{ asset('images/figma/arrow_dropdown.svg') }}" alt="Dropdown" width="24" height="24">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Authors Section -->
            <section class="authors-section">
                <div class="container mx-auto px-4">
                    <div class="flex items-center justify-between mb-8">
                        <div class="section-title">
                            <span>المؤلفين</span>
                            <div class="ornament-icon">
                                <img src="{{ asset('public/images/figma/group1.svg') }}" alt="Ornament">
                            </div>
                        </div>
                    </div>
                    
                    <div class="books-tabs">
                        <button class="books-tab inactive">أكثر المؤلفين قراءةً</button>
                        <button class="books-tab inactive">مؤلفين جدد</button>
                        <button class="books-tab active">جميع المؤلفين</button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="books-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المؤلف</th>
                                    <th>اسم الكتاب</th>
                                    <th>التصنيف</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>عبد الله عزام</td>
                                    <td>أذكار الصباح والمساء</td>
                                    <td>الأذكار والأوراد والأدعية</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="table-footer">
                        <div class="pagination-controls">
                            <img src="{{ asset('images/figma/chevron_left.svg') }}" alt="Previous" width="24" height="24">
                            <img src="{{ asset('images/figma/chevron_right.svg') }}" alt="Next" width="24" height="24">
                        </div>
                        <div class="pagination-info">5-1 من 100</div>
                        <div class="rows-per-page">
                            <span>عدد الصفوف في الصفحة:</span>
                            <div class="flex items-center">
                                <span>10</span>
                                <img src="{{ asset('images/figma/arrow_dropdown.svg') }}" alt="Dropdown" width="24" height="24">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</x-superduper.main>
