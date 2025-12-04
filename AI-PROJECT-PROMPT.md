# Fisherfolk Management System - AI Agent Project Brief

## 🎯 Project Overview

**Project Name:** Fisherfolk Identification Database & Dashboard System  
**Client:** Calapan City Fisheries Management Office (FMO)  
**Developer:** Powerbyte IT Solutions  
**Tech Stack:** Laravel 11+ (Latest), Tailwind CSS, MySQL, PHP 8.2+  
**Database:** MySQL with 2,051+ fisherfolk records  

This is a complete data visualization and management system for tracking fisherfolk in Calapan City. The system displays interactive charts, statistics, and detailed records of registered fisherfolk with their photos, signatures, and activity categories.

### 🎯 Framework Requirements
- **Backend Framework:** Laravel 11+ (use latest stable version)
- **Frontend Framework:** Tailwind CSS 3+ for all UI/UX
- **Authentication:** Laravel Breeze or Laravel Jetstream with Tailwind
- **Database ORM:** Eloquent ORM
- **Routing:** Laravel routing with middleware protection
- **Asset Building:** Vite (Laravel default)

### 👥 User Management & Permissions
The system includes a **flexible role-based permission system** where each user can have granular permissions for every page/module:

**Permission Types (per page/module):**
- ✅ **Create** - Can add new records
- ✅ **View** - Can view/read records
- ✅ **Update** - Can edit existing records
- ✅ **Delete** - Can remove records

**User Management Page Features:**
- Manage user accounts (CRUD)
- Assign permissions via radio buttons/checkboxes per module
- Permission matrix UI showing all pages × all permission types
- Real-time permission updates
- User role templates (Admin, Viewer, Editor, etc.)

---

## 📊 System Features

### Dashboard Components
1. **Summary Statistics Cards**
   - Total registered fisherfolk count
   - Male/Female gender distribution
   - Number of distinct barangays
   - Active categories count

2. **Interactive Charts** (using Chart.js)
   - Barangay Distribution (Horizontal Bar Chart)
   - Gender Distribution (Doughnut Chart)
   - Age Group Distribution (Bar Chart)
   - Activity Categories (Horizontal Bar Chart)
   - Barangay-Category Cross-Analysis (Stacked Bar Chart)

3. **Fisherfolk List Table**
   - Searchable and filterable table
   - Displays photos and signatures (with modal zoom)
   - Filter by barangay, gender
   - Sort by name, ID, or barangay
   - Shows activity category badges
   - Responsive design with Tailwind CSS

4. **User Management Module** (NEW)
   - User account CRUD operations
   - Permission management per user
   - Granular permissions: Create, View, Update, Delete
   - Permission matrix interface (users × pages × permissions)
   - Role templates (Super Admin, Admin, Editor, Viewer)
   - User status management (Active/Inactive)
   - Password reset functionality
   - User activity logging

5. **CSV Import Feature**
   - Bulk import fisherfolk data
   - Downloadable CSV template
   - Data validation and error reporting

---

## 🗄️ Database Schema

**Database Name:** `fmo_fisherfolk_management_system`

**Table: `fisherfolk`**

