# Fisherfolk Management System - Environment Setup

## Development vs Production

The system now supports automatic environment detection with separate database configurations:

### Files Created:

1. **`config/database.php`** - Development database config (localhost)
2. **`config/database.prod.php`** - Production database config (mysecurecloudhost.com)
3. **`config/env.php`** - Environment auto-detection and loader
4. **`deploy-production.sh`** - Production deployment script
5. **`test-db-connection.php`** - Database connection test utility

### Environment Detection:

The system automatically detects the environment based on:
- **Development**: `APP_ENV=development` variable OR localhost/127.0.0.1 hostname
- **Production** (Default): All other cases (shared hosting friendly)

Production is the default to work seamlessly on shared hosting where environment variables cannot be set.

### Local Development:

Development uses `config/database.php` with localhost credentials.

Run the development server:
```bash
./run-dev-server.sh
```

This automatically sets `APP_ENV=development` to use the local database.

### Production Deployment:

1. **Upload files to production server:**
   ```bash
   # All files from this directory to your web root
   ```

2. **Test database connection:**
   ```bash
   php test-db-connection.php
   ```

3. **Verify environment:**
   - Production automatically uses: s1105.usc1.mysecurecloudhost.com
   - Database: jerlanlo_powerbyteitsolutions_com_fisherfolks

### Manual Environment Override:

Development server script automatically sets:
```bash
export APP_ENV=development
```

For manual override in other contexts:
```bash
export APP_ENV=production  # Force production
export APP_ENV=development  # Force development
```

Or in your Apache/hosting configuration:
```apache
SetEnv APP_ENV production
```

### Security Notes:

- ✅ Production credentials secured in `database.prod.php`
- ✅ Keep `.git` folder out of public web directory
- ✅ Enable HTTPS on production
- ✅ Set proper file permissions (755/644)
- ✅ Regular database backups recommended

### Testing:

**Development:**
```bash
php test-db-connection.php
# Should connect to localhost
```

**Production:**
```bash
export APP_ENV=production
php test-db-connection.php
# Should connect to mysecurecloudhost.com
```
