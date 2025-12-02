# Fisherfolk ID Database - Chart Dashboard

> Data visualization and charting system for the Fisherfolk Identification Database of Calapan City FMO
> 
> Developed by **Powerbyte IT Solutions**

## 📊 Overview

This project provides a comprehensive dashboard for visualizing fisherfolk data in Calapan City. The system displays various charts and statistics including demographics, barangay distribution, gender breakdown, age groups, and activity categories.

## 🎨 Features

- **Real-time Dashboard** with interactive charts
- **Summary Statistics Cards** showing key metrics
- **5 Chart Types**:
  - Fisherfolk distribution by barangay (Horizontal Bar Chart)
  - Gender distribution (Doughnut Chart)
  - Age group distribution (Bar Chart)
  - Activity category distribution (Horizontal Bar Chart)
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Maritime Theme** - Blue (#0000FF) and Orange (#FFA500) color scheme

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **UI Framework**: Bootstrap 5.3.2
- **Chart Library**: Chart.js 4.4.0
- **Icons**: Font Awesome 6.4.2
- **Server**: Apache (LAMP Stack)

## 📋 Prerequisites

- Apache web server with PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- PHP PDO MySQL extension enabled
- Web browser with JavaScript enabled

## 🚀 Installation

### 1. Clone or Download

```bash
git clone https://github.com/bonitobonita24/fmo-fisherfolk-management-system.git
cd fmo-fisherfolk-management-system
```

### 2. Database Setup

#### Import the database schema and sample data:

```bash
# Login to MySQL
mysql -u root -p

# Run the schema file
mysql -u root -p < sql/schema.sql

# Import sample data
mysql -u root -p fisherfolk_db < sql/sample_data.sql
```

Or use phpMyAdmin:
1. Create a new database named `fisherfolk_db`
2. Import `sql/schema.sql`
3. Import `sql/sample_data.sql`

### 3. Configure Database Connection

Edit `config/database.php` if your database credentials differ:

```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'your-password-here');
define('DB_NAME', 'fisherfolk_db');
```

### 4. Deploy to Web Server

#### Option A: Move to Apache DocumentRoot

```bash
# Copy project to Apache web directory
sudo cp -r . /var/www/html/fisherfolk-dashboard/

# Set proper permissions
sudo chown -R www-data:www-data /var/www/html/fisherfolk-dashboard/
sudo chmod -R 755 /var/www/html/fisherfolk-dashboard/
```

#### Option B: Use as Virtual Host

Create Apache virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName fisherfolk.local
    DocumentRoot /path/to/fmo-fisherfolk-management-system/public
    
    <Directory /path/to/fmo-fisherfolk-management-system/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/fisherfolk-error.log
    CustomLog ${APACHE_LOG_DIR}/fisherfolk-access.log combined
</VirtualHost>
```

### 5. Access the Dashboard

Open your web browser and navigate to:
- `http://localhost/fisherfolk-dashboard/public/`
- Or `http://fisherfolk.local` (if using virtual host)

## 📁 Project Structure

```
fmo-fisherfolk-management-system/
├── .github/
│   └── copilot-instructions.md    # AI coding agent instructions
├── api/                            # PHP API endpoints
│   ├── barangay-stats.php         # Barangay distribution data
│   ├── gender-stats.php           # Gender statistics
│   ├── age-group-stats.php        # Age group distribution
│   ├── category-stats.php         # Activity category data
│   └── summary-stats.php          # Overall summary statistics
├── config/
│   └── database.php               # Database configuration
├── public/                         # Public web root
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css          # Custom styles
│   │   └── js/
│   │       └── charts.js          # Chart initialization & data loading
│   └── index.html                 # Main dashboard page
├── sql/
│   ├── schema.sql                 # Database schema
│   └── sample_data.sql            # Sample fisherfolk data
└── README.md                       # This file
```

## 🔌 API Endpoints

All API endpoints return JSON data:

| Endpoint | Description | Response |
|----------|-------------|----------|
| `/api/summary-stats.php` | Overall statistics | Total fisherfolk, gender counts, barangay count |
| `/api/barangay-stats.php` | Barangay distribution | Count per barangay |
| `/api/gender-stats.php` | Gender breakdown | Male/Female counts |
| `/api/age-group-stats.php` | Age group distribution | Count per age group |
| `/api/category-stats.php` | Activity categories | Count per fishing activity type |

### Example API Response

```json
{
  "success": true,
  "data": [
    {
      "barangay": "Barangay Poblacion",
      "count": "5"
    },
    {
      "barangay": "Barangay Guinobatan",
      "count": "4"
    }
  ]
}
```

## 📊 Database Schema

### Fisherfolk Table

```sql
CREATE TABLE fisherfolk (
    id_number VARCHAR(50) PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    address VARCHAR(255) NOT NULL,
    sex VARCHAR(10) NOT NULL,
    image VARCHAR(255),
    signature VARCHAR(255),
    rsbsa VARCHAR(50),
    contact_number VARCHAR(20),
    boat_owneroperator TINYINT(1) DEFAULT 0,
    capture_fishing TINYINT(1) DEFAULT 0,
    gleaning TINYINT(1) DEFAULT 0,
    vendor TINYINT(1) DEFAULT 0,
    fish_processing TINYINT(1) DEFAULT 0,
    aquaculture TINYINT(1) DEFAULT 0,
    date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🎯 Usage

1. **View Dashboard**: Open the main page to see all charts and statistics
2. **Interact with Charts**: Hover over chart elements to see detailed tooltips
3. **Responsive Design**: Access from any device - desktop, tablet, or mobile
4. **Auto-refresh** (optional): Uncomment the auto-refresh line in `charts.js` for live updates

## 🔧 Customization

### Change Colors

Edit `public/assets/css/style.css` and `public/assets/js/charts.js`:

```css
/* style.css */
.bg-primary {
    background-color: #YourColor !important;
}
```

```javascript
// charts.js
const THEME_COLORS = {
    primary: '#YourPrimaryColor',
    secondary: '#YourSecondaryColor',
};
```

### Add More Charts

1. Create new PHP API endpoint in `/api/`
2. Add canvas element to `index.html`
3. Create chart function in `charts.js`
4. Call the function in `initializeDashboard()`

## 🐛 Troubleshooting

### Charts Not Loading

1. Check browser console for JavaScript errors
2. Verify API endpoints are accessible: `http://localhost/fisherfolk-dashboard/api/summary-stats.php`
3. Ensure database connection is configured correctly
4. Check Apache error logs: `/var/log/apache2/error.log`

### Database Connection Errors

1. Verify MySQL service is running: `sudo systemctl status mysql`
2. Check database credentials in `config/database.php`
3. Ensure PHP PDO MySQL extension is enabled: `php -m | grep pdo_mysql`
4. Verify database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### Permission Issues

```bash
# Fix file permissions
sudo chown -R www-data:www-data /path/to/project/
sudo chmod -R 755 /path/to/project/
```

## 📝 Sample Data

The system includes 35 sample fisherfolk records across 10 barangays in Calapan City:
- Poblacion, Guinobatan, Canubing I, Canubing II, Suqui
- Lumang Bayan, Sta. Rita, Tawagan, Silonay, Bayanan I

You can add more data directly through MySQL or via a separate CRUD interface.

## 🔐 Security Notes

- Change default database password in production
- Implement proper input validation for production use
- Use prepared statements (already implemented via PDO)
- Consider adding authentication for dashboard access
- Enable HTTPS in production environments

## 📞 Support

For issues or questions, contact:
- **Developer**: Powerbyte IT Solutions
- **Client**: Calapan City FMO
- **Project**: Fisherfolk Management System

## 📄 License

Proprietary - Developed for Calapan City Fisheries Management Office

---

**Powerbyte IT Solutions** | Calapan City FMO | 2025
