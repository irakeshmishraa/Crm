# DirectAdmin Deployment Guide - SmartLead CRM Pro

## Complete Step-by-Step Deployment on DirectAdmin Hosting

---

## Prerequisites

- DirectAdmin hosting panel access
- PHP 8.2+ with required extensions
- MySQL 8+ database
- FTP/SFTP access or File Manager
- Domain pointed to hosting server

---

## Step 1: Create MySQL Database

1. Login to **DirectAdmin** panel
2. Go to **Account Manager** → **MySQL Management**
3. Click **Create New Database**
4. Enter database name: `smartlead_crm`
5. Create a database user with a strong password
6. The database will be created as: `yourusername_smartlead_crm`
7. The user will be: `yourusername_dbuser`

> **Note:** DirectAdmin prefixes database names and users with your account username automatically.

---

## Step 2: Upload Files

### Option A: File Manager (Recommended for beginners)

1. Go to **System Info & Files** → **File Manager**
2. Navigate to `/home/yourusername/domains/yourdomain.com/`
3. You'll see `public_html/` folder
4. **Upload** the project ZIP file to the home directory
5. **Extract** the ZIP file
6. Organize files as shown in Step 3

### Option B: FTP/SFTP Upload

1. Connect via FTP client (FileZilla recommended)
   - Host: `yourdomain.com`
   - Port: `21` (FTP) or `22` (SFTP)
   - Username: Your DirectAdmin username
   - Password: Your DirectAdmin password
2. Navigate to `/home/yourusername/domains/yourdomain.com/`
3. Upload files as described in Step 3

### Option C: SSH (Advanced)

1. Go to **Account Manager** → **SSH Keys** (enable SSH if needed)
2. Connect: `ssh username@yourdomain.com`
3. Navigate to your domain folder:
   ```bash
   cd /home/yourusername/domains/yourdomain.com/
   ```

---

## Step 3: File Structure Setup

DirectAdmin uses this structure:
```
/home/yourusername/domains/yourdomain.com/
├── public_html/          ← Document root (public/ contents go HERE)
│   ├── index.php         ← Modified entry point
│   ├── .htaccess
│   ├── css/
│   ├── js/
│   ├── images/
│   ├── uploads/
│   ├── manifest.json
│   └── sw.js
├── private/              ← Create this folder for app files
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   ├── artisan
│   └── composer.json
```

### Steps:

1. Create a folder called `private` (or `crm_app`) in your domain root:
   ```
   /home/yourusername/domains/yourdomain.com/private/
   ```

2. Upload all application files (everything except `public/` folder contents) into `private/`

3. Upload contents of the `public/` folder into `public_html/`

---

## Step 4: Modify public_html/index.php

