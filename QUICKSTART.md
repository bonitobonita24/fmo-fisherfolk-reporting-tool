# Quick Start Guide

## 🚀 Get Started in 3 Steps

### Step 1: Setup Database
```bash
# Run the automated setup script
./setup.sh

# OR manually import SQL files
mysql -u root -p < sql/schema.sql
mysql -u root -p fisherfolk_db < sql/sample_data.sql
```

### Step 2: Configure Database Connection
Edit `config/database.php` with your credentials:
```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'your-password');
define('DB_NAME', 'fisherfolk_db');
```

### Step 3: Access Dashboard
Open in browser:
- Local: `http://localhost/fisherfolk-dashboard/public/`
- Or place in your web server's document root

## 📊 What You Get

### 5 Interactive Charts
1. **Barangay Distribution** - Horizontal bar chart showing fisherfolk per barangay
2. **Gender Distribution** - Doughnut chart for male/female breakdown  
3. **Age Groups** - Bar chart with 6 age categories
4. **Activity Categories** - Horizontal bar for fishing activities
5. **Summary Cards** - Total, male, female, barangays count

### 5 API Endpoints
- `/api/summary-stats.php` - Overall statistics
- `/api/barangay-stats.php` - Per barangay counts
- `/api/gender-stats.php` - Gender breakdown
- `/api/age-group-stats.php` - Age distribution
- `/api/category-stats.php` - Activity categories

## 🎨 Features

✓ Responsive design (mobile, tablet, desktop)
✓ Maritime color theme (Blue #0000FF, Orange #FFA500)
✓ Real-time data from MySQL database
✓ Bootstrap 5 UI with Font Awesome icons
✓ Chart.js 4.4 for smooth animations
✓ Clean, modern interface
✓ 35 sample fisherfolk records included

## 🔧 Quick Commands

```bash
# Check if services are running
systemctl status apache2
systemctl status mysql

# View database records
mysql -u root -p -e "SELECT COUNT(*) FROM fisherfolk_db.fisherfolk"

# Test API endpoint
curl http://localhost/fisherfolk-dashboard/api/summary-stats.php

# View Apache error logs
tail -f /var/log/apache2/error.log
```

## 📝 File Overview

```
├── public/index.html          # Main dashboard page
├── public/assets/
│   ├── css/style.css          # Custom styles
│   └── js/charts.js           # Chart logic
├── api/                       # PHP endpoints
├── config/database.php        # DB connection
├── sql/                       # Database files
└── setup.sh                   # Auto-setup script
```

## 🆘 Troubleshooting

**Charts not showing?**
- Check browser console (F12)
- Verify API URLs are correct
- Test database connection

**500 Error?**
- Check `config/database.php` credentials
- Verify PHP PDO extension: `php -m | grep pdo`
- Check Apache error logs

**Permission denied?**
```bash
sudo chown -R www-data:www-data /var/www/html/fisherfolk-dashboard
sudo chmod -R 755 /var/www/html/fisherfolk-dashboard
```

## 📞 Support
- Full documentation: `README.md`
- Developer: Powerbyte IT Solutions
- Client: Calapan City FMO
