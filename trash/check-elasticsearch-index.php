<?php

/**
 * فحص شامل للفهرس الحالي على Elasticsearch
 * يفحص: الإعدادات، المحللات، الـ Mapping، الإحصائيات
 */

require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

echo "══════════════════════════════════════════════════════════\n";
echo "🔍 فحص الفهرس الحالي على Elasticsearch\n";
echo "══════════════════════════════════════════════════════════\n\n";

// 1. فحص الفهارس الموجودة
echo "📋 الفهارس الموجودة:\n";
echo "─────────────────────\n";
try {
    $indices = $client->cat()->indices(['v' => true]);
    echo $indices . "\n\n";
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n\n";
}

// 2. فحص إعدادات الفهرس pages
echo "⚙️  إعدادات الفهرس 'pages':\n";
echo "─────────────────────────────\n";
try {
    $settings = $client->indices()->getSettings(['index' => 'pages']);
    
    if (isset($settings['pages']['settings']['index'])) {
        $indexSettings = $settings['pages']['settings']['index'];
        
        echo "📊 معلومات أساسية:\n";
        echo "   - عدد الـ Shards: " . ($indexSettings['number_of_shards'] ?? 'N/A') . "\n";
        echo "   - عدد الـ Replicas: " . ($indexSettings['number_of_replicas'] ?? 'N/A') . "\n";
        echo "   - تاريخ الإنشاء: " . ($indexSettings['creation_date'] ?? 'N/A') . "\n";
        echo "   - UUID: " . ($indexSettings['uuid'] ?? 'N/A') . "\n";
        echo "   - الإصدار: " . ($indexSettings['version']['created'] ?? 'N/A') . "\n\n";
        
        // فحص المحللات المخصصة
        if (isset($settings['pages']['settings']['index']['analysis'])) {
            echo "🔬 المحللات المخصصة (Custom Analyzers):\n";
            $analysis = $settings['pages']['settings']['index']['analysis'];
            
            if (isset($analysis['analyzer'])) {
                echo "   المحللات (Analyzers):\n";
                foreach ($analysis['analyzer'] as $name => $config) {
                    echo "   ✓ $name\n";
                    echo "      " . json_encode($config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
                }
            } else {
                echo "   ⚠️  لا توجد محللات مخصصة!\n";
            }
            
            if (isset($analysis['filter'])) {
                echo "\n   الفلاتر (Filters):\n";
                foreach ($analysis['filter'] as $name => $config) {
                    echo "   ✓ $name\n";
                }
            }
            
            if (isset($analysis['char_filter'])) {
                echo "\n   محللات الأحرف (Char Filters):\n";
                foreach ($analysis['char_filter'] as $name => $config) {
                    echo "   ✓ $name\n";
                }
            }
        } else {
            echo "⚠️  لا توجد إعدادات تحليل مخصصة!\n";
        }
    }
    
    echo "\n📄 الإعدادات الكاملة (JSON):\n";
    echo json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n\n";
}

// 3. فحص الـ Mapping
echo "🗺️  Mapping الحقول:\n";
echo "──────────────────\n";
try {
    $mapping = $client->indices()->getMapping(['index' => 'pages']);
    
    if (isset($mapping['pages']['mappings']['properties'])) {
        $properties = $mapping['pages']['mappings']['properties'];
        
        echo "الحقول الموجودة:\n";
        foreach ($properties as $field => $config) {
            echo "   ✓ $field\n";
            echo "      النوع: " . ($config['type'] ?? 'N/A') . "\n";
            
            // فحص إذا كان الحقل يحتوي على حقول فرعية
            if (isset($config['fields'])) {
                echo "      الحقول الفرعية (Multi-fields):\n";
                foreach ($config['fields'] as $subfield => $subconfig) {
                    echo "         - $field.$subfield (" . ($subconfig['type'] ?? 'N/A') . ")\n";
                    if (isset($subconfig['analyzer'])) {
                        echo "            المحلل: " . $subconfig['analyzer'] . "\n";
                    }
                }
            }
            
            if (isset($config['analyzer'])) {
                echo "      المحلل: " . $config['analyzer'] . "\n";
            }
        }
    }
    
    echo "\n📄 الـ Mapping الكامل (JSON):\n";
    echo json_encode($mapping, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n\n";
}

// 4. إحصائيات الفهرس
echo "📈 إحصائيات الفهرس:\n";
echo "──────────────────\n";
try {
    $stats = $client->indices()->stats(['index' => 'pages']);
    
    if (isset($stats['indices']['pages'])) {
        $pageStats = $stats['indices']['pages'];
        $primaries = $pageStats['primaries'] ?? [];
        
        echo "   📊 عدد المستندات: " . number_format($primaries['docs']['count'] ?? 0) . "\n";
        echo "   🗑️  المستندات المحذوفة: " . number_format($primaries['docs']['deleted'] ?? 0) . "\n";
        echo "   💾 حجم التخزين: " . ($primaries['store']['size_in_bytes'] ?? 0) / (1024*1024*1024) . " GB\n";
        echo "   🔍 عدد عمليات البحث: " . number_format($primaries['search']['query_total'] ?? 0) . "\n";
        echo "   ⏱️  وقت البحث الكلي: " . ($primaries['search']['query_time_in_millis'] ?? 0) / 1000 . " ثانية\n";
        
        if (($primaries['search']['query_total'] ?? 0) > 0) {
            $avgTime = ($primaries['search']['query_time_in_millis'] ?? 0) / ($primaries['search']['query_total'] ?? 1);
            echo "   📊 متوسط وقت البحث: " . round($avgTime, 2) . " ms\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n\n";
}

// 5. اختبار بحث بسيط
echo "\n🧪 اختبار بحث بسيط:\n";
echo "────────────────────\n";
try {
    $searchResult = $client->search([
        'index' => 'pages',
        'body' => [
            'query' => [
                'match' => [
                    'content' => 'الصلاة'
                ]
            ],
            'size' => 1
        ]
    ]);
    
    $total = $searchResult['hits']['total']['value'] ?? 0;
    echo "   ✓ البحث عن 'الصلاة': وجد " . number_format($total) . " نتيجة\n";
    
    if ($total > 0 && isset($searchResult['hits']['hits'][0])) {
        $firstHit = $searchResult['hits']['hits'][0];
        echo "   📄 أول نتيجة:\n";
        echo "      - ID: " . ($firstHit['_id'] ?? 'N/A') . "\n";
        echo "      - Score: " . ($firstHit['_score'] ?? 'N/A') . "\n";
        if (isset($firstHit['_source']['book_title'])) {
            echo "      - الكتاب: " . $firstHit['_source']['book_title'] . "\n";
        }
        if (isset($firstHit['_source']['page_number'])) {
            echo "      - الصفحة: " . $firstHit['_source']['page_number'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ خطأ في البحث: " . $e->getMessage() . "\n";
}

echo "\n══════════════════════════════════════════════════════════\n";
echo "✅ انتهى الفحص\n";
echo "══════════════════════════════════════════════════════════\n";
