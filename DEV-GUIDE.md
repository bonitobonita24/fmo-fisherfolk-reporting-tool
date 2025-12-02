# Development Environment Guide

## 🚀 Quick Start for Development

### Prerequisites
✅ PHP 8.3.6 installed
✅ WAMP MySQL running on localhost:3306

### Start Development Server

```bash
# 1. Make sure WAMP is running (MySQL service)

# 2. Start the PHP development server
./run-dev-server.sh

# 3. Open browser to:
#    http://localhost:8080
```

## 📝 How It Works

### Architecture
```
┌─────────────────────────────────────────┐
│  Browser: http://localhost:8080         │
└────────────────┬────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────┐
│  PHP Built-in Server (Port 8080)        │
│  - Serves: public/index.html             │
│  - Routes: /api/* → api/*.php            │
│  - Router: dev-router.php                │
└────────────────┬────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────┐
│  WAMP MySQL (Port 3306)                  │
│  - Database: fisherfolk_db               │
│  - Credentials: config/database.php      │
└─────────────────────────────────────────┘
```

### What Was Set Up

1. **PHP Built-in Server** (Portable)
   - No Apache installation needed in project
   - Runs on localhost:8080 (configurable)
   - Hot-reload on file changes

2. **Router** (`dev-router.php`)
   - Routes `/api/*` to PHP files
   - Serves static files (HTML, CSS, JS)
   - Handles 404s gracefully

3. **Database Connection**
   - Connects to external WAMP MySQL
   - Configuration in `config/database.php`
   - Uses PDO for security

## 🔧 Configuration Files

### config/database.php
```php
DB_HOST: 127.0.0.1
DB_PORT: 3306
DB_USER: root
DB_PASS: 4,q@TG^Gy.HzM%ZL-B
DB_NAME: fisherfolk_db
```

To change credentials, edit this file or run:
```bash
./dev-setup.sh  # Re-run setup wizard
```

## 📊 Setting Up the Database in WAMP

### Option 1: Using phpMyAdmin
1. Start WAMP
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Create database: `fisherfolk_db`
4. Import `sql/schema.sql`
5. Import `sql/sample_data.sql`

### Option 2: Using MySQL Command Line
```bash
# Access WAMP MySQL
mysql -u root -p

# Run setup
mysql -u root -p < sql/schema.sql
mysql -u root -p fisherfolk_db < sql/sample_data.sql
```

### Option 3: Using PHP (While Server Running)
```bash
# The dev-setup.sh script can do this automatically
# Just select 'Y' when asked to create database
```

## 🌐 Testing the Dashboard

### 1. Start WAMP
- Ensure MySQL is running (green icon)
- Default: localhost:3306

### 2. Start Dev Server
```bash
./run-dev-server.sh
```

### 3. Access Dashboard
Open browser: **http://localhost:8080**

You should see:
- ✅ 4 Summary cards (Total, Male, Female, Barangays)
- ✅ Barangay distribution chart
- ✅ Gender distribution chart
- ✅ Age group chart
- ✅ Category/activity chart

### 4. Test API Endpoints
```bash
# Summary stats
curl http://localhost:8080/api/summary-stats.php

# Barangay distribution
curl http://localhost:8080/api/barangay-stats.php

# Gender stats
curl http://localhost:8080/api/gender-stats.php

# Age groups
curl http://localhost:8080/api/age-group-stats.php

# Categories
curl http://localhost:8080/api/category-stats.php
```

## 🛠️ Development Workflow

### Making Changes

**Frontend (HTML/CSS/JS):**
```bash
# Edit files in:
public/index.html
public/assets/css/style.css
public/assets/js/charts.js

# Refresh browser (Ctrl+F5) to see changes
```

**Backend (PHP APIs):**
```bash
# Edit files in:
api/*.php
config/database.php

# Changes take effect immediately
# No server restart needed
```

**Database Schema:**
```bash
# Edit:
sql/schema.sql

# Re-import in phpMyAdmin or:
mysql -u root -p fisherfolk_db < sql/schema.sql
```

## 🔍 Troubleshooting

### Server Won't Start
```bash
# Check if port 8080 is already in use
lsof -i :8080

# Use different port
# Edit run-dev-server.sh, change SERVER_PORT
```

### Database Connection Failed
```bash
# 1. Check WAMP is running
# Look for green icon in system tray

# 2. Test MySQL connection
mysql -u root -p -h 127.0.0.1 -P 3306

# 3. Verify credentials in config/database.php
# 4. Check firewall isn't blocking port 3306
```

### Charts Not Showing
```bash
# 1. Open browser console (F12)
# 2. Check for JavaScript errors
# 3. Verify API endpoints return data:
curl http://localhost:8080/api/summary-stats.php

# 4. Check database has data:
mysql -u root -p -e "SELECT COUNT(*) FROM fisherfolk_db.fisherfolk"
```

### No Data in Charts
```bash
# Import sample data:
mysql -u root -p fisherfolk_db < sql/sample_data.sql

# Or run setup again:
./dev-setup.sh
```

## 📦 Project Structure
```
fmo-fisherfolk-management-system/
├── dev-router.php         # Request router for dev server
├── run-dev-server.sh      # Start dev server
├── dev-setup.sh           # Configure environment
├── install-php.sh         # Install PHP (if needed)
│
├── public/                # Web root
│   ├── index.html         # Main dashboard
│   └── assets/
│       ├── css/style.css  # Custom styles
│       └── js/charts.js   # Chart logic
│
├── api/                   # Backend endpoints
│   ├── summary-stats.php
│   ├── barangay-stats.php
│   ├── gender-stats.php
│   ├── age-group-stats.php
│   └── category-stats.php
│
├── config/
│   └── database.php       # DB connection
│
└── sql/
    ├── schema.sql         # Database structure
    └── sample_data.sql    # Test data (35 records)
```

## 🎯 Common Tasks

### Change Server Port
```bash
# Edit run-dev-server.sh
SERVER_PORT="8000"  # Change to desired port
```

### Add New Chart
1. Create API endpoint: `api/new-chart-stats.php`
2. Add canvas to `public/index.html`
3. Create chart function in `public/assets/js/charts.js`
4. Call function in `initializeDashboard()`

### Use Different Database
```bash
# Edit config/database.php
define('DB_NAME', 'your_database_name');

# Update schema location if needed
```

### Deploy to Production
```bash
# Copy to Apache DocumentRoot:
sudo cp -r public /var/www/html/fisherfolk-dashboard
sudo cp -r api /var/www/html/fisherfolk-dashboard/
sudo cp -r config /var/www/html/fisherfolk-dashboard/

# Update database.php with production credentials
```

## 📞 Support

**Development Server Issues:**
- Check: `./run-dev-server.sh` output
- Logs: Displayed in terminal
- Browser Console: F12 → Console tab

**Database Issues:**
- WAMP Logs: Check phpMyAdmin
- Test connection: `config/database.php`
- Sample data: `sql/sample_data.sql`

---

**Development Environment Ready! 🎉**

Start coding with:
```bash
./run-dev-server.sh
```