```sql
CREATE TABLE fisherfolk (
    id_number VARCHAR(50) PRIMARY KEY,           -- Unique fisherfolk ID (8-12 chars)
    full_name VARCHAR(255) NOT NULL,              -- Full name
    date_of_birth DATE NOT NULL,                  -- Birth date
    address VARCHAR(255) NOT NULL,                -- Barangay name
    sex VARCHAR(10) NOT NULL,                     -- "Male" or "Female"
    image VARCHAR(255),                           -- Photo filename (stored in /public/uploads/)
    signature VARCHAR(255),                       -- Signature filename (stored in /public/uploads/)
    rsbsa VARCHAR(50),                            -- RSBSA registration number
    contact_number VARCHAR(20),                   -- Contact number
    
    -- Activity Categories (boolean flags)
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

**Table: `users`** (Laravel default with extensions)

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Table: `permissions`** (User permission matrix)

```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    module VARCHAR(50) NOT NULL,                  -- 'dashboard', 'fisherfolk', 'users', 'reports', etc.
    can_create BOOLEAN DEFAULT 0,
    can_view BOOLEAN DEFAULT 0,
    can_update BOOLEAN DEFAULT 0,
    can_delete BOOLEAN DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_module (user_id, module)
);
```

**Available Modules for Permissions:**
- `dashboard` - Main dashboard access
- `fisherfolk` - Fisherfolk management
- `users` - User management (admin only by default)
- `reports` - Reports and analytics
- `import` - CSV import functionality
- `settings` - System settings

**Important Notes:**
- `id_number` is the primary key for fisherfolk (string, not auto-increment)
- Images/signatures stored as filenames (e.g., "MR-CL-001234-2015.JPG")
- Actual files located in `/storage/app/public/uploads/` (Laravel storage)
- **File extensions MUST match database** (case-sensitive: .JPG, .PNG uppercase)
- Multiple activity categories per fisherfolk (checkboxes, not mutually exclusive)
- Permissions are module-based with CRUD granularity

---

## 🔌 API Endpoints

All APIs follow Laravel RESTful conventions with JSON responses:

```json
{
  "success": true,
  "data": [...],
  "message": "Operation successful"
}
```

### Available Endpoints

| Endpoint | Method | Description | Permissions Required |
|----------|--------|-------------|---------------------|
| `/api/stats/summary` | GET | Overall statistics | `dashboard.view` |
| `/api/stats/barangay` | GET | Count of fisherfolk per barangay | `dashboard.view` |
| `/api/stats/gender` | GET | Male/Female counts | `dashboard.view` |
| `/api/stats/age-group` | GET | Distribution by age groups | `dashboard.view` |
| `/api/stats/category` | GET | Count per activity category | `dashboard.view` |
| `/api/barangays` | GET | List of all unique barangays | `fisherfolk.view` |
| `/api/fisherfolk` | GET | Paginated fisherfolk list | `fisherfolk.view` |
| `/api/fisherfolk/{id}` | GET | Single fisherfolk details | `fisherfolk.view` |
| `/api/fisherfolk` | POST | Create fisherfolk | `fisherfolk.create` |
| `/api/fisherfolk/{id}` | PUT/PATCH | Update fisherfolk | `fisherfolk.update` |
| `/api/fisherfolk/{id}` | DELETE | Delete fisherfolk | `fisherfolk.delete` |
| `/api/fisherfolk/import` | POST | Bulk import from CSV | `import.create` |
| `/api/users` | GET | List all users | `users.view` |
| `/api/users/{id}` | GET | Single user details | `users.view` |
| `/api/users` | POST | Create user | `users.create` |
| `/api/users/{id}` | PUT/PATCH | Update user | `users.update` |
| `/api/users/{id}` | DELETE | Delete user | `users.delete` |
| `/api/users/{id}/permissions` | GET | Get user permissions | `users.view` |
| `/api/users/{id}/permissions` | PUT | Update permissions | `users.update` |

---

## 📁 Project Structure

```
fmo-fisherfolk-management-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── FisherfolkController.php
│   │   │   ├── UserController.php
│   │   │   ├── PermissionController.php
│   │   │   └── Api/
│   │   │       ├── StatsController.php
│   │   │       └── FisherfolkApiController.php
│   │   ├── Middleware/
│   │   │   ├── CheckPermission.php       # Permission checking middleware
│   │   │   └── CheckModuleAccess.php
│   │   └── Requests/
│   │       ├── StoreFisherfolkRequest.php
│   │       └── StoreUserRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Fisherfolk.php
│   │   └── Permission.php
│   └── Policies/
│       ├── FisherfolkPolicy.php
│       └── UserPolicy.php
│
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_users_table.php
│   │   ├── xxxx_create_fisherfolk_table.php
│   │   └── xxxx_create_permissions_table.php
│   └── seeders/
│       ├── UserSeeder.php
│       └── PermissionSeeder.php
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php             # Main Tailwind layout
│   │   ├── dashboard.blade.php           # Dashboard with Chart.js
│   │   ├── fisherfolk/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   ├── edit.blade.php
│   │   │   └── show.blade.php
│   │   ├── users/
│   │   │   ├── index.blade.php           # User list
│   │   │   ├── create.blade.php          # Create user
│   │   │   ├── edit.blade.php            # Edit user
│   │   │   └── permissions.blade.php     # Permission matrix UI
│   │   └── components/                   # Tailwind components
│   │       ├── permission-toggle.blade.php
│   │       └── stats-card.blade.php
│   └── js/
│       ├── app.js
│       └── charts.js                     # Chart.js initialization
│
├── routes/
│   ├── web.php                           # Web routes with middleware
│   └── api.php                           # API routes
│
├── storage/
│   └── app/
│       └── public/
│           └── uploads/                  # Fisherfolk photos & signatures
│
├── public/
│   ├── build/                            # Vite compiled assets
│   └── storage -> ../storage/app/public  # Symlink
│
├── config/
│   ├── filesystems.php                   # Storage configuration
│   └── permission.php                    # Permission system config
│
├── tailwind.config.js                    # Tailwind CSS configuration
├── vite.config.js                        # Vite build configuration
├── composer.json                         # PHP dependencies
└── package.json                          # NPM dependencies
```

---

## 🎨 Design & UI Guidelines

### Color Scheme (Maritime Theme)
- **Primary Blue:** `#0000FF` (rgb(0, 0, 255))
- **Orange Accent:** `#FFA500` (rgb(255, 165, 0))
- **Gradients:** Blue-to-Orange for headers/footers

