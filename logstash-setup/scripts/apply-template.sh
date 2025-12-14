#!/bin/bash

# =============================================================================
# سكريبت تطبيق Template الجديد على Elasticsearch
# =============================================================================

echo "════════════════════════════════════════════════════════"
echo "   تطبيق Index Template الجديد"
echo "════════════════════════════════════════════════════════"
echo ""

ELASTICSEARCH_HOST="http://145.223.98.97:9201"
TEMPLATE_NAME="pages_new_search_template"
TEMPLATE_FILE="$(dirname "$0")/../elasticsearch/pages_new_search_template.json"

# التحقق من وجود الملف
if [ ! -f "$TEMPLATE_FILE" ]; then
    echo "❌ خطأ: ملف Template غير موجود: $TEMPLATE_FILE"
    exit 1
fi

echo "📄 Template File: $TEMPLATE_FILE"
echo "🔗 Elasticsearch: $ELASTICSEARCH_HOST"
echo ""

# حذف Template القديم إن وجد
echo "1️⃣ حذف Template القديم (إن وجد)..."
curl -X DELETE "$ELASTICSEARCH_HOST/_index_template/$TEMPLATE_NAME" 2>/dev/null
echo ""
echo ""

# تطبيق Template الجديد
echo "2️⃣ تطبيق Template الجديد..."
RESPONSE=$(curl -X PUT "$ELASTICSEARCH_HOST/_index_template/$TEMPLATE_NAME" \
    -H 'Content-Type: application/json' \
    -d @"$TEMPLATE_FILE" \
    2>/dev/null)

echo "$RESPONSE"
echo ""

# التحقق من النجاح
if echo "$RESPONSE" | grep -q '"acknowledged":true'; then
    echo "✅ تم تطبيق Template بنجاح!"
else
    echo "❌ فشل تطبيق Template!"
    exit 1
fi

echo ""

# عرض معلومات Template
echo "3️⃣ التحقق من Template..."
curl -X GET "$ELASTICSEARCH_HOST/_index_template/$TEMPLATE_NAME" 2>/dev/null | jq '.index_templates[0].index_template | {index_patterns, settings: .template.settings.analysis.analyzer | keys}'
echo ""

echo ""
echo "════════════════════════════════════════════════════════"
echo "   ✅ اكتمل بنجاح!"
echo "════════════════════════════════════════════════════════"
echo ""
echo "الخطوة التالية:"
echo "  cd logstash-setup"
echo "  docker-compose up -d"
echo ""
