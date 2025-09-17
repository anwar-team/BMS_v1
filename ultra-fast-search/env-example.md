# Environment Variables (.env)

Add these to your .env file:

```env
# Search Configuration
SCOUT_DRIVER=elasticsearch
SCOUT_QUEUE=false

# Elasticsearch Configuration
ELASTICSEARCH_HOST=http://localhost:9200
ELASTICSEARCH_INDEX=pages
ELASTICSEARCH_TIMEOUT=120
ELASTICSEARCH_CONNECT_TIMEOUT=30
```

If you're using a remote Elasticsearch server, change the host:
```env
ELASTICSEARCH_HOST=http://your-elasticsearch-server:9200
```