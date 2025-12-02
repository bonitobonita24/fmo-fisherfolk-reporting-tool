#!/bin/bash

###############################################################################
# Test Database Connection
# Quick utility to verify WAMP MySQL is accessible
###############################################################################

echo "Testing WAMP MySQL Connection..."
echo "================================"
echo ""

# Get credentials from config
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_USER="root"
DB_PASS='4,q@TG^Gy.HzM%ZL-B'
DB_NAME="fmo_fisherfolk_management_system"

echo "Host: $DB_HOST:$DB_PORT"
echo "User: $DB_USER"
echo "Database: $DB_NAME"
echo ""

# Test connection
echo -n "Testing connection... "
RESULT=$(php -r "
try {
    \$conn = new PDO('mysql:host=$DB_HOST;port=$DB_PORT', '$DB_USER', '$DB_PASS');
    echo 'OK';
} catch (Exception \$e) {
    echo 'FAILED: ' . \$e->getMessage();
}
" 2>&1)

if [[ "$RESULT" == "OK" ]]; then
    echo -e "\033[0;32m✓ Connected\033[0m"
else
    echo -e "\033[0;31m✗ $RESULT\033[0m"
    echo ""
    echo "Troubleshooting:"
    echo "1. Is WAMP running? (Check system tray for green icon)"
    echo "2. Is MySQL service started in WAMP?"
    echo "3. Are credentials correct in config/database.php?"
    exit 1
fi

# Check if database exists
echo -n "Checking database '$DB_NAME'... "
DB_EXISTS=$(php -r "
try {
    \$conn = new PDO('mysql:host=$DB_HOST;port=$DB_PORT', '$DB_USER', '$DB_PASS');
    \$stmt = \$conn->query('SHOW DATABASES LIKE \"$DB_NAME\"');
    echo \$stmt->rowCount() > 0 ? 'yes' : 'no';
} catch (Exception \$e) {
    echo 'error';
}
" 2>&1)

if [[ "$DB_EXISTS" == "yes" ]]; then
    echo -e "\033[0;32m✓ Found\033[0m"
    
    # Check if table exists
    echo -n "Checking 'fisherfolk' table... "
    TABLE_EXISTS=$(php -r "
    try {
        \$conn = new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME', '$DB_USER', '$DB_PASS');
        \$stmt = \$conn->query('SHOW TABLES LIKE \"fisherfolk\"');
        echo \$stmt->rowCount() > 0 ? 'yes' : 'no';
    } catch (Exception \$e) {
        echo 'error';
    }
    " 2>&1)
    
    if [[ "$TABLE_EXISTS" == "yes" ]]; then
        echo -e "\033[0;32m✓ Found\033[0m"
        
        # Count records
        echo -n "Counting records... "
        RECORD_COUNT=$(php -r "
        try {
            \$conn = new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME', '$DB_USER', '$DB_PASS');
            \$stmt = \$conn->query('SELECT COUNT(*) as count FROM fisherfolk');
            \$row = \$stmt->fetch();
            echo \$row['count'];
        } catch (Exception \$e) {
            echo 'error';
        }
        " 2>&1)
        
        if [[ "$RECORD_COUNT" =~ ^[0-9]+$ ]]; then
            echo -e "\033[0;32m$RECORD_COUNT fisherfolk records\033[0m"
        else
            echo -e "\033[0;31m✗ Error\033[0m"
        fi
    else
        echo -e "\033[0;33m⚠ Not found\033[0m"
        echo ""
        echo "Run: mysql -u root -p $DB_NAME < sql/schema.sql"
    fi
elif [[ "$DB_EXISTS" == "no" ]]; then
    echo -e "\033[0;33m⚠ Not found\033[0m"
    echo ""
    echo "To create database and import data:"
    echo "  mysql -u root -p < sql/schema.sql"
    echo "  mysql -u root -p $DB_NAME < sql/sample_data.sql"
else
    echo -e "\033[0;31m✗ Error checking\033[0m"
fi

echo ""
echo "================================"
echo -e "\033[0;32m✓ Connection test complete\033[0m"
echo ""
echo "If everything looks good, start the server with:"
echo "  \033[0;34m./run-dev-server.sh\033[0m"
echo ""
