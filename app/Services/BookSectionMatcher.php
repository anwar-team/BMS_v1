<?php

namespace App\Services;

use App\Models\BookSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BookSectionMatcher
{
    /**
     * استخراج قسم الكتاب من الوصف
     */
    public function extractSection(string $description): ?string
    {
        // إزالة المسافات الزائدة والأسطر الفارغة
        $description = trim($description);
        
        // الأنماط المستخدمة للاستخراج (بالترتيب من الأدق للأعم)
        $patterns = [
            // نمط "القسم:" مع النقطتين العربية أو الإنجليزية
            '/(?:القسم|التصنيف|الموضوع|المجال)\s*[:：]\s*([^\n\r،؛]+)/u',
            
            // نمط "القسم" بدون نقطتين
            '/القسم\s+([^\n\r،؛]+)/u',
            
            // نمط التصنيف
            '/التصنيف\s+([^\n\r،؛]+)/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                $extracted = trim($matches[1]);
                
                // تنظيف النص المستخرج
                $extracted = $this->cleanExtractedText($extracted);
                
                // تجاهل النتائج القصيرة جداً أو الطويلة جداً
                if (mb_strlen($extracted) >= 3 && mb_strlen($extracted) <= 100) {
                    Log::info("Section extracted", [
                        'pattern' => $pattern,
                        'extracted' => $extracted
                    ]);
                    
                    return $extracted;
                }
            }
        }

        return null;
    }

    /**
     * تنظيف النص المستخرج
     */
    protected function cleanExtractedText(string $text): string
    {
        // إزالة علامات الترقيم الزائدة في النهاية
        $text = rtrim($text, '.,;؛،:：- ');
        
        // إزالة الأقواس وما بداخلها في نهاية النص
        $text = preg_replace('/\s*[\(\[].*?[\)\]]\s*$/u', '', $text);
        
        // إزالة المسافات المتعددة
        $text = preg_replace('/\s+/u', ' ', $text);
        
        return trim($text);
    }

    /**
     * مطابقة القسم المستخرج مع قاعدة البيانات
     */
    public function matchSection(string $extractedSection): ?array
    {
        // 1. مطابقة تامة (100% ثقة)
        $exactMatch = $this->exactMatch($extractedSection);
        if ($exactMatch) {
            return [
                'section_id' => $exactMatch->id,
                'section_name' => $exactMatch->name,
                'confidence' => 1.00,
                'match_type' => 'exact'
            ];
        }

        // 2. مطابقة جزئية - يحتوي على (85% ثقة)
        $partialMatch = $this->partialMatch($extractedSection);
        if ($partialMatch) {
            return [
                'section_id' => $partialMatch->id,
                'section_name' => $partialMatch->name,
                'confidence' => 0.85,
                'match_type' => 'partial_contains'
            ];
        }

        // 3. مطابقة عكسية - النص المستخرج يحتوي على اسم القسم (75% ثقة)
        $reverseMatch = $this->reverseMatch($extractedSection);
        if ($reverseMatch) {
            return [
                'section_id' => $reverseMatch->id,
                'section_name' => $reverseMatch->name,
                'confidence' => 0.75,
                'match_type' => 'reverse_contains'
            ];
        }

        // 4. مطابقة تقريبية باستخدام similar_text
        $similarMatch = $this->similarMatch($extractedSection);
        if ($similarMatch) {
            return $similarMatch;
        }

        // 5. لم يتم العثور على مطابقة
        Log::warning("No section match found", [
            'extracted' => $extractedSection
        ]);
        
        return null;
    }

    /**
     * مطابقة تامة
     */
    protected function exactMatch(string $extractedSection): ?BookSection
    {
        return BookSection::where('name', $extractedSection)
            ->where('is_active', true)
            ->first();
    }

    /**
     * مطابقة جزئية - اسم القسم يحتوي على النص المستخرج
     */
    protected function partialMatch(string $extractedSection): ?BookSection
    {
        return BookSection::where('name', 'LIKE', "%{$extractedSection}%")
            ->where('is_active', true)
            ->first();
    }

    /**
     * مطابقة عكسية - النص المستخرج يحتوي على اسم القسم
     */
    protected function reverseMatch(string $extractedSection): ?BookSection
    {
        $sections = BookSection::where('is_active', true)->get();
        
        foreach ($sections as $section) {
            if (mb_stripos($extractedSection, $section->name) !== false) {
                return $section;
            }
        }
        
        return null;
    }

    /**
     * مطابقة تقريبية باستخدام similar_text
     */
    protected function similarMatch(string $extractedSection): ?array
    {
        $sections = BookSection::where('is_active', true)->get();
        
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($sections as $section) {
            similar_text(
                mb_strtolower($extractedSection),
                mb_strtolower($section->name),
                $score
            );
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $section;
            }
        }
        
        // نقبل فقط المطابقات الجيدة (> 60%)
        if ($bestMatch && $bestScore > 60) {
            $confidence = $bestScore / 100;
            
            return [
                'section_id' => $bestMatch->id,
                'section_name' => $bestMatch->name,
                'confidence' => round($confidence, 2),
                'match_type' => 'similar_text',
                'similarity_score' => $bestScore
            ];
        }
        
        return null;
    }

    /**
     * معالجة قسم واحد - استخراج ومطابقة
     */
    public function process(string $description): ?array
    {
        // استخراج القسم
        $extractedSection = $this->extractSection($description);
        
        if (!$extractedSection) {
            return null;
        }

        // محاولة المطابقة
        $matchResult = $this->matchSection($extractedSection);
        
        if ($matchResult) {
            return [
                'extracted_section_name' => $extractedSection,
                'matched_section_id' => $matchResult['section_id'],
                'section_match_confidence' => $matchResult['confidence'],
                'match_details' => $matchResult
            ];
        }

        // لم يتم العثور - نعيد فقط النص المستخرج
        return [
            'extracted_section_name' => $extractedSection,
            'matched_section_id' => null,
            'section_match_confidence' => 0.00,
            'match_details' => [
                'match_type' => 'no_match',
                'needs_manual_review' => true
            ]
        ];
    }

    /**
     * إنشاء قسم جديد (استخدام حذر - يحتاج موافقة admin)
     */
    public function createNewSection(string $sectionName, ?int $parentId = null): BookSection
    {
        // توليد slug فريد
        $slug = \Illuminate\Support\Str::slug($sectionName);
        $originalSlug = $slug;
        $counter = 1;
        
        while (BookSection::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return BookSection::create([
            'name' => $sectionName,
            'slug' => $slug,
            'parent_id' => $parentId,
            'description' => 'قسم تم إنشاؤه تلقائياً من الاستخراج',
            'is_active' => false, // غير نشط حتى يتم المراجعة
            'sort_order' => 999
        ]);
    }

    /**
     * معالجة دفعة من الكتب
     */
    public function processBatch(Collection $books): array
    {
        $results = [
            'processed' => 0,
            'extracted' => 0,
            'matched' => 0,
            'high_confidence' => 0,
            'needs_review' => 0,
            'failed' => 0
        ];

        foreach ($books as $book) {
            try {
                $results['processed']++;
                
                if (!$book->description) {
                    continue;
                }

                $processResult = $this->process($book->description);
                
                if ($processResult) {
                    $results['extracted']++;
                    
                    if ($processResult['matched_section_id']) {
                        $results['matched']++;
                        
                        if ($processResult['section_match_confidence'] >= 0.80) {
                            $results['high_confidence']++;
                        }
                    } else {
                        $results['needs_review']++;
                    }
                }
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error("Failed to process book section", [
                    'book_id' => $book->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }
}
