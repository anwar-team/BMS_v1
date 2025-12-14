<?php

namespace App\Services;

use App\Models\BookSection;
use Illuminate\Support\Str;

class BookSectionClassifier
{
    protected $sections;
    protected $keywords = [];

    public function __construct()
    {
        $this->sections = BookSection::all();
        $this->initializeKeywords();
    }

    /**
     * تصنيف الكتاب إلى القسم المناسب
     */
    public function classify($book)
    {
        $text = $this->prepareText($book);
        
        $scores = [];
        
        foreach ($this->sections as $section) {
            $score = $this->calculateScore($text, $section);
            
            if ($score > 0) {
                $scores[$section->id] = [
                    'section' => $section,
                    'score' => $score,
                    'confidence' => min(100, round($score * 10, 2))
                ];
            }
        }
        
        // ترتيب حسب النقاط
        uasort($scores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        if (empty($scores)) {
            return null;
        }
        
        $best = reset($scores);
        
        // تحويل النسبة المئوية إلى قيمة عشرية (0.00 - 1.00)
        $confidenceDecimal = min(1.00, round($best['score'] / 10, 2));
        
        return [
            'section_id' => $best['section']->id,
            'section_name' => $best['section']->name,
            'confidence' => $confidenceDecimal,  // قيمة من 0.00 إلى 1.00
            'score' => $best['score']
        ];
    }

    /**
     * تحضير النص للتحليل
     */
    protected function prepareText($book)
    {
        $parts = [
            $book->name ?? '',
            $book->description ?? ''
        ];
        
        return mb_strtolower(implode(' ', $parts));
    }

    /**
     * حساب درجة التطابق مع القسم
     */
    protected function calculateScore($text, $section)
    {
        $sectionName = mb_strtolower($section->name);
        $score = 0;
        
        // الكلمات المفتاحية للقسم
        if (isset($this->keywords[$section->id])) {
            foreach ($this->keywords[$section->id] as $keyword => $weight) {
                $keyword = mb_strtolower($keyword);
                
                // عدد مرات الظهور
                $count = mb_substr_count($text, $keyword);
                
                if ($count > 0) {
                    $score += $count * $weight;
                }
            }
        }
        
        // اسم القسم نفسه
        if (mb_strpos($text, $sectionName) !== false) {
            $score += 5;
        }
        
        return $score;
    }

    /**
     * تعريف الكلمات المفتاحية لكل قسم
     */
    protected function initializeKeywords()
    {
        // تعريف الكلمات المفتاحية يدوياً لكل قسم
        $keywordMap = [
            'التفسير' => ['تفسير', 'القرآن', 'السور', 'الآيات', 'المفسر', 'التأويل', 'سورة'],
            'الحديث' => ['حديث', 'الرسول', 'النبي', 'صحيح', 'السنة', 'المسند', 'الرواية', 'صلى الله عليه وسلم'],
            'الفقه' => ['فقه', 'أحكام', 'مسائل', 'الفقهاء', 'الحلال', 'الحرام', 'الطهارة', 'الصلاة', 'الزكاة', 'الحج'],
            'الحنفي' => ['حنفي', 'أبو حنيفة', 'الهداية', 'البناية'],
            'الشافعي' => ['شافعي', 'الشافعي', 'المنهاج', 'الأم'],
            'الحنبلي' => ['حنبلي', 'أحمد', 'المقنع', 'الكافي'],
            'المالكي' => ['مالكي', 'مالك', 'الموطأ', 'المدونة'],
            'العقيدة' => ['عقيدة', 'التوحيد', 'الإيمان', 'الصفات', 'الأسماء', 'الإلهية', 'العقائد'],
            'التاريخ' => ['تاريخ', 'السيرة', 'الغزوات', 'الخلفاء', 'الدول', 'العصر', 'التراجم'],
            'اللغة' => ['لغة', 'نحو', 'صرف', 'إعراب', 'المعجم', 'القواعد', 'العربية', 'النحو'],
            'الأدب' => ['أدب', 'شعر', 'نثر', 'ديوان', 'الشعراء', 'القصائد', 'الأدبية'],
            'أصول' => ['أصول', 'الأصول', 'القياس', 'الإجماع', 'الاجتهاد'],
            'الخراج' => ['خراج', 'الجزية', 'الأموال', 'الضرائب', 'المال'],
            'الأنساب' => ['نسب', 'أنساب', 'القبائل', 'الأصول', 'العشائر'],
            'الرقائق' => ['رقائق', 'وعظ', 'زهد', 'آداب', 'أخلاق', 'الذكر'],
            'القراءات' => ['قراءة', 'قراءات', 'التجويد', 'القراء'],
            'الطب' => ['طب', 'طبي', 'الأمراض', 'العلاج', 'الدواء'],
        ];
        
        foreach ($this->sections as $section) {
            $sectionName = mb_strtolower($section->name);
            $this->keywords[$section->id] = [];
            
            // البحث عن كلمات مفتاحية مناسبة
            foreach ($keywordMap as $key => $keywords) {
                if (mb_stripos($sectionName, mb_strtolower($key)) !== false) {
                    foreach ($keywords as $keyword) {
                        $this->keywords[$section->id][$keyword] = 3;
                    }
                }
            }
            
            // تحليل اسم القسم وإضافة كلمات منه
            $words = preg_split('/[\s\-]+/', $sectionName);
            foreach ($words as $word) {
                $word = trim($word);
                if (mb_strlen($word) > 3 && !in_array($word, ['كتب', 'علوم', 'شروح', 'متون'])) {
                    $this->keywords[$section->id][$word] = 2;
                }
            }
            
            // إضافة اسم القسم كاملاً بوزن عالي
            if (!isset($this->keywords[$section->id][$sectionName])) {
                $this->keywords[$section->id][$sectionName] = 5;
            }
        }
    }

    /**
     * الحصول على جميع التصنيفات المحتملة
     */
    public function getAllMatches($book, $minConfidence = 30)
    {
        $result = $this->classify($book);
        
        if (!$result || $result['confidence'] < $minConfidence) {
            return [];
        }
        
        return [$result];
    }
}
