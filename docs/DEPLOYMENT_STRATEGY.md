# Deployment Strategy - PA Penajam Website

**Project:** Pengadilan Agama Penajam Website Migration
**Version:** 1.0
**Last Updated:** 2026-01-18
**Status:** Phase 4.4 - Deployment & Migration

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Deployment Architecture](#deployment-architecture)
3. [Server Requirements](#server-requirements)
4. [Deployment Strategy](#deployment-strategy)
5. [Zero-Downtime Deployment](#zero-downtime-deployment)
6. [Rollback Procedures](#rollback-procedures)
7. [Monitoring & Logging](#monitoring--logging)
8. [Security Considerations](#security-considerations)
9. [Backup Strategy](#backup-strategy)
10. [Joomla Migration Strategy](#joomla-migration-strategy)

---

## Executive Summary

This document outlines the comprehensive deployment strategy for migrating the PA Penajam website from Joomla 3 to Laravel 12. The deployment approach prioritizes:

- **Zero downtime** during migration and deployment
- **Data integrity** with comprehensive validation
- **Quick rollback** capabilities in case of issues
- **Automation** to reduce human error
- **Monitoring** for proactive issue detection
- **Security** with best practices at every layer

### Key Metrics

- **Target Downtime:** < 5 minutes (DNS propagation only)
- **Rollback Time:** < 15 minutes
- **Data Migration Window:** 2-4 hours
- **Deployment Frequency:** On-demand (with CI/CD readiness)

---

## Deployment Architecture

### Recommended Approach: Laravel Forge

For PA Penajam, we recommend **Laravel Forge** for production deployment due to:

- **Official Laravel tool** with active support
- **Zero-downtime deployment** built-in
- **Automated SSL** via Let's Encrypt
- **Queue worker management** out of the box
- **Database backup automation**
- **Simple CI/CD integration**
- **Cost-effective** for government websites

### Alternative: Custom Deployment

If Forge is not available, a custom deployment using:

- **Web Server:** Nginx 1.24+
- **PHP:** PHP 8.5 with FPM
- **Database:** MySQL 8.0+ / PostgreSQL 14+
- **Cache:** Redis 7.0+
- **Process Manager:** Supervisor
- **SSL:** Let's Encrypt with certbot

### Infrastructure Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Users                                 │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                    Load Balancer (Optional)                 │
│                   (Cloudflare / HAProxy)                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              Web Server (Nginx + SSL)                       │
│                  pa-penajam.go.id                           │
└────────────────────────┬────────────────────────────────────┘
                         │
         ┌───────────────┼───────────────┐
         │               │               │
         ▼               ▼               ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│ Laravel App  │  │ Queue Worker │  │   Redis      │
│   (PHP 8.5)  │  │  (Supervisor)│  │   (Cache)    │
└──────┬───────┘  └──────────────┘  └──────────────┘
       │
         ┌──────────────┐
         │              │
         ▼              ▼
┌──────────────┐  ┌──────────────┐
│   MySQL 8.0  │  │  File Storage│
│  (Primary)   │  │  (Local/S3)  │
└──────────────┘  └──────────────┘
```

---

## Server Requirements

### Minimum Requirements (Production)

```yaml
# Web Server
CPU: 2 cores
RAM: 4 GB
Storage: 40 GB SSD
OS: Ubuntu 22.04 LTS / Debian 12

# Software Versions
PHP: 8.5
Nginx: 1.24+
MySQL: 8.0+ / PostgreSQL 14+
Redis: 7.0+
Composer: 2.6+
Node.js: 20+
```

### Recommended Requirements (High Traffic)

```yaml
# Web Server
CPU: 4 cores
RAM: 8 GB
Storage: 80 GB SSD
OS: Ubuntu 22.04 LTS

# Database Server (Separate)
CPU: 2 cores
RAM: 4 GB
Storage: 100 GB SSD

# Additional Services
- Separate Redis instance
- CDN for static assets (Cloudflare)
- Backup storage (off-site)
```

### PHP Extensions Required

```bash
php-bcmath
php-cli
php-curl
php-exif
php-fpm
php-gd
php-intl
php-mbstring
php-mysql (or php-pgsql)
php-redis
php-xml
php-zip
php-opcache
```

### Laravel-Specific Requirements

```bash
# PHP Configuration
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 50M
post_max_size = 50M

# OPcache Settings (for production)
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 60
```

---

## Deployment Strategy

### Deployment Method: Git-Based Deployment

We use a **Git-based deployment strategy** with the following workflow:

1. **Development:** Feature branches in Git
2. **Testing:** Merge to staging branch for testing
3. **Production:** Merge to production branch for deployment
4. **Automation:** Deployment scripts handle the rest

### Deployment Phases

#### Phase 1: Pre-Deployment Preparation

1. **Code Review**
   - All code reviewed and approved
   - Tests passing (unit, feature, browser)
   - Laravel Pint formatting applied
   - Security scan completed

2. **Backup**
   - Database backup
   - Files backup
   - Configuration backup

3. **Staging Validation**
   - Deploy to staging environment
   - Run full test suite
   - Manual QA testing
   - Performance testing

#### Phase 2: Deployment Execution

1. **Maintenance Mode** (if needed)
   ```bash
   php artisan down --render="errors/503"
   ```

2. **Pull Latest Code**
   ```bash
   git pull origin production
   ```

3. **Install Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

5. **Clear and Cache**
   ```bash
   php artisan optimize:clear
   php artisan optimize
   ```

6. **Restart Services**
   ```bash
   php artisan queue:restart
   supervisorctl restart all
   ```

7. **Bring Application Back Up**
   ```bash
   php artisan up
   ```

#### Phase 3: Post-Deployment Validation

1. **Health Checks**
   - Check application health endpoint
   - Verify database connections
   - Verify cache connections
   - Verify queue workers

2. **Smoke Tests**
   - Test critical user journeys
   - Test admin panel access
   - test SIPP integration
   - Test file uploads

3. **Monitoring**
   - Check application logs
   - Monitor error rates
   - Monitor response times
   - Monitor queue processing

---

## Zero-Downtime Deployment

### Strategy: Symbolic Link Deployment

For true zero-downtime deployment, we use symbolic link switching:

```bash
# Directory Structure
/var/www/
├── releases/
│   ├── release-20250118-143000/
│   ├── release-20250118-150000/
│   └── release-20250118-160000/
├── shared/
│   ├── storage/
│   ├── .env
│   └── node_modules/
└── current -> releases/release-20250118-160000/
```

### Zero-Downtime Deployment Process

1. **Create New Release**
   ```bash
   mkdir /var/www/releases/release-$(date +%Y%m%d-%H%M%S)
   ```

2. **Clone/Update Code**
   ```bash
   git clone --depth 1 -b production \
     git@github.com:pa-penajam/website.git \
     /var/www/releases/release-$(date +%Y%m%d-%H%M%S)
   ```

3. **Install Dependencies**
   ```bash
   cd /var/www/releases/release-$(date +%Y%m%d-%H%M%S)
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```

4. **Link Shared Resources**
   ```bash
   ln -nfs /var/www/shared/storage /var/www/current/storage
   ln -nfs /var/www/shared/.env /var/www/current/.env
   ```

5. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

6. **Optimize Application**
   ```bash
   php artisan optimize
   ```

7. **Switch Symbolic Link** (atomic operation)
   ```bash
   ln -nfs /var/www/releases/release-$(date +%Y%m%d-%H%M%S) /var/www/current
   ```

8. **Restart PHP-FPM**
   ```bash
   sudo systemctl reload php8.5-fpm
   ```

9. **Cleanup Old Releases** (keep last 5)
   ```bash
   ls -t /var/www/releases/ | tail -n +6 | xargs rm -rf
   ```

### Laravel Forge Zero-Downtime

Laravel Forge handles zero-downtime deployment automatically:

1. **Deploy to new directory**
2. **Run migrations and optimizations**
3. **Atomically switch to new release**
4. **Reload PHP-FPM**
5. **Clean up old releases**

---

## Rollback Procedures

### Immediate Rollback (< 1 minute)

For critical issues requiring immediate rollback:

```bash
# Rollback to previous release (Forge)
cd /var/www/html && rm current && \
  ln -s /var/www/releases/previous-release current

# Or using symbolic link deployment
ln -nfs /var/www/releases/previous-release /var/www/current
sudo systemctl reload php8.5-fpm
```

### Database Rollback

```bash
# Rollback last migration batch
php artisan migrate:rollback --step=1

# Or rollback to specific batch
php artisan migrate:rollback --batch=5
```

### Complete Rollback Procedure

1. **Assess the situation**
   ```bash
   # Check current state
   php artisan about
   tail -f storage/logs/laravel.log
   ```

2. **Put site in maintenance mode**
   ```bash
   php artisan down
   ```

3. **Rollback code**
   ```bash
   # Using Git
   git reset --hard HEAD~1
   git push --force

   # Or rollback release
   ln -nfs /var/www/releases/previous-release /var/www/current
   ```

4. **Rollback database** (if needed)
   ```bash
   php artisan migrate:rollback
   ```

5. **Restore configuration** (if needed)
   ```bash
   cp /var/www/backups/.env.backup /var/www/current/.env
   ```

6. **Clear all caches**
   ```bash
   php artisan optimize:clear
   ```

7. **Restart services**
   ```bash
   sudo systemctl reload php8.5-fpm
   sudo supervisorctl restart all
   ```

8. **Bring site back up**
   ```bash
   php artisan up
   ```

9. **Verify rollback**
   - Test critical functionality
   - Check logs for errors
   - Monitor performance

---

## Monitoring & Logging

### Application Monitoring

#### Laravel Telescope (Development)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

#### Production Monitoring Stack

1. **Laravel Pail** - Real-time log monitoring
   ```bash
   php artisan pail --timeout=0
   ```

2. **Health Check Endpoint**
   ```php
   // routes/web.php
   Route::get('/health', function () {
       return response()->json([
           'status' => 'ok',
           'timestamp' => now()->toIso8601String(),
           'database' => DB::connection()->getPdo() ? 'up' : 'down',
           'cache' => Cache::get('health_check') ? 'up' : 'down',
       ]);
   });
   ```

3. **Uptime Monitoring** - External services
   - UptimeRobot (free)
   - Pingdom
   - StatusCake

### Log Management

#### Log Channels (config/logging.php)

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
        'ignore_exceptions' => false,
    ],
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
        'days' => 14,
    ],
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'critical',
    ],
],
```

### Performance Monitoring

#### Laravel Debugbar (Development Only)

```bash
composer require barryvdh/laravel-debugbar --dev
```

#### Production Performance Tracking

1. **Query Logging** (temporary)
   ```php
   DB::enableQueryLog();
   // ... run queries
   dd(DB::getQueryLog());
   ```

2. **Laravel Telescope** (selective)
   ```php
   'watchers' => [
       Watchers\QueryWatcher::class => true,
       Watchers\RequestWatcher::class => true,
   ],
   ```

3. **APM Solutions** (recommended for production)
   - New Relic
   - Datadog
   - Laravel Pulse (official)

### Error Tracking

#### Bugsnag Integration

```bash
composer require bugsnag/bugsnag-laravel
```

#### Sentry Integration

```bash
composer require sentry/sentry-laravel
```

---

## Security Considerations

### Web Server Security

#### Nginx Configuration

```nginx
# Hide Nginx version
server_tokens off;

# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

# Limit request size
client_max_body_size 50M;
```

### Application Security

#### Environment Configuration

```env
# Production Settings
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-generated-key-here

# Force HTTPS
APP_URL=https://pa-penajam.go.id

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pa_penajam_prod
DB_USERNAME=pa_penajam_user
DB_PASSWORD=strong-password-here

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379
```

#### Security Best Practices

1. **HTTPS Only**
   - SSL certificate (Let's Encrypt)
   - Force HTTPS redirect
   - HSTS enabled

2. **File Permissions**
   ```bash
   # Storage and cache directories
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache

   # All other directories
   sudo chown -R www-data:www-data .
   sudo find . -type f -exec chmod 644 {} \;
   sudo find . -type d -exec chmod 755 {} \;
   ```

3. **Firewall Configuration**
   ```bash
   # UFW (Uncomplicated Firewall)
   sudo ufw allow 22/tcp    # SSH
   sudo ufw allow 80/tcp    # HTTP
   sudo ufw allow 443/tcp   # HTTPS
   sudo ufw enable
   ```

4. **Security Headers**
   - X-Frame-Options
   - X-Content-Type-Options
   - X-XSS-Protection
   - Content-Security-Policy
   - Strict-Transport-Security

5. **Regular Updates**
   ```bash
   # System updates
   sudo apt update && sudo apt upgrade -y

   # PHP dependencies
   composer update

   # Node dependencies
   npm update
   ```

### Authentication & Authorization

1. **Laravel Fortify** - Authentication backend
2. **Two-Factor Authentication** - Enabled for admins
3. **Role-Based Access Control** - 5 role levels
4. **Rate Limiting** - Prevent brute force attacks
5. **Password Requirements** - Strong password policy

---

## Backup Strategy

### Database Backups

#### Automated Daily Backups

```bash
# /etc/cron.d/laravel-backup
0 2 * * * www-data cd /var/www/current && php artisan backup:run --only-db
```

#### Manual Backup

```bash
# MySQL dump
mysqldump -u pa_penajam_user -p pa_penajam_prod > \
  backup_$(date +%Y%m%d_%H%M%S).sql

# Or using Laravel
php artisan backup:run --only-db
```

### File Backups

#### What to Backup

- `storage/app/` - User uploads
- `.env` - Configuration
- `database/` - Custom migrations

#### Backup Script

```bash
#!/bin/bash
# backup-files.sh

BACKUP_DIR="/var/backups/pa-penajam"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p "$BACKUP_DIR/$DATE"

# Backup storage
tar -czf "$BACKUP_DIR/$DATE/storage.tar.gz" /var/www/current/storage/app

# Backup .env
cp /var/www/current/.env "$BACKUP_DIR/$DATE/.env"

# Keep last 30 days
find "$BACKUP_DIR" -type d -mtime +30 -exec rm -rf {} \;
```

### Off-Site Backups

#### S3 Backup Configuration

```env
# .env
BACKUP_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=pa-penajam-backups
```

```php
// config/backup.php
'destination' => [
    'filename_prefix' => '',
    'disks' => [
        's3',
    ],
],
```

---

## Joomla Migration Strategy

### Migration Approach

We use a **phased migration approach** to minimize risk:

#### Phase 1: Preparation (Day 1)

1. **Export Joomla Data**
   ```bash
   # From Joomla server
   mysqldump -u joomla_user -p joomla_db > joomla_export.sql
   ```

2. **Validate Export**
   - Check record counts
   - Verify data integrity
   - Test import on staging

3. **Clean Joomla Data**
   - Remove spam content
   - Fix broken images
   - Standardize formatting

#### Phase 2: Migration Execution (Day 2-3)

1. **Run Migration Scripts**
   ```bash
   # On Laravel application
   php artisan joomla:migrate:content
   php artisan joomla:migrate:categories
   php artisan joomla:migrate:menu
   php artisan joomla:migrate:media
   ```

2. **Validate Migrated Data**
   - Compare record counts
   - Spot-check content quality
   - Verify images and links
   - Test user accounts

3. **Fix Migration Issues**
   - Manual data cleanup
   - Fix broken relationships
   - Update redirects

#### Phase 3: Testing (Day 4)

1. **User Acceptance Testing**
   - Admin panel testing
   - Frontend testing
   - SIPP integration testing
   - Performance testing

2. **Go/No-Go Decision**
   - Stakeholder approval
   - Final data validation
   - Performance benchmarks

### Migration Timeline

```
Day 1 (08:00 - 17:00):
  - Export Joomla data (2 hours)
  - Validate and clean data (4 hours)
  - Prepare migration scripts (2 hours)

Day 2 (08:00 - 17:00):
  - Run content migration (4 hours)
  - Run media migration (2 hours)
  - Validate migrated data (2 hours)

Day 3 (08:00 - 17:00):
  - Fix migration issues (6 hours)
  - Final validation (2 hours)

Day 4 (08:00 - 12:00):
  - User acceptance testing (4 hours)
  - Go/No-Go decision (1 hour)

Day 4 (20:00 - 22:00):
  - Go-Live (2 hours)
```

### Rollback Plan

If migration fails:

1. **Keep Joomla Site Online**
   - Migrate on separate server
   - Don't switch DNS until validated

2. **Quick Rollback**
   ```bash
   # Switch DNS back to Joomla
   # Point to old server
   ```

3. **Data Recovery**
   - Restore database backup
   - Restore file backup
   - Investigate failure cause

---

## Deployment Checklist

### Pre-Deployment Checklist

- [ ] All code reviewed and approved
- [ ] All tests passing (unit, feature, browser)
- [ ] Laravel Pint formatting applied
- [ ] Security scan completed
- [ ] Performance benchmarks met
- [ ] Database backup completed
- [ ] Files backup completed
- [ ] Configuration backup completed
- [ ] Staging environment tested
- [ ] Stakeholder approval received

### Deployment Checklist

- [ ] Put site in maintenance mode (if needed)
- [ ] Pull latest code
- [ ] Install PHP dependencies
- [ ] Install Node dependencies
- [ ] Build frontend assets
- [ ] Run database migrations
- [ ] Clear and cache configuration
- [ ] Clear and cache routes
- [ ] Clear and cache views
- [ ] Restart queue workers
- [ ] Restart PHP-FPM
- [ ] Bring site back online
- [ ] Run smoke tests
- [ ] Verify critical functionality
- [ ] Monitor logs for errors

### Post-Deployment Checklist

- [ ] Health check passing
- [ ] Database connection OK
- [ ] Cache connection OK
- [ ] Queue workers running
- [ ] No errors in logs
- [ ] Response times normal
- [ ] User functionality working
- [ ] Admin panel accessible
- [ ] SIPP integration working
- [ ] Monitoring alerts configured

---

## CI/CD Pipeline

### GitHub Actions Workflow

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [production]

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.5'
          extensions: mbstring, pdo, pdo_mysql
          coverage: none

      - name: Install Dependencies
        run: composer install --no-dev --optimize-autoloader

      - name: Build Frontend
        run: |
          npm ci
          npm run build

      - name: Run Tests
        run: vendor/bin/pest

      - name: Deploy to Forge
        uses: deployphp/action@v1
        with:
          deployer: deployer/deployer
          private-key: ${{ secrets.SSH_PRIVATE_KEY }}
          dep: deploy production
```

---

## Support & Maintenance

### Regular Maintenance Tasks

**Daily:**
- Check application logs
- Monitor error rates
- Verify backup completion

**Weekly:**
- Review performance metrics
- Check security advisories
- Test backup restoration

**Monthly:**
- Update dependencies (composer, npm)
- Security audit
- Performance review
- Capacity planning

**Quarterly:**
- Major dependency updates
- Security penetration testing
- Disaster recovery testing
- Architecture review

### Emergency Contacts

- **Primary Developer:** [Contact Info]
- **System Administrator:** [Contact Info]
- **Management:** [Contact Info]
- **Laravel Forge Support:** https://forge.laravel.com/support

### Documentation Resources

- **Laravel Documentation:** https://laravel.com/docs/12.x
- **Filament Documentation:** https://filamentphp.com/docs
- **Inertia.js Documentation:** https://inertiajs.com/
- **Project Repository:** [GitHub URL]

---

## Appendix

### A. Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Optimize for production
php artisan optimize

# View logs
tail -f storage/logs/laravel.log

# Restart queue
php artisan queue:restart

# Database backup
php artisan backup:run

# Migration status
php artisan migrate:status

# Route list
php artisan route:list

# Config cache
php artisan config:cache

# View cache
php artisan view:cache
```

### B. Troubleshooting

**Issue: 500 Error**
```bash
# Check permissions
ls -la storage bootstrap/cache

# Check logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan optimize:clear
```

**Issue: Queue Not Processing**
```bash
# Check queue status
php artisan queue:failed

# Restart queue
php artisan queue:restart

# Check supervisor
sudo supervisorctl status
```

**Issue: Database Connection**
```bash
# Test connection
php artisan db:show

# Check credentials
cat .env | grep DB_

# Test MySQL
mysql -u username -p -h host database
```

### C. Performance Optimization

```bash
# Optimize composer
composer install --optimize-autoloader --no-dev

# Optimize npm
npm ci --production

# Cache routes
php artisan route:cache

# Cache config
php artisan config:cache

# Cache views
php artisan view:cache

# Warmup cache
php artisan cache:warmup
```

---

**Document Version:** 1.0
**Last Updated:** 2026-01-18
**Next Review:** 2026-02-18
**Maintained By:** Development Team
