#!/bin/bash

###############################################################################
# Fisherfolk Management System - Quick Setup Script
# For LAMP Stack (Linux, Apache, MySQL, PHP)
# Powerbyte IT Solutions - Calapan City FMO
###############################################################################

echo "=========================================="
echo "Fisherfolk Dashboard - Quick Setup"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if MySQL is running
echo -n "Checking MySQL service... "
if systemctl is-active --quiet mysql || systemctl is-active --quiet mariadb; then
    echo -e "${GREEN}✓ Running${NC}"
else
    echo -e "${RED}✗ Not running${NC}"
    echo "Please start MySQL/MariaDB: sudo systemctl start mysql"
    exit 1
fi

# Check if Apache is running
echo -n "Checking Apache service... "
if systemctl is-active --quiet apache2 || systemctl is-active --quiet httpd; then
    echo -e "${GREEN}✓ Running${NC}"
else
    echo -e "${RED}✗ Not running${NC}"
    echo "Please start Apache: sudo systemctl start apache2"
    exit 1
fi

# Check PHP
echo -n "Checking PHP installation... "
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -f1-2 -d".")
    echo -e "${GREEN}✓ PHP $PHP_VERSION${NC}"
else
    echo -e "${RED}✗ Not found${NC}"
    echo "Please install PHP: sudo apt install php php-mysql"
    exit 1
fi

echo ""
echo "=========================================="
echo "Database Setup"
echo "=========================================="
echo ""

# Get MySQL credentials
read -p "MySQL username [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "MySQL password: " DB_PASS
echo ""

# Test connection
echo -n "Testing database connection... "
if mysql -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1" &> /dev/null; then
    echo -e "${GREEN}✓ Success${NC}"
else
    echo -e "${RED}✗ Failed${NC}"
    echo "Please check your credentials"
    exit 1
fi

# Create database and import schema
echo -n "Creating database 'fmo_fisherfolk_management_system'... "
mysql -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS fmo_fisherfolk_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Done${NC}"
else
    echo -e "${RED}✗ Failed${NC}"
    exit 1
fi

# Import schema
echo -n "Importing database schema... "
mysql -u"$DB_USER" -p"$DB_PASS" fmo_fisherfolk_management_system < sql/schema.sql 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Done${NC}"
else
    echo -e "${RED}✗ Failed${NC}"
    exit 1
fi

# Import sample data
echo -n "Importing sample data... "
mysql -u"$DB_USER" -p"$DB_PASS" fmo_fisherfolk_management_system < sql/sample_data.sql 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Done${NC}"
else
    echo -e "${RED}✗ Failed${NC}"
    exit 1
fi

# Verify data
RECORD_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASS" fmo_fisherfolk_management_system -se "SELECT COUNT(*) FROM fisherfolk" 2>/dev/null)
echo "Records imported: $RECORD_COUNT fisherfolk"

echo ""
echo "=========================================="
echo "Web Server Configuration"
echo "=========================================="
echo ""

# Get current directory
CURRENT_DIR=$(pwd)
echo "Project location: $CURRENT_DIR"
echo ""

# Deployment options
echo "Choose deployment method:"
echo "1) Copy to /var/www/html (requires sudo)"
echo "2) Create symbolic link (requires sudo)"
echo "3) Manual deployment (show instructions)"
read -p "Select option [1-3]: " DEPLOY_OPTION

case $DEPLOY_OPTION in
    1)
        echo -n "Copying to /var/www/html/fisherfolk-dashboard... "
        sudo cp -r "$CURRENT_DIR" /var/www/html/fisherfolk-dashboard
        sudo chown -R www-data:www-data /var/www/html/fisherfolk-dashboard
        sudo chmod -R 755 /var/www/html/fisherfolk-dashboard
        echo -e "${GREEN}✓ Done${NC}"
        URL="http://localhost/fisherfolk-dashboard/public/"
        ;;
    2)
        echo -n "Creating symbolic link... "
        sudo ln -sf "$CURRENT_DIR/public" /var/www/html/fisherfolk-dashboard
        sudo chown -h www-data:www-data /var/www/html/fisherfolk-dashboard
        echo -e "${GREEN}✓ Done${NC}"
        URL="http://localhost/fisherfolk-dashboard/"
        ;;
    3)
        echo ""
        echo "Manual deployment instructions:"
        echo "1. Copy 'public' folder to your web server document root"
        echo "2. Ensure Apache can read the files"
        echo "3. Update database credentials in config/database.php if needed"
        echo ""
        URL="http://localhost/path-to-your-deployment/public/"
        ;;
esac

echo ""
echo "=========================================="
echo "✓ Setup Complete!"
echo "=========================================="
echo ""
echo "Dashboard URL: ${GREEN}${URL}${NC}"
echo ""
echo "Next steps:"
echo "1. Open your browser and navigate to the URL above"
echo "2. View the interactive charts and statistics"
echo "3. Add real fisherfolk data to replace sample data"
echo ""
echo "For more information, see README.md"
echo ""