### Tailwind CSS Configuration
```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: '#0000FF',
        secondary: '#FFA500',
        'ocean-blue': '#0066CC',
        'sunset-orange': '#FF8C00'
      }
    }
  }
}
```

### Frameworks & Libraries
- **CSS Framework:** Tailwind CSS 3+ (all styling)
- **Icons:** Heroicons (Tailwind default) or Font Awesome 6
- **Charts:** Chart.js 4.4.0
- **UI Components:** Tailwind UI or custom Blade components
- **Forms:** Tailwind CSS forms plugin
- **Tables:** Tailwind CSS tables
- **Modals:** Alpine.js with Tailwind (optional) or Livewire
- **Responsive:** Mobile-first Tailwind breakpoints (sm, md, lg, xl, 2xl)

### Key UI Components (Tailwind-based)

1. **Summary Cards**
   ```html
   <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-primary hover:shadow-lg transition">
     <!-- Card content -->
   </div>
   ```

2. **Charts**
   ```html
   <div class="bg-white rounded-lg shadow-md p-6">
     <canvas id="chartId"></canvas>
   </div>
   ```

3. **Data Tables**
   ```html
   <div class="overflow-x-auto">
     <table class="min-w-full divide-y divide-gray-200">
       <!-- Striped rows with hover effects -->
     </table>
   </div>
   ```

4. **Permission Matrix** (User Management)
   ```html
   <table class="min-w-full">
     <thead>
       <tr>
         <th>User</th>
         <th>Create</th>
         <th>View</th>
         <th>Update</th>
         <th>Delete</th>
       </tr>
     </thead>
     <tbody>
       <tr>
         <td>Module Name</td>
         <td><input type="radio" name="module_create" class="text-primary"></td>
         <td><input type="radio" name="module_view" class="text-primary"></td>
         <td><input type="radio" name="module_update" class="text-primary"></td>
         <td><input type="radio" name="module_delete" class="text-primary"></td>
       </tr>
     </tbody>
   </table>
   ```

5. **Forms**
   - Use Tailwind form classes
   - Validation errors styled with Tailwind
   - Submit buttons with primary/secondary colors

6. **Modals**
   - Image zoom for photos/signatures
   - Confirmation dialogs for delete actions
   - Permission editing modals

---

## 🔧 Technical Implementation Details

### Laravel Setup & Configuration

**Required Packages:**
```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^11.0",
    "laravel/breeze": "^2.0",
    "intervention/image": "^3.0",
    "spatie/laravel-permission": "^6.0" // Optional alternative
  },
  "require-dev": {
    "laravel/pint": "^1.0",
    "laravel/sail": "^1.0"
  }
}
```

**NPM Dependencies:**
```json
{
  "devDependencies": {
    "@tailwindcss/forms": "^0.5",
    "alpinejs": "^3.0",
    "autoprefixer": "^10.0",
    "chart.js": "^4.4",
    "postcss": "^8.0",
    "tailwindcss": "^3.0",
    "vite": "^5.0",
    "laravel-vite-plugin": "^1.0"
  }
}
```

### Permission System Implementation

**Middleware Usage:**
```php
// routes/web.php
Route::middleware(['auth', 'permission:fisherfolk.view'])->group(function () {
    Route::get('/fisherfolk', [FisherfolkController::class, 'index']);
});

Route::middleware(['auth', 'permission:fisherfolk.create'])->group(function () {
    Route::post('/fisherfolk', [FisherfolkController::class, 'store']);
});
```

**Permission Check in Blade:**
```blade
@can('fisherfolk.create')
    <a href="{{ route('fisherfolk.create') }}" class="bg-primary text-white px-4 py-2 rounded">
        Add Fisherfolk
    </a>
@endcan
```

