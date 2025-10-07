@echo off
REM =============================================================================
REM سكريبت تطبيق Template الجديد على Elasticsearch (Windows)
REM =============================================================================

echo ════════════════════════════════════════════════════════
echo    تطبيق Index Template الجديد
echo ════════════════════════════════════════════════════════
echo.

set ELASTICSEARCH_HOST=http://145.223.98.97:9201
set TEMPLATE_NAME=pages_new_search_template
set TEMPLATE_FILE=%~dp0..\elasticsearch\pages_new_search_template.json

REM التحقق من وجود الملف
if not exist "%TEMPLATE_FILE%" (
    echo ❌ خطأ: ملف Template غير موجود: %TEMPLATE_FILE%
    exit /b 1
)

echo 📄 Template File: %TEMPLATE_FILE%
echo 🔗 Elasticsearch: %ELASTICSEARCH_HOST%
echo.

REM حذف Template القديم إن وجد
echo 1️⃣ حذف Template القديم ^(إن وجد^)...
curl -X DELETE "%ELASTICSEARCH_HOST%/_index_template/%TEMPLATE_NAME%" 2>nul
echo.
echo.

REM تطبيق Template الجديد
echo 2️⃣ تطبيق Template الجديد...
curl -X PUT "%ELASTICSEARCH_HOST%/_index_template/%TEMPLATE_NAME%" ^
    -H "Content-Type: application/json" ^
    -d @"%TEMPLATE_FILE%"
echo.
echo.

REM التحقق من Template
echo 3️⃣ التحقق من Template...
curl -X GET "%ELASTICSEARCH_HOST%/_index_template/%TEMPLATE_NAME%"
echo.
echo.

echo ════════════════════════════════════════════════════════
echo    ✅ اكتمل!
echo ════════════════════════════════════════════════════════
echo.
echo الخطوة التالية:
echo   cd logstash-setup
echo   docker-compose up -d
echo.

pause
