<?php

namespace App\Http\Controllers;

use App\Models\BookSection;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * عرض صفحة أقسام الكتب مع إمكانية البحث
     * 
     * الوظائف:
     * 1. عرض جميع أقسام الكتب النشطة
     * 2. البحث في أسماء الأقسام
     * 3. عرض عدد الكتب لكل قسم
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /**
         * جلب أقسام الكتب مع إمكانية البحث
         * 
         * 1. البداية: جلب جميع الأقسام النشطة مع عدد الكتب
         * 2. إذا وجد نص بحث: تصفية الأقسام حسب الاسم
         * 3. withCount('books'): حساب عدد الكتب لكل قسم لتحسين الأداء
         */
        $query = BookSection::withCount(['books' => function($bookQuery) {
            // عد الكتب المنشورة والمرئية فقط
            $bookQuery->where('status', 'published')
                     ->where('visibility', 'public');
        }])
        ->where('is_active', true)
        ->orderBy('name');
        
        // تطبيق البحث إذا تم إدخال نص بحث
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }
        
        // جلب النتائج
        $sections = $query->get();
        
        return view('components.superduper.pages.categories', compact('sections'));
    }
}