**Permission Model Relationship:**
```php
// User.php
public function permissions()
{
    return $this->hasMany(Permission::class);
}

public function hasPermission($module, $action)
{
    return $this->permissions()
        ->where('module', $module)
        ->where("can_{$action}", true)
        ->exists();
}
```

### File Storage (Laravel)

**Storage Configuration:**
```php
// config/filesystems.php
'disks' => [
    'uploads' => [
        'driver' => 'local',
        'root' => storage_path('app/public/uploads'),
        'url' => env('APP_URL').'/storage/uploads',
        'visibility' => 'public',
    ],
]
```

**Image Upload:**
```php
if ($request->hasFile('image')) {
    $path = $request->file('image')->store('uploads', 'public');
    $fisherfolk->image = basename($path);
}
```

**Creating Storage Symlink:**
```bash
php artisan storage:link
```

### Image Handling (Critical!)

**Problem:** File extensions are case-sensitive on Linux servers.

**Laravel Solution:**
```php
// Fisherfolk Model
public function getImageUrlAttribute()
{
    return $this->image 
        ? asset('storage/uploads/' . $this->image)
        : asset('images/placeholder.png');
}

public function getSignatureUrlAttribute()
{
    return $this->signature
        ? asset('storage/uploads/' . $this->signature)
        : asset('images/signature-placeholder.png');
}
```

**File Naming Convention:**
- Database stores: `"21-175205000-04028.JPG"` (uppercase extension)
- Actual files must match exactly: `21-175205000-04028.JPG`
- If files are lowercase (`.jpg`), rename them to uppercase (`.JPG`)
- Use Laravel Storage facade for file operations

### Chart.js Configuration (Vite Integration)

**Import in resources/js/app.js:**
```javascript
import Chart from 'chart.js/auto';
window.Chart = Chart;
```

**Or separate charts.js file:**
```javascript
// resources/js/charts.js
import Chart from 'chart.js/auto';

const PRIMARY_COLOR = 'rgb(0, 0, 255)';
const SECONDARY_COLOR = 'rgb(255, 165, 0)';

export async function initCharts() {
    const summaryData = await fetch('/api/stats/summary').then(r => r.json());
    // Initialize charts...
}
```

**Include in Blade:**
```blade
@vite(['resources/js/app.js', 'resources/js/charts.js'])
```

### User Management Permission Matrix

**Blade Component Example:**
```blade
<!-- resources/views/users/permissions.blade.php -->
<form method="POST" action="{{ route('users.permissions.update', $user) }}">
    @csrf
    @method('PUT')
    
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3">Module</th>
                <th class="px-6 py-3">Create</th>
                <th class="px-6 py-3">View</th>
                <th class="px-6 py-3">Update</th>
                <th class="px-6 py-3">Delete</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($modules as $module)
            <tr>
                <td class="px-6 py-4">{{ ucfirst($module) }}</td>
                <td class="px-6 py-4">
                    <input type="checkbox" 
                           name="permissions[{{ $module }}][create]"
                           class="rounded text-primary focus:ring-primary"
                           {{ $user->hasPermission($module, 'create') ? 'checked' : '' }}>
                </td>
                <td class="px-6 py-4">
                    <input type="checkbox" 
                           name="permissions[{{ $module }}][view]"
                           class="rounded text-primary focus:ring-primary"
                           {{ $user->hasPermission($module, 'view') ? 'checked' : '' }}>
                </td>
                <td class="px-6 py-4">
                    <input type="checkbox" 
                           name="permissions[{{ $module }}][update]"
                           class="rounded text-primary focus:ring-primary"
                           {{ $user->hasPermission($module, 'update') ? 'checked' : '' }}>
                </td>
                <td class="px-6 py-4">
                    <input type="checkbox" 
                           name="permissions[{{ $module }}][delete]"
                           class="rounded text-primary focus:ring-primary"
                           {{ $user->hasPermission($module, 'delete') ? 'checked' : '' }}>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <button type="submit" class="mt-4 bg-primary text-white px-6 py-2 rounded hover:bg-blue-700">
        Update Permissions
    </button>
</form>
```

---

## 🚀 Deployment

### Development Setup

```bash
# Clone repository
git clone <repo-url>
cd fmo-fisherfolk-management-system

# Install PHP dependencies
composer install

# Install NPM dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Create storage symlink
php artisan storage:link

# Build assets
npm run dev

# Start development server
php artisan serve
```

Access at: `http://localhost:8000`

### Production Deployment

**Build for production:**
```bash
npm run build
```

**Optimize Laravel:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

