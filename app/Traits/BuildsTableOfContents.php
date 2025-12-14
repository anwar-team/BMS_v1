<?php

namespace App\Traits;

use App\Models\Chapter;
use App\Models\Volume;
use Illuminate\Database\Eloquent\Collection;

/**
 * Trait لتوحيد منطق بناء الفهرس بدون تكرار
 * يستخدم في BookReadController و BookReader Livewire Component
 */
trait BuildsTableOfContents
{
    /**
     * بناء الفهرس الشجري للكتاب بدون تكرار
     * 
     * @param int $bookId
     * @return array
     */
    protected function buildUniqueTableOfContents(int $bookId): array
    {
        // جلب الأجزاء
        $volumes = Volume::where('book_id', $bookId)
            ->orderBy('number')
            ->get();

        // إذا لم توجد أجزاء، جلب الفصول مباشرة
        if ($volumes->isEmpty()) {
            $chapters = $this->getUniqueRootChapters($bookId, null);

            return [
                'type' => 'chapters_only',
                'data' => $chapters
            ];
        }

        // جلب الفصول لكل جزء
        foreach ($volumes as $volume) {
            $volume->uniqueChapters = $this->getUniqueRootChapters($bookId, $volume->id);
        }

        return [
            'type' => 'volumes_with_chapters',
            'data' => $volumes
        ];
    }

    /**
     * جلب الفصول الرئيسية الفريدة (parent_id = NULL)
     * 
     * @param int $bookId
     * @param int|null $volumeId
     * @return Collection
     */
    protected function getUniqueRootChapters(int $bookId, ?int $volumeId): Collection
    {
        $query = Chapter::where('book_id', $bookId)
            ->whereNull('parent_id');
        
        // فلترة حسب volume_id إذا كان موجودًا
        if ($volumeId !== null) {
            $query->where('volume_id', $volumeId);
        }
        
        $chapters = $query->orderBy('order')->get();
        
        // إزالة التكرار الكامل: parent_id, order, title, page_start, page_end
        $uniqueChapters = $this->removeDuplicateChapters($chapters);
        
        // إضافة معلومات إضافية لكل فصل
        foreach ($uniqueChapters as $chapter) {
            // جلب الفصول الفرعية بشكل متكرر
            $chapter->uniqueChildren = $this->getUniqueChildChapters($chapter->id);
            
            // تحديد إذا كان الفصل له نفس الصفحة مع فصول أخرى
            $chapter->hasSiblingsSamePage = $this->checkSiblingsSamePage(
                $chapter, 
                $chapters
            );
            
            // عدد الفصول التي لها نفس الصفحة
            if ($chapter->hasSiblingsSamePage) {
                $chapter->samePageCount = $this->countSamePageSiblings(
                    $chapter,
                    $chapters
                );
            }
        }
        
        return $uniqueChapters;
    }

    /**
     * جلب الفصول الفرعية الفريدة بشكل متكرر
     * 
     * @param int $parentId
     * @return Collection
     */
    protected function getUniqueChildChapters(int $parentId): Collection
    {
        $children = Chapter::where('parent_id', $parentId)
            ->orderBy('order')
            ->get();
        
        if ($children->isEmpty()) {
            return $children;
        }
        
        // إزالة التكرار
        $uniqueChildren = $this->removeDuplicateChapters($children);
        
        // جلب الفصول الفرعية بشكل متكرر
        foreach ($uniqueChildren as $child) {
            $child->uniqueChildren = $this->getUniqueChildChapters($child->id);
            
            // تحديد إذا كان الفصل له نفس الصفحة مع فصول أخرى
            $child->hasSiblingsSamePage = $this->checkSiblingsSamePage(
                $child,
                $children
            );
            
            if ($child->hasSiblingsSamePage) {
                $child->samePageCount = $this->countSamePageSiblings(
                    $child,
                    $children
                );
            }
        }
        
        return $uniqueChildren;
    }

    /**
     * إزالة الفصول المكررة
     * استراتيجية: الاحتفاظ بأول فصل فريد بناءً على مجموعة من الحقول
     * 
     * @param Collection $chapters
     * @return Collection
     */
    protected function removeDuplicateChapters(Collection $chapters): Collection
    {
        return $chapters->unique(function ($chapter) {
            // مفتاح فريد يجمع عدة حقول لضمان التفرد الكامل
            return implode('|', [
                $chapter->parent_id ?? 'null',
                $chapter->volume_id ?? 'null',
                $chapter->order ?? 'null',
                trim($chapter->title),
                $chapter->page_start ?? 'null',
                $chapter->page_end ?? 'null',
            ]);
        })->values(); // إعادة فهرسة المجموعة
    }

    /**
     * التحقق إذا كان الفصل له فصول أخرى بنفس الصفحة (page_start و page_end)
     * 
     * @param Chapter $chapter
     * @param Collection $siblings
     * @return bool
     */
    protected function checkSiblingsSamePage(Chapter $chapter, Collection $siblings): bool
    {
        if (!$chapter->page_start || !$chapter->page_end) {
            return false;
        }
        
        $samePageCount = $siblings->filter(function ($sibling) use ($chapter) {
            return $sibling->id !== $chapter->id
                && $sibling->page_start === $chapter->page_start
                && $sibling->page_end === $chapter->page_end
                && $sibling->parent_id === $chapter->parent_id;
        })->count();
        
        return $samePageCount > 0;
    }

    /**
     * عد الفصول التي لها نفس الصفحة
     * 
     * @param Chapter $chapter
     * @param Collection $siblings
     * @return int
     */
    protected function countSamePageSiblings(Chapter $chapter, Collection $siblings): int
    {
        if (!$chapter->page_start || !$chapter->page_end) {
            return 1;
        }
        
        return $siblings->filter(function ($sibling) use ($chapter) {
            return $sibling->page_start === $chapter->page_start
                && $sibling->page_end === $chapter->page_end
                && $sibling->parent_id === $chapter->parent_id;
        })->count();
    }

    /**
     * تنظيف الكاش الخاص بالفهرس
     * 
     * @param int $bookId
     * @return void
     */
    protected function clearTableOfContentsCache(int $bookId): void
    {
        \Cache::forget("book_toc_{$bookId}");
    }
}
