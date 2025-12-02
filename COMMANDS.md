# Development Server - Command Reference

## 🚀 Essential Commands

### Start Development Server
```bash
./run-dev-server.sh
```
Opens server on http://localhost:8080

### Test Database Connection
```bash
./test-db.sh
```
Verifies WAMP MySQL is accessible and data exists

### Setup/Reconfigure Environment
```bash
./dev-setup.sh
```
Interactive setup for database credentials and server configuration

## 📊 Database Setup (WAMP MySQL)

### Import Schema and Sample Data
```bash
# Using MySQL command line (from WAMP)
mysql -u root -p < sql/schema.sql
mysql -u root -p fisherfolk_db < sql/sample_data.sql

# Or use phpMyAdmin:
# 1. Open: http://localhost/phpmyadmin
# 2. Create database: fisherfolk_db
# 3. Import: sql/schema.sql
# 4. Import: sql/sample_data.sql
```

### Check Data
```bash
# Count records
mysql -u root -p -e "SELECT COUNT(*) FROM fisherfolk_db.fisherfolk"

# View sample records
mysql -u root -p -e "SELECT * FROM fisherfolk_db.fisherfolk LIMIT 5"
```

## 🔧 Configuration

### Update Database Credentials
```bash
# Edit config file
nano config/database.php

# Or re-run setup
./dev-setup.sh
```

### Change Server Port
```bash
# Edit run-dev-server.sh
nano run-dev-server.sh
# Change: SERVER_PORT="8080" to your preferred port
```

## 🧪 Testing

### Test API Endpoints
```bash
# Summary statistics
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

### Browser Testing
```bash
# Open dashboard
xdg-open http://localhost:8080

# Or manually open in browser:
# http://localhost:8080
```

## 🛠️ Development Tasks

### Edit Frontend
```bash
# HTML
nano public/index.html

# CSS
nano public/assets/css/style.css

# JavaScript
nano public/assets/js/charts.js

# Refresh browser to see changes (Ctrl+F5)
```

### Edit Backend
```bash
# API files
nano api/summary-stats.php
nano api/barangay-stats.php
# etc...

# Database config
nano config/database.php

# Changes take effect immediately
```

### View Server Logs
```bash
# Logs appear in terminal where run-dev-server.sh is running
# Look for errors in red text
```

## 🐛 Troubleshooting

### Server won't start
```bash
# Check if port is in use
lsof -i :8080

# Try different port in run-dev-server.sh
```

### Database connection failed
```bash
# 1. Verify WAMP is running
# Check system tray for green icon

# 2. Test connection
./test-db.sh

# 3. Check MySQL is accessible
mysql -u root -p -h 127.0.0.1

# 4. Verify credentials in config/database.php
```

### No data showing
```bash
# Import sample data
mysql -u root -p fisherfolk_db < sql/sample_data.sql

# Test API returns data
curl http://localhost:8080/api/summary-stats.php
```

### Charts not rendering
```bash
# 1. Open browser console (F12)
# 2. Check for JavaScript errors
# 3. Verify Chart.js is loading
# 4. Test API endpoints with curl
```

## 📁 File Locations

```
Development Files:
├── run-dev-server.sh   → Start server
├── dev-setup.sh        → Configure environment
├── test-db.sh          → Test database
├── dev-router.php      → Route handler
└── DEV-GUIDE.md        → Full documentation

Application Files:
├── public/             → Frontend (HTML/CSS/JS)
├── api/                → Backend (PHP endpoints)
├── config/             → Database configuration
└── sql/                → Database schema & data

Documentation:
├── README.md           → Full project documentation
├── QUICKSTART.md       → Quick reference
├── DEV-GUIDE.md        → Development guide
└── COMMANDS.md         → This file
```

## ⚡ Quick Workflow

```bash
# 1. Start WAMP (ensure MySQL is running)

# 2. Test database connection
./test-db.sh

# 3. Start development server
./run-dev-server.sh

# 4. Open browser
# http://localhost:8080

# 5. Make changes to files
# Browser auto-reloads on refresh

# 6. Stop server
# Press Ctrl+C in terminal
```

## 🎯 Common Scenarios

### First Time Setup
```bash
# Install PHP (if needed)
./install-php.sh

# Configure environment
./dev-setup.sh

# Import database (in WAMP MySQL)
mysql -u root -p < sql/schema.sql
mysql -u root -p fisherfolk_db < sql/sample_data.sql

# Start server
./run-dev-server.sh
```

### Daily Development
```bash
# Start WAMP

# Start server
./run-dev-server.sh

# Make changes, test in browser

# Stop server (Ctrl+C)
```

### Testing Changes
```bash
# Test API
curl http://localhost:8080/api/summary-stats.php | jq

# Test database
./test-db.sh

# View in browser
# http://localhost:8080
```

---

**Need help? Check DEV-GUIDE.md for detailed documentation**