**Server Requirements:**
- PHP 8.2+
- MySQL 8.0+
- Composer 2.x
- Node.js 18+ & NPM
- Apache/Nginx with mod_rewrite

**Apache Configuration:**
```apache
<VirtualHost *:80>
    ServerName fisherfolk.calapancity.gov.ph
    DocumentRoot /var/www/fmo-fisherfolk/public
    
    <Directory /var/www/fmo-fisherfolk/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**File Permissions:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 📝 Common Data Patterns

### Activity Categories
Fisherfolk can have multiple categories (boolean flags):
- **Boat Owner/Operator:** `boat_owneroperator = 1`
- **Capture Fishing:** `capture_fishing = 1`
- **Gleaning:** `gleaning = 1`
- **Vendor:** `vendor = 1`
- **Fish Processing:** `fish_processing = 1`
- **Aquaculture:** `aquaculture = 1`

Display as colored badges in the UI.

### Barangays
Stored in `address` field as plain text (e.g., "WAWA", "SANTA ISABEL").
Use `DISTINCT address` to get unique barangay list.

### Age Calculation
```sql
SELECT 
    TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age
FROM fisherfolk
```

Age groups: 18-25, 26-35, 36-45, 46-55, 56-65, 66+

---

## ⚠️ Critical Considerations

### For AI Agents Working on This Project:

1. **Laravel 11+ Framework:** Use latest Laravel conventions, Eloquent ORM, and Blade templating.

2. **Tailwind CSS Only:** All styling must use Tailwind utility classes. No Bootstrap or custom CSS files.

3. **File Extensions:** Always check case sensitivity. Database has `.JPG` (uppercase), files must match.

4. **Image Paths (Laravel Storage):**
   - Stored in DB as filename only: `"image123.JPG"`
   - Actual location: `storage/app/public/uploads/image123.JPG`
   - URL reference: `asset('storage/uploads/image123.JPG')`
   - Use `Storage::disk('public')` for file operations

5. **Primary Key:** `id_number` is a string (VARCHAR), not auto-increment INT for fisherfolk table.

6. **Multiple Categories:** A fisherfolk can be a "Boat Owner" AND "Capture Fishing" simultaneously.

7. **API Format:** All endpoints return `{"success": true/false, "data": [...], "message": "..."}` JSON.

8. **Permission-Based Access:** Every route must check permissions via middleware or policies.

9. **User Permissions:** Granular CRUD permissions per module. Use checkboxes/radio buttons in UI.

10. **Responsive Design:** Tailwind mobile-first. Must work on tablets (fisheries officers use tablets).

11. **Chart.js Integration:** Import via NPM, bundle with Vite, not CDN.

12. **Vite Asset Building:** Use `@vite` directive, not `<script src>` tags for JS/CSS.

13. **Blade Components:** Create reusable Tailwind components for cards, tables, modals, forms.

14. **Form Validation:** Use Laravel Form Requests with Tailwind error styling.

15. **Database Migrations:** Always use migrations for schema changes. Never manual SQL.

---

## 🎯 Typical Development Tasks

When asked to work on this project, you might:

### Add New Module/Page
1. Create migration for permissions table entry
2. Create controller with CRUD methods
3. Define routes in `routes/web.php` with permission middleware
4. Create Blade views using Tailwind components
5. Add permission checks in Blade with `@can` directive
6. Update permission seeder to include new module
7. Test with different user permission levels

### Build User Management Interface
1. Create `UserController` with index, create, edit, destroy methods
2. Create `PermissionController` for permission matrix
3. Design permission matrix UI with Tailwind table
4. Implement checkboxes for each module × permission type
5. Add AJAX for real-time permission updates
6. Create user role templates (Admin, Editor, Viewer)
7. Add user activation/deactivation toggle

### Add New Chart
1. Create API endpoint in `StatsController`
2. Add chart container in dashboard Blade view
3. Initialize chart in `resources/js/charts.js`
4. Use maritime color scheme (blue/orange)
5. Make responsive with Tailwind classes
6. Add permission check for chart visibility

### Implement Fisherfolk CRUD
1. Create `FisherfolkController` with resource methods
2. Define routes with appropriate permission middleware
3. Create Blade views (index, create, edit, show) with Tailwind
4. Implement image upload with Laravel Storage
5. Add validation rules in Form Request
6. Create Eloquent model with relationships
7. Add permission-based buttons (Create/Edit/Delete)

### Fix Image Display Issues
1. Check file exists in `storage/app/public/uploads/`
2. Verify storage symlink: `php artisan storage:link`
3. Check filename case matches database exactly
4. Test accessor methods in Fisherfolk model
5. Verify `FILESYSTEM_DISK=public` in `.env`

### Add Permission Check
1. Create middleware if not exists
2. Add to route: `->middleware('permission:module.action')`
3. Add Blade directive: `@can('module.action')`
4. Test with user without permission (should see 403)
5. Update seeder to grant permission to test users

### Style with Tailwind
1. Use utility classes only (no custom CSS)
2. Follow mobile-first responsive design
3. Use Tailwind forms plugin for inputs
4. Implement hover/focus states
5. Use maritime color palette from config
6. Create reusable Blade components

---

## 📚 Reference Links

- **Laravel 11 Docs:** https://laravel.com/docs/11.x
- **Tailwind CSS:** https://tailwindcss.com/docs
- **Chart.js Docs:** https://www.chartjs.org/docs/latest/
- **Laravel Blade:** https://laravel.com/docs/11.x/blade
- **Laravel Eloquent:** https://laravel.com/docs/11.x/eloquent
- **Laravel Middleware:** https://laravel.com/docs/11.x/middleware
- **Laravel Storage:** https://laravel.com/docs/11.x/filesystem
- **Laravel Validation:** https://laravel.com/docs/11.x/validation
- **Tailwind Forms:** https://github.com/tailwindlabs/tailwindcss-forms
- **Heroicons:** https://heroicons.com/
- **Alpine.js:** https://alpinejs.dev/ (optional for interactivity)

---

## 🏁 Quick Start Commands

```bash
# Initial Setup
composer create-project laravel/laravel fmo-fisherfolk-management-system
cd fmo-fisherfolk-management-system

