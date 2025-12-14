#!/bin/bash

##############################################
# Script for Setting Up HTTPS with SSL/TLS
# for BMS_v1 on VPS
##############################################

echo "🔐 Setting up HTTPS/SSL for BMS_v1"
echo "===================================="

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Please run as root (use sudo)"
    exit 1
fi

# Update package lists
echo "📦 Updating package lists..."
apt-get update

# Install Certbot for Let's Encrypt SSL
echo "🔧 Installing Certbot..."
apt-get install -y certbot python3-certbot-nginx

# Your domain names
DOMAIN="alkamelah.com"
DOMAIN_ALT1="alkamelah.net"
DOMAIN_ALT2="alkamelah.org"
EMAIL="admin@alkamelah.com"  # Replace with your email

echo "🌐 Primary Domain: $DOMAIN"
echo "🌐 Alternative Domain 1: $DOMAIN_ALT1"
echo "🌐 Alternative Domain 2: $DOMAIN_ALT2"
echo "📧 Email: $EMAIL"

# Obtain SSL Certificate for all domains
echo "🔐 Obtaining SSL certificates from Let's Encrypt..."
certbot --nginx -d $DOMAIN -d $DOMAIN_ALT1 -d $DOMAIN_ALT2 --non-interactive --agree-tos -m $EMAIL

# Auto-renewal setup
echo "⏰ Setting up automatic renewal..."
certbot renew --dry-run

# Update Nginx configuration for Laravel
echo "📝 Updating Nginx configuration..."

cat > /etc/nginx/sites-available/bms_v1 << 'EOF'
# Redirect all domains to primary (alkamelah.com)
server {
    listen 80;
    listen [::]:80;
    server_name alkamelah.net alkamelah.org;
    
    # Redirect to primary domain with HTTPS
    return 301 https://alkamelah.com$request_uri;
}

server {
    listen 80;
    listen [::]:80;
    server_name alkamelah.com;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name alkamelah.net alkamelah.org;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/alkamelah.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/alkamelah.com/privkey.pem;
    
    # Redirect to primary domain
    return 301 https://alkamelah.com$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name alkamelah.com;
    
    root /var/www/bms_v1/public;
    index index.php index.html index.htm;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/alkamelah.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/alkamelah.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Laravel Configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
    
    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

# Enable site configuration
ln -sf /etc/nginx/sites-available/bms_v1 /etc/nginx/sites-enabled/

# Test Nginx configuration
echo "🧪 Testing Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx configuration is valid"
    
    # Reload Nginx
    echo "🔄 Reloading Nginx..."
    systemctl reload nginx
    
    echo ""
    echo "✅ HTTPS setup completed successfully!"
    echo ""
    echo "📋 Next steps:"
    echo "1. Update your .env file: APP_URL=https://alkamelah.com"
    echo "2. Run: php artisan config:cache"
    echo "3. Test your sites:"
    echo "   - https://alkamelah.com"
    echo "   - https://alkamelah.net (redirects to .com)"
    echo "   - https://alkamelah.org (redirects to .com)"
    echo "4. Verify SSL: https://www.ssllabs.com/ssltest/"
    echo ""
else
    echo "❌ Nginx configuration test failed"
    echo "Please check the configuration and try again"
    exit 1
fi

# Display SSL certificate info
echo "📜 SSL Certificate Information:"
certbot certificates
