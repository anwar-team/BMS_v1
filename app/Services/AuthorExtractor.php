<?php

namespace App\Services;

use App\Models\Author;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AuthorExtractor
{
    /**
     * استخراج اسم المؤلف من الوصف
     */
    public function extractAuthor(string $description): ?string
    {
        // تنظيف الوصف
        $description = trim($description);
        
        // الأنماط المستخدمة للاستخراج
        $patterns = [
            // نمط "المؤلف:" مع النقطتين
            '/المؤلف\s*[:：]\s*([^\n\r]+)/u',
            
            // نمط "المصنف:" أو "الكاتب:"
            '/(?:المصنف|الكاتب)\s*[:：]\s*([^\n\r]+)/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                $extracted = trim($matches[1]);
                
                // تنظيف النص المستخرج
                $extracted = $this->cleanExtractedAuthor($extracted);
                
                // تجاهل النتائج القصيرة جداً أو الطويلة جداً
                if (mb_strlen($extracted) >= 5 && mb_strlen($extracted) <= 200) {
                    Log::info("Author extracted", [
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
     * تنظيف اسم المؤلف المستخرج
     */
    protected function cleanExtractedAuthor(string $text): string
    {
        // إزالة تواريخ الوفاة (ت ١٢٣ هـ) أو (المتوفى: ١٢٣هـ)
        $text = preg_replace('/\(المتوفى[:：]?\s*[^\)]+\)/u', '', $text);
        $text = preg_replace('/\(ت\s*[^\)]+\)/u', '', $text);
        $text = preg_replace('/\[ت\s*[^\]]+\]/u', '', $text);
        
        // إزالة الألقاب الشائعة في النهاية
        $titles = [
            'رحمه الله',
            'رضي الله عنه',
            'رضي الله عنها',
            'عليه السلام',
            'صلى الله عليه وسلم'
        ];
        
        foreach ($titles as $title) {
            $text = str_replace($title, '', $text);
        }
        
        // إزالة علامات الترقيم الزائدة في النهاية
        $text = rtrim($text, '.,;؛،:：- ');
        
        // إزالة المسافات المتعددة
        $text = preg_replace('/\s+/u', ' ', $text);
        
        return trim($text);
    }

    /**
     * مطابقة المؤلف المستخرج مع قاعدة البيانات
     */
    public function matchAuthor(string $extractedAuthor): ?array
    {
        // 1. مطابقة تامة (100% ثقة)
        $exactMatch = $this->exactMatch($extractedAuthor);
        if ($exactMatch) {
            return [
                'author_id' => $exactMatch->id,
                'author_name' => $exactMatch->full_name,
                'confidence' => 1.00,
                'match_type' => 'exact'
            ];
        }

        // 2. مطابقة جزئية - يحتوي على (85% ثقة)
        $partialMatch = $this->partialMatch($extractedAuthor);
        if ($partialMatch) {
            return [
                'author_id' => $partialMatch->id,
                'author_name' => $partialMatch->full_name,
                'confidence' => 0.85,
                'match_type' => 'partial_contains'
            ];
        }

        // 3. مطابقة عكسية - النص المستخرج يحتوي على اسم المؤلف (75% ثقة)
        $reverseMatch = $this->reverseMatch($extractedAuthor);
        if ($reverseMatch) {
            return [
                'author_id' => $reverseMatch->id,
                'author_name' => $reverseMatch->full_name,
                'confidence' => 0.75,
                'match_type' => 'reverse_contains'
            ];
        }

        // 4. مطابقة تقريبية باستخدام similar_text
        $similarMatch = $this->similarMatch($extractedAuthor);
        if ($similarMatch) {
            return $similarMatch;
        }

        // 5. لم يتم العثور على مطابقة
        Log::warning("No author match found", [
            'extracted' => $extractedAuthor
        ]);
        
        return null;
    }

    /**
     * مطابقة تامة
     */
    protected function exactMatch(string $extractedAuthor): ?Author
    {
        return Author::where('full_name', $extractedAuthor)->first();
    }

    /**
     * مطابقة جزئية - اسم المؤلف يحتوي على النص المستخرج
     */
    protected function partialMatch(string $extractedAuthor): ?Author
    {
        return Author::where('full_name', 'LIKE', "%{$extractedAuthor}%")->first();
    }

    /**
     * مطابقة عكسية - النص المستخرج يحتوي على اسم المؤلف
     */
    protected function reverseMatch(string $extractedAuthor): ?Author
    {
        // نبحث في الأسماء الأقصر أولاً لتجنب المطابقة الخاطئة
        $authors = Author::orderByRaw('LENGTH(full_name) DESC')->get();
        
        foreach ($authors as $author) {
            if (mb_stripos($extractedAuthor, $author->full_name) !== false) {
                return $author;
            }
        }
        
        return null;
    }

    /**
     * مطابقة تقريبية باستخدام similar_text
     */
    protected function similarMatch(string $extractedAuthor): ?array
    {
        $authors = Author::all();
        
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($authors as $author) {
            similar_text(
                mb_strtolower($extractedAuthor),
                mb_strtolower($author->full_name),
                $score
            );
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $author;
            }
        }
        
        // نقبل فقط المطابقات الجيدة (> 70% للمؤلفين)
        if ($bestMatch && $bestScore > 70) {
            $confidence = $bestScore / 100;
            
            return [
                'author_id' => $bestMatch->id,
                'author_name' => $bestMatch->full_name,
                'confidence' => round($confidence, 2),
                'match_type' => 'similar_text',
                'similarity_score' => $bestScore
            ];
        }
        
        return null;
    }

    /**
     * معالجة مؤلف واحد - استخراج ومطابقة
     */
    public function process(string $description): ?array
    {
        // استخراج المؤلف
        $extractedAuthor = $this->extractAuthor($description);
        
        if (!$extractedAuthor) {
            return null;
        }

        // محاولة المطابقة
        $matchResult = $this->matchAuthor($extractedAuthor);
        
        if ($matchResult) {
            return [
                'extracted_author_name' => $extractedAuthor,
                'matched_author_id' => $matchResult['author_id'],
                'author_match_confidence' => $matchResult['confidence'],
                'match_details' => $matchResult
            ];
        }

        // لم يتم العثور - نعيد فقط النص المستخرج
        return [
            'extracted_author_name' => $extractedAuthor,
            'matched_author_id' => null,
            'author_match_confidence' => 0.00,
            'match_details' => [
                'match_type' => 'no_match',
                'needs_manual_review' => true,
                'suggestion' => 'create_new_author'
            ]
        ];
    }

    /**
     * إنشاء مؤلف جديد (استخدام حذر - يحتاج موافقة admin)
     */
    public function createNewAuthor(string $authorName): Author
    {
        return Author::create([
            'full_name' => $authorName,
            'biography' => 'مؤلف تم إنشاؤه تلقائياً من الاستخراج',
            'is_living' => false,
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
                    
                    if ($processResult['matched_author_id']) {
                        $results['matched']++;
                        
                        if ($processResult['author_match_confidence'] >= 0.80) {
                            $results['high_confidence']++;
                        }
                    } else {
                        $results['needs_review']++;
                    }
                }
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error("Failed to process book author", [
                    'book_id' => $book->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }
}
