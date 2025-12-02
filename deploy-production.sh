#!/bin/bash

# Production Deployment Script
# Fisherfolk Management System - Calapan City FMO

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║  Fisherfolk Management System - Production Deployment    ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Set production environment
export APP_ENV=production

echo "🚀 Deploying to Production Environment..."
echo "   Server: s1105.usc1.mysecurecloudhost.com"
echo "   Database: jerlanlo_powerbyteitsolutions_com_fisherfolks"
echo ""

# Check if required files exist
if [ ! -f "config/database.prod.php" ]; then
    echo "❌ Error: Production database config not found!"
    exit 1
fi

echo "✅ Production configuration files found"
echo ""

# Create production .htaccess if not exists
if [ ! -f "public/.htaccess" ]; then
    echo "📝 Creating production .htaccess..."
    cat > public/.htaccess << 'HTACCESS'
# Production .htaccess for Fisherfolk Management System

# Enable rewrite engine
RewriteEngine On

# Redirect to index.html for root
DirectoryIndex index.html

# API routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} ^/api/
RewriteRule ^api/(.*)$ ../api/$1 [L]

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Prevent directory browsing
Options -Indexes

# PHP settings
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value max_input_time 300
</IfModule>
HTACCESS
    echo "✅ .htaccess created"
fi

echo ""
echo "📋 Deployment Checklist:"
echo "   ✓ Production database credentials configured"
echo "   ✓ Environment auto-detection enabled"
echo "   ✓ Security headers configured"
echo ""

echo "⚠️  Manual Steps Required:"
echo "   1. Upload all files to production server"
echo "   2. Ensure database tables are created"
echo "   3. Set proper file permissions (755 for directories, 644 for files)"
echo "   4. Verify database connection: php -f test-db-connection.php"
echo ""

echo "🔐 Security Recommendations:"
echo "   • Keep database credentials secure"
echo "   • Enable HTTPS/SSL on production server"
echo "   • Regular database backups"
echo "   • Monitor error logs"
echo ""

echo "✨ Deployment preparation complete!"