# Install dependencies
composer require laravel/breeze intervention/image
composer require --dev laravel/pint
npm install -D tailwindcss @tailwindcss/forms alpinejs chart.js

# Setup authentication with Tailwind
php artisan breeze:install blade
npm install && npm run build

# Database setup
php artisan migrate
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=PermissionSeeder

# Create storage symlink
php artisan storage:link

# Start development server
php artisan serve
npm run dev

# Access application
# Dashboard: http://localhost:8000/dashboard
# Login: http://localhost:8000/login
# Users: http://localhost:8000/users

# Production build
npm run build
php artisan optimize

# Clear caches (development)
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## 🎓 Understanding This Codebase

This is a **full-stack Laravel application** with:

### What It IS:
- ✅ **Laravel 11+ MVC Application** with Eloquent ORM
- ✅ **User Authentication & Authorization** system
- ✅ **Granular Permission Management** (CRUD per module)
- ✅ **Data Visualization Dashboard** with Chart.js
- ✅ **CRUD Operations** for fisherfolk records
- ✅ **User Management Interface** with permission matrix
- ✅ **RESTful API** for statistics and data
- ✅ **File Upload Management** for photos/signatures
- ✅ **Tailwind CSS** for all UI/UX
- ✅ **Responsive Design** for mobile/tablet access
- ✅ **CSV Import** functionality

### What It's NOT:
- ❌ Not a static HTML/CSS/JS site
- ❌ Not using Bootstrap or other CSS frameworks
- ❌ Not a public-facing website (requires authentication)
- ❌ Not a REST-only API (has full web interface)
- ❌ Not using vanilla PHP (uses Laravel framework)

### Key Architectural Decisions:

**1. Permission System:**
- Flexible, granular permissions per user
- Each user can have different CRUD permissions for each module
- No rigid roles (Super Admin, Admin, etc.) - permission matrix instead
- Checkbox/radio button UI for easy permission assignment

**2. Tailwind-First Approach:**
- All styling via Tailwind utility classes
- No custom CSS files
- Reusable Blade components
- Mobile-first responsive design

**3. Laravel Best Practices:**
- Controllers for business logic
- Models for data layer (Eloquent)
- Blade for views/templates
- Middleware for authorization
- Form Requests for validation
- Policies for resource authorization (optional)

**4. File Storage:**
- Laravel Storage facade
- Public disk for fisherfolk images
- Storage symlink for public access
- Database stores filenames, not full paths

**Target Users:**
- **Fisheries Officers:** View and analyze data, create reports
- **Administrators:** Full CRUD access, user management
- **Data Entry Staff:** Create and update fisherfolk records
- **Viewers:** Read-only access to dashboard and reports

---

**Last Updated:** December 4, 2025  
**Version:** 2.0 (Laravel Migration)  
**Status:** In Development - Migrating from vanilla PHP to Laravel 11+
