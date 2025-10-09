<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Volume;
use App\Models\Chapter;

echo "=== Testing Volume Chapters Accessor ===\n\n";

// Find a volume with chapters
$volume = Volume::whereHas('chapters')->first();

if (!$volume) {
    echo "❌ No volumes with chapters found in database.\n";
    exit;
}

echo "Testing Volume: {$volume->full_title}\n";
echo "Volume ID: {$volume->id}\n\n";

// Get all chapters (without filter)
$allChapters = Chapter::where('volume_id', $volume->id)->get();
echo "Total chapters in database for this volume: " . $allChapters->count() . "\n";
echo "Breakdown:\n";
echo "  - Top-level (parent_id = null): " . $allChapters->where('parent_id', null)->count() . "\n";
echo "  - Sub-chapters (parent_id != null): " . $allChapters->whereNotNull('parent_id')->count() . "\n\n";

// Test the new accessor
echo "=== Testing \$volume->chapters (with new accessor) ===\n";
$volume = Volume::with('topLevelChapters.children')->find($volume->id);
$chaptersViaAccessor = $volume->chapters;

echo "Chapters returned by accessor: " . $chaptersViaAccessor->count() . "\n";
echo "Expected: Only top-level chapters\n\n";

if ($chaptersViaAccessor->count() > 0) {
    echo "Chapters list:\n";
    foreach ($chaptersViaAccessor as $chapter) {
        echo "  - {$chapter->title} (ID: {$chapter->id}, parent_id: " . ($chapter->parent_id ?? 'null') . ")\n";
        
        if ($chapter->children && $chapter->children->count() > 0) {
            foreach ($chapter->children as $child) {
                echo "    - {$child->title} (ID: {$child->id}, parent_id: {$child->parent_id})\n";
            }
        }
    }
}

echo "\n✅ If all chapters show parent_id = null, the fix is working!\n";
echo "❌ If you see parent_id with numbers, there's still an issue.\n";
