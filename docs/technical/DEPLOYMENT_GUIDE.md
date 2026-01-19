# PA Penajam Website - Deployment Guide

## Overview

This guide covers deploying the PA Penajam website from development to production environment.

---

## Table of Contents

1. [Server Requirements](#server-requirements)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Deployment Methods](#deployment-methods)
4. [Production Environment Setup](#production-environment-setup)
5. [Database Setup](#database-setup)
6. [Asset Building](#asset-building)
7. [Web Server Configuration](#web-server-configuration)
6. [SSL Configuration](#ssl-configuration)
7. [Queue Workers](#queue-workers)
8. [Monitoring & Logging](#monitoring--logging)
9. [Backup Strategy](#backup-strategy)
10. [Rollback Procedures](#rollback-procedures)

---

## Server Requirements

### Minimum Requirements

- **PHP:** 8.5 or higher
- **Database:** MySQL 8.0+ or PostgreSQL 12+
- **Web Server:** Nginx 1.18+ or Apache 2.4+
- **RAM:** 2GB minimum (4GB recommended)
- **Storage:** 20GB minimum
- **PHP Extensions:**
  - bcrypt
  - ctype
  - cURL
  - dom
  - fileinfo
  - mbstring
  - openssl
  - pcntl
  - pdo
  - tokenizer
  - xml
  - json
  - bcmath

### Recommended Requirements

- **PHP:** 8.5
- **Database:** MySQL 8.0+
- **Web Server:** Nginx
- **RAM:** 4GB
- **Storage:** 50GB SSD
- **Redis:** For caching and queues
- **Supervisor:** For queue workers

### Optional Software

- **Composer:** 2.x
- **Node.js:** 20.x
- **npm:** 10.x
- **Git:** 2.x

---

## Pre-Deployment Checklist

### Code Quality

- [ ] All tests pass (`php artisan test --compact`)
- [ ] Code formatted with Laravel Pint (`vendor/bin/pint`)
- [ ] No console errors or warnings
- [ ] Database migrations tested
- [ ] Environment variables configured

### Security

- [ ] `APP_KEY` set with strong encryption key
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Force HTTPS enabled
- [ ] Secure database credentials
- [ ] CORS configured properly
- [ ] File permissions set correctly

### Performance

- [ ] Opcode cache enabled (OPcache)
- [ ] Database queries optimized
- [ ] N+1 queries eliminated
- [ ] Redis configured for cache
- [ ] Queue workers set up
- [ ] CDN configured (if using)

### Content

- [ ] Joomla data migrated
- [ ] Initial content created
- [ ] Default admin user created
- [ ] Settings configured
- [ ] Email templates configured

---

## Deployment Methods

### Method 1: Manual Deployment (Git)

#### Step 1: Clone Repository

```bash
# SSH into server
ssh user@server

# Navigate to web directory
cd /var/www

# Clone repository
git clone https://github.com/your-org/web-papenajam.git
cd web-papenajam
```

#### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm ci
```

#### Step 3: Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

Configure `.env`:
```env
APP_NAME="PA Penajam"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://pa-penajam.go.id

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pa_penajam_prod
DB_USERNAME=pa_penajam_user
DB_PASSWORD=secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@pa-penajam.go.id"
MAIL_FROM_NAME="${APP_NAME}"

# SIPP API Configuration
SIPP_API_URL=https://sipp.example.com/api
SIPP_API_KEY=your_api_key_here
SIPP_API_VERSION=v1

# Filament Configuration
FILAMENT_FILESYSTEM_DISK=public
```

#### Step 4: Build Assets

```bash
# Build production assets
npm run build

# Clear and cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 5: Database Setup

```bash
# Run migrations
php artisan migrate --force

# Seed database (if needed)
php artisan db:seed --force
```

#### Step 6: Set Permissions

```bash
# Set storage and cache permissions
chmod -R 775 storage bootstrap/cache

# Change ownership to web server user
chown -R www-data:www-data storage bootstrap/cache

# Set proper permissions for entire directory
chmod -R 755 /var/www/web-papenajam
```

#### Step 7: Configure Web Server

See [Web Server Configuration](#web-server-configuration) section.

---

### Method 2: Deployer (Automated)

#### Install Deployer

```bash
# Install Deployer globally
composer global require deployer/deployer
```

#### Create Deployer Script

Create `deploy.php` in project root:

```php
<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config
set('application', 'PA Penajam Website');
set('repository', 'git@github.com:your-org/web-papenajam.git');
set('php_version', '8.5');
set('keep_releases', 3);

// Servers
host('production')
    ->set('remote_user', 'deploy')
    ->set('hostname', 'pa-penajam.go.id')
    ->set('deploy_path', '/var/www/web-papenajam');

// Tasks
task('build', function () {
    cd('{{release_path}}');
    run('npm ci');
    run('npm run build');
});

task('deploy:permissions', function () {
    run('chmod -R 755 {{release_path}}');
    run('chown -R www-data:www-data {{release_path}}/storage');
    run('chown -R www-data:www-data {{release_path}}/bootstrap/cache');
});

after('deploy:code', 'build');
after('deploy', 'deploy:permissions');
```

#### Deploy

```bash
# Deploy to production
dep deploy production
```

---

### Method 3: Docker (Laravel Sail)

#### Production Dockerfile

Create `Dockerfile.prod`:

```dockerfile
FROM php:8.5-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY . /var/www

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN composer install --optimize-autoloader --no-dev
RUN npm ci && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

CMD ["php-fpm"]
```

#### Build and Run

```bash
# Build image
docker build -f Dockerfile.prod -t pa-penajam:latest .

# Run container
docker run -d \
  --name pa-penajam-web \
  -p 9000:9000 \
  -v /var/www/storage:/var/www/storage \
  pa-penajam:latest
```

---

## Production Environment Setup

### Database Setup

#### Create MySQL Database

```sql
-- Create database
CREATE DATABASE pa_penajam_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'pa_penajam_user'@'localhost' IDENTIFIED BY 'secure_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON pa_penajam_prod.* TO 'pa_penajam_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;
```

#### Import Data (if migrating from Joomla)

```bash
# Import Joomla data via migration command
php artisan joomla:migrate
```

### Redis Setup

#### Install Redis

```bash
# Ubuntu/Debian
sudo apt-get install redis-server

# Start Redis
sudo systemctl start redis
sudo systemctl enable redis
```

#### Configure Laravel

In `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## Web Server Configuration

### Nginx Configuration

Create `/etc/nginx/sites-available/pa-penajam`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name pa-penajam.go.id www.pa-penajam.go.id;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name pa-penajam.go.id www.pa-penajam.go.id;

    root /var/www/web-papenajam/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/pa-penajam.go.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/pa-penajam.go.id/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Logging
    access_log /var/log/nginx/pa-penajam-access.log;
    error_log /var/log/nginx/pa-penajam-error.log;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    # Index File
    index index.php;

    # Location Blocks
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Static Files Caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

#### Enable Site

```bash
# Create symlink
sudo ln -s /etc/nginx/sites-available/pa-penajam /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### Apache Configuration

Create `.htaccess` in `public/` directory (already included in Laravel):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

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
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Gzip Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

---

## SSL Configuration

### Let's Encrypt (Certbot)

#### Install Certbot

```bash
# Ubuntu/Debian
sudo apt-get install certbot python3-certbot-nginx
```

#### Obtain SSL Certificate

```bash
# Obtain and configure SSL
sudo certbot --nginx -d pa-penajam.go.id -d www.pa-penajam.go.id

# Auto-renewal is configured automatically
sudo certbot renew --dry-run
```

### Manual SSL

If using commercial SSL certificates:

1. **Purchase SSL certificate** from provider
2. **Upload certificate files** to server
3. **Configure Nginx:**

```nginx
ssl_certificate /path/to/certificate.crt;
ssl_certificate_key /path/to/private.key;
ssl_certificate_chain /path/to/chain.crt;
```

---

## Queue Workers

### Supervisor Configuration

#### Install Supervisor

```bash
sudo apt-get install supervisor
```

#### Create Queue Worker Configuration

Create `/etc/supervisor/conf.d/pa-penajam-worker.conf`:

```ini
[program:pa-penajam-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/web-penajam/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/web-papenajam/storage/logs/worker.log
stopwaitsecs=3600
```

#### Start Workers

```bash
# Update Supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start pa-penajam-worker-*

# Check status
sudo supervisorctl status
```

---

## Monitoring & Logging

### Laravel Telescope (Development Only)

```bash
# Install Telescope
composer require laravel/telescope --dev

# Publish assets
php artisan telescope:install

# Run migrations
php artisan migrate
```

### Production Monitoring

#### Error Tracking (Sentry)

```bash
# Install Sentry
composer require sentry/sentry-laravel

# Install Sentry integration
php artisan sentry:publish
php artisan sentry:install
```

#### Uptime Monitoring

- Use services like:
  - UptimeRobot
  - Pingdom
  - StatusCake

#### Application Logging

Logs are stored in `storage/logs/laravel.log`.

Monitor logs:
```bash
# View last 100 lines
tail -f storage/logs/laravel.log

# Search for errors
grep "ERROR" storage/logs/laravel.log
```

---

## Backup Strategy

### Database Backups

#### Automated Backup Script

Create `/usr/local/bin/backup-papenajam.sh`:

```bash
#!/bin/bash

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/pa-penajam"
DB_NAME="pa_penajam_prod"
DB_USER="pa_penajam_user"
DB_PASS="secure_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep last 30 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete

echo "Backup completed: db_$DATE.sql.gz"
```

Make executable and add to crontab:
```bash
chmod +x /usr/local/bin/backup-papenajam.sh

# Add to crontab
crontab -e

# Run daily at 2 AM
0 2 * * * /usr/local/bin/backup-papenajam.sh
```

### File Backups

```bash
# Backup storage directory
rsync -avz /var/www/web-papenajam/storage /var/backups/pa-penajam/

# Backup to remote server
rsync -avz -e ssh /var/www/web-papenajam/storage user@backup-server:/backups/
```

### Laravel Backup Package

```bash
# Install spatie/laravel-backup
composer require spatie/laravel-backup

# Publish config
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

# Run backup
php artisan backup:run
```

---

## Rollback Procedures

### Database Rollback

```bash
# Rollback last migration
php artisan migrate:rollback

# Rollback specific number of migrations
php artisan migrate:rollback --step=5

# Rollback all migrations
php artisan migrate:reset
```

### Application Rollback (Git)

```bash
# View recent commits
git log --oneline -10

# Reset to previous commit
git reset --hard HEAD~1

# Reset to specific commit
git reset --hard <commit-hash>
```

### Application Rollback (Deployer)

```bash
# Rollback to previous release
dep rollback production
```

### Emergency Rollback

If critical issues occur:

1. **Enable Maintenance Mode**
   ```bash
   php artisan down
   ```

2. **Restore Database Backup**
   ```bash
   gunzip < /var/backups/pa-penajam/db_YYYYMMDD.sql.gz | mysql -u user -p pa_penajam_prod
   ```

3. **Rollback Code**
   ```bash
   # If using Git
   git checkout previous-release-tag

   # If using Deployer
   dep rollback production
   ```

4. **Clear Caches**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

5. **Restart Services**
   ```bash
   sudo systemctl reload nginx
   sudo systemctl restart php8.5-fpm
   sudo supervisorctl restart pa-penajam-worker-*
   ```

6. **Bring Application Back Up**
   ```bash
   php artisan up
   ```

---

## Performance Tuning

### OPcache Configuration

Edit `/etc/php/8.5/fpm/conf.d/10-opcache.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.enable_cli=1
```

### MySQL Optimization

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_size = 64M
query_cache_type = 1
```

### Nginx Optimization

Add to `http` block in `/etc/nginx/nginx.conf`:

```nginx
# Worker Processes
worker_processes auto;
worker_connections 1024;

# Keep Alive
keepalive_timeout 65;
keepalive_requests 100;

# Buffer Sizes
client_body_buffer_size 128k;
client_max_body_size 20M;
client_header_buffer_size 1k;
large_client_header_buffers 4 16k;
```

---

## Post-Deployment Tasks

### Verify Deployment

```bash
# Check application status
curl -I https://pa-penajam.go.id

# Check SSL certificate
curl -vI https://pa-penajam.go.id 2>&1 | grep -i "ssl"

# Test database connection
php artisan tinker
>>> \DB::connection()->getPdo();
=> PDO { ... }
```

### Smoke Tests

```bash
# Run test suite
php artisan test --compact

# Check queue workers
sudo supervisorctl status pa-penajam-worker-*

# Check cache
php artisan cache:status
```

### Monitor First 24 Hours

- Check error logs regularly
- Monitor server resources
- Verify scheduled tasks running
- Check SIPP sync status
- Monitor queue processing

---

## Maintenance Mode

### Enable Maintenance Mode

```bash
# Enable
php artisan down

# With message
php artisan down --message="Upgrading system"

# With retry time
php artisan down --retry=60

# Allow specific IPs
php artisan down --allow=127.0.0.1 --allow=192.168.1.1
```

### Disable Maintenance Mode

```bash
php artisan up
```

---

## Troubleshooting

### Common Issues

#### 502 Bad Gateway

**Cause:** PHP-FPM not running
**Solution:**
```bash
sudo systemctl restart php8.5-fpm
```

#### 504 Gateway Timeout

**Cause:** Long-running script or slow database
**Solution:**
- Optimize slow queries
- Increase timeout in Nginx config
- Use queue for long tasks

#### Blank Page

**Cause:** PHP error or permission issue
**Solution:**
```bash
# Check permissions
chmod -R 755 /var/www/web-papenajam
chown -R www-data:www-data /var/www/web-papenajam/storage

# Check logs
tail -f storage/logs/laravel.log
```

#### Storage Not Writable

**Cause:** Incorrect permissions
**Solution:**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## Security Checklist

- [ ] SSL certificate installed and configured
- [ ] Force HTTPS enabled
- [ ] Firewall configured (only open necessary ports)
- [ ] Fail2Ban installed and configured
- [ ] Regular security updates applied
- [ ] Strong passwords for database and admin users
- [ ] Debug mode disabled in production
- [ ] Secure file permissions
- [ ] Regular backups configured
- [ ] Monitoring and alerting configured
- [ ] CORS configured properly
- [ ] Rate limiting enabled
- [ ] Input validation enabled

---

## Support & Resources

### Official Documentation
- [Laravel Documentation](https://laravel.com/docs/12.x)
- [Filament Documentation](https://filamentphp.com/docs)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Nginx Documentation](https://nginx.org/en/docs/)

### Community
- [Laravel Forums](https://laracasts.com/discuss)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/laravel)

---

**Document Version:** 1.0.0
**Last Updated:** 2026-01-18
**Maintainer:** Development Team
