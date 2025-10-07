FROM docker.elastic.co/logstash/logstash:7.17.13

# إعداد المتغيرات للنصوص العربية
ENV XPACK_MONITORING_ENABLED=false
ENV LANG=en_US.UTF-8
ENV LC_ALL=en_US.UTF-8

# التبديل إلى root لإجراء التغييرات
USER root

# إنشاء المجلدات المطلوبة
RUN mkdir -p /usr/share/logstash/data && \
    mkdir -p /usr/share/logstash/logs

# تحديث النظام وتثبيت الأدوات المطلوبة
RUN apt-get update && \
    apt-get install -y default-mysql-client curl && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# نسخ ملف MySQL Connector
COPY drivers/mysql-connector-j-8.1.0.jar /usr/share/logstash/mysql-connector.jar

# إعطاء الصلاحيات المناسبة
RUN chown -R logstash:logstash /usr/share/logstash/ && \
    chmod +x /usr/share/logstash/mysql-connector.jar

# العودة لمستخدم logstash
USER logstash

# تعريف المنافذ
EXPOSE 5044 9600

# أمر البدء مع إعادة التحميل التلقائي
CMD ["/usr/share/logstash/bin/logstash", "--config.reload.automatic"]