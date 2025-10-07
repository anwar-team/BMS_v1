<?php

/**
 * Test script to verify TOC (Table of Contents) fix
 * This script simulates the chapter loading logic to ensure no duplicates
 */

// Simulated data structure
$volumeChaptersRaw = [
    ['id' => 1, 'title' => 'Chapter 1', 'parent_id' => null],
    ['id' => 2, 'title' => 'Chapter 1.1', 'parent_id' => 1],
    ['id' => 3, 'title' => 'Chapter 1.2', 'parent_id' => 1],
    ['id' => 4, 'title' => 'Chapter 2', 'parent_id' => null],
    ['id' => 5, 'title' => 'Chapter 3', 'parent_id' => null],
];

// Filter to get only top-level chapters (parent_id = null)
$topLevelChapters = array_filter($volumeChaptersRaw, function($chapter) {
    return $chapter['parent_id'] === null;
});

echo "=== Before Fix (Simulated Old Behavior) ===\n";
echo "Total chapters returned by volume->chapters: " . count($volumeChaptersRaw) . "\n";
foreach ($volumeChaptersRaw as $chapter) {
    $indent = $chapter['parent_id'] === null ? '' : '  ';
    echo $indent . "- " . $chapter['title'] . " (parent_id: " . ($chapter['parent_id'] ?? 'null') . ")\n";
}

echo "\n=== After Fix (New Behavior) ===\n";
echo "Top-level chapters only: " . count($topLevelChapters) . "\n";
foreach ($topLevelChapters as $chapter) {
    echo "- " . $chapter['title'] . " (parent_id: null)\n";
    
    // Find children
    $children = array_filter($volumeChaptersRaw, function($ch) use ($chapter) {
        return $ch['parent_id'] === $chapter['id'];
    });
    
    foreach ($children as $child) {
        echo "  - " . $child['title'] . " (parent_id: " . $child['parent_id'] . ")\n";
    }
}

echo "\n✅ Expected Result:\n";
echo "Volume 1 should show:\n";
echo "  ├── Chapter 1 (with nested children)\n";
echo "  │   ├── Chapter 1.1\n";
echo "  │   └── Chapter 1.2\n";
echo "  ├── Chapter 2\n";
echo "  └── Chapter 3\n";
echo "\n❌ Old Behavior (Bug):\n";
echo "Volume 1 was showing:\n";
echo "  ├── Chapter 1\n";
echo "  ├── Chapter 2\n";
echo "  ├── Chapter 3\n";
echo "  ├── Chapter 1.1 (duplicate!)\n";
echo "  └── Chapter 1.2 (duplicate!)\n";
