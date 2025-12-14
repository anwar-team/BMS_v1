<?php

namespace App\Services;

use App\Models\Publisher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PublisherExtractor
{
    /**
     * استخراج اسم الناشر من الوصف
     */
    public function extractPublisher(string $description): ?string
    {
        // تنظيف الوصف
        $description = trim($description);
        
        // الأنماط المستخدمة للاستخراج
        $patterns = [
            // نمط "الناشر:" مع النقطتين
            '/الناشر\s*[:：]\s*([^\n\r]+)/u',
            
            // نمط "دار النشر:" أو "المطبعة:"
            '/(?:دار النشر|المطبعة)\s*[:：]\s*([^\n\r]+)/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                $extracted = trim($matches[1]);
                
                // تنظيف النص المستخرج
                $extracted = $this->cleanExtractedPublisher($extracted);
                
                // تجاهل النتائج القصيرة جداً أو الطويلة جداً
                if (mb_strlen($extracted) >= 5 && mb_strlen($extracted) <= 200) {
                    Log::info("Publisher extracted", [
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
     * تنظيف اسم الناشر المستخرج
     */
    protected function cleanExtractedPublisher(string $text): string
    {
        // إزالة المواقع الجغرافية الشائعة
        $locations = [
            '- بيروت',
            'بيروت',
            '- القاهرة',
            'القاهرة',
            '- الرياض',
            'الرياض',
            '- دمشق',
            'دمشق',
            '- جدة',
            'جدة',
            'لبنان',
            'مصر',
            'السعودية',
            'سوريا',
        ];
        
        foreach ($locations as $location) {
            $text = str_ireplace($location, '', $text);
        }
        
        // إزالة عبارات شائعة
        $commonPhrases = [
            '/لبنان/u',
            '/للنشر والتوزيع/u',
            '/للطباعة والنشر/u',
            '/للنشر/u',
            '/والتوزيع/u',
        ];
        
        foreach ($commonPhrases as $phrase) {
            $text = preg_replace($phrase, '', $text);
        }
        
        // إزالة علامات الترقيم الزائدة
        $text = rtrim($text, '.,;؛،:：-/ ');
        $text = ltrim($text, '- ');
        
        // إزالة المسافات المتعددة
        $text = preg_replace('/\s+/u', ' ', $text);
        
        return trim($text);
    }

    /**
     * مطابقة الناشر المستخرج مع قاعدة البيانات
     */
    public function matchPublisher(string $extractedPublisher): ?array
    {
        // 1. مطابقة تامة (100% ثقة)
        $exactMatch = $this->exactMatch($extractedPublisher);
        if ($exactMatch) {
            return [
                'publisher_id' => $exactMatch->id,
                'publisher_name' => $exactMatch->name,
                'confidence' => 1.00,
                'match_type' => 'exact'
            ];
        }

        // 2. مطابقة جزئية - يحتوي على (85% ثقة)
        $partialMatch = $this->partialMatch($extractedPublisher);
        if ($partialMatch) {
            return [
                'publisher_id' => $partialMatch->id,
                'publisher_name' => $partialMatch->name,
                'confidence' => 0.85,
                'match_type' => 'partial_contains'
            ];
        }

        // 3. مطابقة عكسية - النص المستخرج يحتوي على اسم الناشر (75% ثقة)
        $reverseMatch = $this->reverseMatch($extractedPublisher);
        if ($reverseMatch) {
            return [
                'publisher_id' => $reverseMatch->id,
                'publisher_name' => $reverseMatch->name,
                'confidence' => 0.75,
                'match_type' => 'reverse_contains'
            ];
        }

        // 4. مطابقة تقريبية باستخدام similar_text
        $similarMatch = $this->similarMatch($extractedPublisher);
        if ($similarMatch) {
            return $similarMatch;
        }

        // 5. لم يتم العثور على مطابقة
        Log::warning("No publisher match found", [
            'extracted' => $extractedPublisher
        ]);
        
        return null;
    }

    /**
     * مطابقة تامة
     */
    protected function exactMatch(string $extractedPublisher): ?Publisher
    {
        return Publisher::where('name', $extractedPublisher)
            ->where('is_active', true)
            ->first();
    }

    /**
     * مطابقة جزئية - اسم الناشر يحتوي على النص المستخرج
     */
    protected function partialMatch(string $extractedPublisher): ?Publisher
    {
        return Publisher::where('name', 'LIKE', "%{$extractedPublisher}%")
            ->where('is_active', true)
            ->first();
    }

    /**
     * مطابقة عكسية - النص المستخرج يحتوي على اسم الناشر
     */
    protected function reverseMatch(string $extractedPublisher): ?Publisher
    {
        // نبحث في الأسماء الأطول أولاً
        $publishers = Publisher::where('is_active', true)
            ->orderByRaw('LENGTH(name) DESC')
            ->get();
        
        foreach ($publishers as $publisher) {
            if (mb_stripos($extractedPublisher, $publisher->name) !== false) {
                return $publisher;
            }
        }
        
        return null;
    }

    /**
     * مطابقة تقريبية باستخدام similar_text
     */
    protected function similarMatch(string $extractedPublisher): ?array
    {
        $publishers = Publisher::where('is_active', true)->get();
        
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($publishers as $publisher) {
            similar_text(
                mb_strtolower($extractedPublisher),
                mb_strtolower($publisher->name),
                $score
            );
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $publisher;
            }
        }
        
        // نقبل فقط المطابقات الجيدة (> 65% للناشرين)
        if ($bestMatch && $bestScore > 65) {
            $confidence = $bestScore / 100;
            
            return [
                'publisher_id' => $bestMatch->id,
                'publisher_name' => $bestMatch->name,
                'confidence' => round($confidence, 2),
                'match_type' => 'similar_text',
                'similarity_score' => $bestScore
            ];
        }
        
        return null;
    }

    /**
     * معالجة ناشر واحد - استخراج ومطابقة
     */
    public function process(string $description): ?array
    {
        // استخراج الناشر
        $extractedPublisher = $this->extractPublisher($description);
        
        if (!$extractedPublisher) {
            return null;
        }

        // محاولة المطابقة
        $matchResult = $this->matchPublisher($extractedPublisher);
        
        if ($matchResult) {
            return [
                'extracted_publisher_name' => $extractedPublisher,
                'matched_publisher_id' => $matchResult['publisher_id'],
                'publisher_match_confidence' => $matchResult['confidence'],
                'match_details' => $matchResult
            ];
        }

        // لم يتم العثور - نعيد فقط النص المستخرج
        return [
            'extracted_publisher_name' => $extractedPublisher,
            'matched_publisher_id' => null,
            'publisher_match_confidence' => 0.00,
            'match_details' => [
                'match_type' => 'no_match',
                'needs_manual_review' => true,
                'suggestion' => 'create_new_publisher'
            ]
        ];
    }

    /**
     * إنشاء ناشر جديد (استخدام حذر - يحتاج موافقة admin)
     */
    public function createNewPublisher(string $publisherName): Publisher
    {
        return Publisher::create([
            'name' => $publisherName,
            'description' => 'ناشر تم إنشاؤه تلقائياً من الاستخراج',
            'is_active' => false, // غير نشط حتى يتم المراجعة
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
                    
                    if ($processResult['matched_publisher_id']) {
                        $results['matched']++;
                        
                        if ($processResult['publisher_match_confidence'] >= 0.80) {
                            $results['high_confidence']++;
                        }
                    } else {
                        $results['needs_review']++;
                    }
                }
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error("Failed to process book publisher", [
                    'book_id' => $book->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }
}