Edit `/home/yourusername/domains/yourdomain.com/public_html/index.php`:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Point to the private application directory
if (file_exists($maintenance = __DIR__.'/../private/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../private/vendor/autoload.php';

(require_once __DIR__.'/../private/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

---

## Step 5: Install Composer Dependencies

### If SSH is available:

```bash
cd /home/yourusername/domains/yourdomain.com/private/
php -d memory_limit=-1 /usr/local/bin/composer install --optimize-autoloader --no-dev
```

### If no SSH:

1. Install dependencies locally on your computer first:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```
2. Upload the entire `vendor/` folder to `private/vendor/` via FTP

---

## Step 6: Configure Environment (.env)

Create/edit `/home/yourusername/domains/yourdomain.com/private/.env`:

```env
APP_NAME="SmartLead CRM Pro"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=yourusername_smartlead_crm
DB_USERNAME=yourusername_dbuser
DB_PASSWORD=your_strong_password

CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465
MAIL_USERNAME=info@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@yourdomain.com
MAIL_FROM_NAME="SmartLead CRM"

GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback
```

---

## Step 7: Set File Permissions

### Via SSH:
```bash
cd /home/yourusername/domains/yourdomain.com/private/

chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 644 .env

# Create required directories
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
```

### Via File Manager:
1. Navigate to `private/storage/`
2. Right-click → **Set Permissions** → `755` (recursive)
3. Do the same for `private/bootstrap/cache/`

---

## Step 8: Generate App Key & Run Migrations

### Via SSH:
```bash
cd /home/yourusername/domains/yourdomain.com/private/

php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link --relative
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Without SSH (Use Web Installer):
1. Navigate to `https://yourdomain.com/install`
2. Follow the installation wizard
3. It will handle database setup and admin creation

---

## Step 9: Create Storage Symlink

### Via SSH:
```bash
ln -s /home/yourusername/domains/yourdomain.com/private/storage/app/public /home/yourusername/domains/yourdomain.com/public_html/storage
```

### Via File Manager:
If symlinks aren't supported, create a `public_html/storage/` folder and copy files manually, or add this to `public_html/.htaccess`:

```apache
# Storage redirect
RewriteRule ^storage/(.*)$ /private/storage/app/public/$1 [L]
```

---

## Step 10: Setup Cron Jobs

1. Go to **Advanced Features** → **Cron Jobs** in DirectAdmin
2. Add a new cron job:
   - **Minute:** `*` (every minute)
   - **Hour:** `*`
   - **Day of Month:** `*`
   - **Month:** `*`
   - **Day of Week:** `*`
   - **Command:**
     ```
     /usr/local/bin/php /home/yourusername/domains/yourdomain.com/private/artisan schedule:run >> /dev/null 2>&1
     ```

> **Note:** Check the correct PHP path. It might be:
> - `/usr/local/bin/php`
> - `/usr/bin/php`
> - `/usr/local/bin/php82`
> - `/usr/local/bin/ea-php82`
>
> Run `which php` via SSH or check DirectAdmin PHP configuration.

---

## Step 11: Configure PHP Version

1. Go to **Account Manager** → **Domain Setup** or **PHP Version Select**
2. Select **PHP 8.2** or **PHP 8.3** for your domain
3. Ensure these extensions are enabled:
   - bcmath
   - ctype
   - curl
   - dom
   - fileinfo
   - gd
   - json
   - mbstring
   - openssl
   - pdo
   - pdo_mysql
   - tokenizer
   - xml
   - zip

---

## Step 12: SSL Certificate

### Let's Encrypt (Free):
1. Go to **Account Manager** → **SSL Certificates**
2. Click **Free & Automatic Certificate from Let's Encrypt**
3. Check your domain and `www` subdomain
4. Click **Save**

### Force HTTPS:
Add to `public_html/.htaccess` (before the Laravel rules):

```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Step 13: Queue Worker (Optional)

For shared hosting without supervisor, use cron-based queue:

Add another cron job:
```
* * * * * /usr/local/bin/php /home/yourusername/domains/yourdomain.com/private/artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

---

## Complete .htaccess for public_html

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Remove www (optional)
    # RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
    # RewriteRule ^(.*)$ https://%1/$1 [R=301,L]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Disable directory listing
Options -Indexes

# Block access to sensitive files
<FilesMatch "\.(env|log|yml|yaml|json|lock|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## Troubleshooting

### 500 Internal Server Error
```bash
# Check Laravel logs
cat /home/yourusername/domains/yourdomain.com/private/storage/logs/laravel.log | tail -50

# Check Apache error logs (via DirectAdmin)
# Go to: Account Manager → Error Log
```

### Permission Denied
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R yourusername:yourusername storage/
chown -R yourusername:yourusername bootstrap/cache/
```

### Blank White Page
- Ensure `APP_DEBUG=true` temporarily to see errors
- Check PHP version is 8.2+
- Verify all required PHP extensions

### Database Connection Error
- Verify database credentials in `.env`
- Remember DirectAdmin prefixes: `username_dbname`
- Check if MySQL user has all privileges on the database

### "Class not found" Errors
```bash
cd /home/yourusername/domains/yourdomain.com/private/
php artisan clear-compiled
composer dump-autoload --optimize
```

### Storage Link Issues
```bash
# Remove broken link
rm -f /home/yourusername/domains/yourdomain.com/public_html/storage

# Create new link
ln -s /home/yourusername/domains/yourdomain.com/private/storage/app/public /home/yourusername/domains/yourdomain.com/public_html/storage
```

### Cron Not Running
- Check PHP path: `which php` or `which php82`
- Test manually: `/usr/local/bin/php /path/to/artisan schedule:run`
- Check cron logs in DirectAdmin

---

## Performance Optimization

```bash
# After deployment, run these:
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Clear all caches (if needed):
php artisan optimize:clear
```

---

## Updating the Application

```bash
# Put in maintenance mode
php artisan down

# Upload new files (skip vendor/ and .env)

# Install new dependencies
composer install --optimize-autoloader --no-dev

# Run new migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan optimize:clear
php artisan optimize

# Bring back online
php artisan up
```

---

## Security Checklist

- [x] `APP_DEBUG=false` in production
- [x] Strong database password
- [x] SSL enabled (HTTPS)
- [x] Proper file permissions (755 for directories, 644 for files)
- [x] `.env` file not accessible from web
- [x] `storage/` not directly accessible
- [x] Security headers in .htaccess
- [x] Rate limiting enabled
- [x] 2FA enabled for admin accounts
- [x] Regular database backups via CRM backup feature

---

## DirectAdmin vs cPanel Differences

| Feature | DirectAdmin | cPanel |
|---------|-------------|--------|
| File Structure | `/domains/domain.com/public_html/` | `/public_html/` |
| PHP Selection | Domain Setup → PHP Version | MultiPHP Manager |
| Cron Jobs | Advanced → Cron Jobs | Cron Jobs |
| SSL | SSL Certificates | SSL/TLS |
| Database | MySQL Management | MySQL Databases |
| File Manager | System Info → File Manager | File Manager |
| Logs | Error Log | Error Log |

---

## Need Help?

- Check `storage/logs/laravel.log` for application errors
- Check DirectAdmin error logs for server errors
- Ensure PHP version compatibility
- Verify all file paths match your DirectAdmin setup
