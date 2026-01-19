# Joomla Migration Procedures - PA Penajam Website

**Project:** Pengadilan Agama Penajam Website Migration
**Source:** Joomla 3.x
**Target:** Laravel 12 + Inertia.js + React
**Version:** 1.0
**Last Updated:** 2026-01-18

---

## Table of Contents

1. [Migration Overview](#migration-overview)
2. [Pre-Migration Preparation](#pre-migration-preparation)
3. [Data Export from Joomla](#data-export-from-joomla)
4. [Data Migration Process](#data-migration-process)
5. [Post-Migration Validation](#post-migration-validation)
6. [Testing & Verification](#testing--verification)
7. [Go-Live Procedures](#go-live-procedures)
8. [Rollback Procedures](#rollback-procedures)
9. [Troubleshooting](#troubleshooting)

---

## Migration Overview

### Migration Scope

The following data will be migrated from Joomla to Laravel:

1. **Content**
   - Pages (static content)
   - News articles
   - Categories
   - Tags

2. **Media**
   - Images
   - Documents
   - Media folders

3. **Structure**
   - Menu structure
   - Menu items
   - URL redirects

4. **Users**
   - Admin users
   - User roles

### Migration Timeline

```
Phase 1: Preparation        (1 day)
Phase 2: Data Export        (1 day)
Phase 3: Migration          (2 days)
Phase 4: Validation         (1 day)
Phase 5: Testing            (1 day)
Phase 6: Go-Live            (1 day)
```

**Total Duration:** 7 days

---

## Pre-Migration Preparation

### Step 1: Analyze Joomla Installation

**Objective:** Understand the current Joomla structure and data

#### Tasks:

1. **Access Joomla Admin Panel**
   ```bash
   URL: https://old-pa-penajam.go.id/administrator
   Username: [admin username]
   Password: [admin password]
   ```

2. **Review Content Structure**
   - List all categories
   - Count articles per category
   - Identify featured articles
   - Note special pages

3. **Review Media Structure**
   - List all media folders
   - Identify image locations
   - Check file sizes
   - Note broken links

4. **Review User Accounts**
   - List all user accounts
   - Note user roles
   - Identify active users

5. **Review Menu Structure**
   - Document menu hierarchy
   - Note external links
   - Identify special menu items

#### Output:

- [ ] Content inventory completed
- [ ] Media inventory completed
- [ ] User inventory completed
- [ ] Menu inventory completed

### Step 2: Prepare Target Environment

**Objective:** Set up Laravel environment for migration

#### Tasks:

1. **Set Up Staging Environment**
   ```bash
   # Create staging database
   mysql -u root -p -e "CREATE DATABASE pa_penajam_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

   # Clone repository
   git clone -b staging git@github.com:pa-penajam/website.git /var/www/pa-penajam-staging

   # Install dependencies
   cd /var/www/pa-penajam-staging
   composer install
   npm install
   ```

2. **Configure Environment**
   ```bash
   # Copy environment file
   cp .env.example .env

   # Generate key
   php artisan key:generate

   # Configure database
   # Edit .env with staging database credentials
   ```

3. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

4. **Install Media Files**
   ```bash
   # Create storage link
   php artisan storage:link

   # Set permissions
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

#### Output:

- [ ] Staging environment ready
- [ ] Database configured
- [ ] Migrations run successfully
- [ ] File permissions set

### Step 3: Prepare Migration Scripts

**Objective:** Ensure all migration scripts are ready

#### Tasks:

1. **Review Migration Scripts**
   ```bash
   # List migration scripts
   ls -la app/Console/Commands/Joomla/

   # Verify scripts exist:
   # - MigrateContent.php
   # - MigrateCategories.php
   # - MigrateMenu.php
   # - MigrateMedia.php
   # - MigrateUsers.php
   ```

2. **Test Migration Scripts**
   ```bash
   # Test with sample data
   php artisan joomla:test-migration
   ```

3. **Configure Migration Settings**
   ```php
   // config/joomla.php
   return [
       'source_path' => '/path/to/joomla/export',
       'image_base_url' => 'https://old-pa-penajam.go.id/images',
       'media_base_url' => 'https://old-pa-penajam.go.id/media',
       'skip_existing' => true,
       'dry_run' => false,
   ];
   ```

#### Output:

- [ ] Migration scripts reviewed
- [ ] Migration scripts tested
- [ ] Configuration completed

---

## Data Export from Joomla

### Step 1: Export Database

**Objective:** Export Joomla database

#### Procedure:

1. **Access Joomla Server**
   ```bash
   ssh user@old-joomla-server.com
   ```

2. **Create Export Directory**
   ```bash
   mkdir -p /tmp/joomla-export
   cd /tmp/joomla-export
   ```

3. **Export Database**
   ```bash
   # Method 1: Using mysqldump (recommended)
   mysqldump -u joomla_user -p joomla_db \
     --single-transaction \
     --quick \
     --lock-tables=false \
     --complete-insert \
     --extended-insert=false \
     > joomla_database.sql

   # Method 2: Export specific tables
   mysqldump -u joomla_user -p joomla_db \
     jos_content jos_categories jos_users jos_menu \
     > joomla_core_tables.sql
   ```

4. **Compress Export**
   ```bash
   gzip joomla_database.sql
   ```

5. **Transfer to Laravel Server**
   ```bash
   scp joomla_database.sql.gz user@laravel-server:/var/www/pa-penajam/staging/joomla-export/
   ```

#### Output:

- [ ] Database exported
- [ ] Export file transferred
- [ ] Export integrity verified

### Step 2: Export Media Files

**Objective:** Export all images and documents

#### Procedure:

1. **Create Media Archive**
   ```bash
   # On Joomla server
   cd /var/www/joomla
   tar -czf /tmp/joomla-media.tar.gz images/ media/
   ```

2. **Transfer to Laravel Server**
   ```bash
   scp /tmp/joomla-media.tar.gz user@laravel-server:/var/www/pa-penajam/staging/joomla-export/
   ```

3. **Extract on Laravel Server**
   ```bash
   cd /var/www/pa-penajam/staging/joomla-export/
   tar -xzf joomla-media.tar.gz
   ```

#### Output:

- [ ] Media files exported
- [ ] Media files transferred
- [ ] Media files extracted

### Step 3: Export JSON Data

**Objective:** Export data in JSON format for migration scripts

#### Procedure:

1. **Export Content**
   ```bash
   mysql -u joomla_user -p joomla_db \
     --silent \
     --skip-column-names \
     -e "SELECT * FROM jos_content" \
     | json > joomla_content.json
   ```

2. **Export Categories**
   ```bash
   mysql -u joomla_user -p joomla_db \
     --silent \
     --skip-column-names \
     -e "SELECT * FROM jos_categories" \
     | json > joomla_categories.json
   ```

3. **Export Menu**
   ```bash
   mysql -u joomla_user -p joomla_db \
     --silent \
     --skip-column-names \
     -e "SELECT * FROM jos_menu" \
     | json > joomla_menu.json
   ```

4. **Export Users**
   ```bash
   mysql -u joomla_user -p joomla_db \
     --silent \
     --skip-column-names \
     -e "SELECT * FROM jos_users" \
     | json > joomla_users.json
   ```

#### Output:

- [ ] JSON exports completed
- [ ] JSON files validated
- [ ] JSON files stored in staging/docs

---

## Data Migration Process

### Step 1: Migrate Categories

**Objective:** Migrate all categories from Joomla

#### Procedure:

1. **Run Category Migration**
   ```bash
   cd /var/www/pa-penajam/current
   php artisan joomla:migrate:categories
   ```

2. **Verify Migration**
   ```bash
   # Check database
   php artisan tinker
   >>> App\Models\Category::count()
   ```

3. **Review Results**
   - Check for errors
   - Verify category hierarchy
   - Verify category slugs

#### Output:

- [ ] Categories migrated
- [ ] Category hierarchy preserved
- [ ] No migration errors

### Step 2: Migrate Content

**Objective:** Migrate all pages and articles

#### Procedure:

1. **Run Content Migration**
   ```bash
   php artisan joomla:migrate:content
   ```

2. **Monitor Progress**
   - Migration will show progress bar
   - Check for warnings
   - Note any skipped items

3. **Verify Migration**
   ```bash
   php artisan tinker
   >>> App\Models\Page::count()
   >>> App\Models\News::count()
   ```

4. **Review Content**
   - Check content formatting
   - Verify image links
   - Check internal links

#### Output:

- [ ] Pages migrated
- [ ] News articles migrated
- [ ] Content formatting preserved
- [ ] Images linked correctly

### Step 3: Migrate Media Files

**Objective:** Migrate all images and documents

#### Procedure:

1. **Run Media Migration**
   ```bash
   php artisan joomla:migrate:media
   ```

2. **Monitor Progress**
   - Large files may take time
   - Check disk space
   - Verify file integrity

3. **Verify Migration**
   ```bash
   # Check storage directory
   ls -la storage/app/public/images/
   ls -la storage/app/public/documents/
   ```

4. **Update Links**
   - Update image URLs in content
   - Verify document links
   - Check for broken links

#### Output:

- [ ] Images migrated
- [ ] Documents migrated
- [ ] File links updated
- [ ] No broken links

### Step 4: Migrate Menu Structure

**Objective:** Migrate menu hierarchy and links

#### Procedure:

1. **Run Menu Migration**
   ```bash
   php artisan joomla:migrate:menu
   ```

2. **Verify Migration**
   ```bash
   php artisan tinker
   >>> App\Models\Menu::count()
   >>> App\Models\MenuItem::count()
   ```

3. **Test Menu Rendering**
   - Check frontend menu display
   - Verify menu hierarchy
   - Test menu links
   - Check external links

#### Output:

- [ ] Menus migrated
- [ ] Menu hierarchy preserved
- [ ] Menu links working
- [ ] Frontend menu displaying correctly

### Step 5: Migrate Users

**Objective:** Migrate admin users

#### Procedure:

1. **Run User Migration**
   ```bash
   php artisan joomla:migrate:users
   ```

2. **Verify Migration**
   ```bash
   php artisan tinker
   >>> App\Models\User::count()
   ```

3. **Test User Access**
   - Test admin login
   - Verify user roles
   - Check permissions
   - Reset passwords if needed

#### Output:

- [ ] Users migrated
- [ ] User roles assigned
- [ ] Admin access working

---

## Post-Migration Validation

### Step 1: Data Integrity Check

**Objective:** Verify all data migrated correctly

#### Tasks:

1. **Compare Record Counts**
   ```bash
   # Joomla counts
   mysql -u joomla_user -p joomla_db \
     -e "SELECT COUNT(*) FROM jos_content"

   # Laravel counts
   php artisan tinker
   >>> App\Models\Page::count() + App\Models\News::count()
   ```

2. **Verify Content Quality**
   - Spot-check 20 random pages
   - Verify images displaying
   - Check links working
   - Verify formatting

3. **Verify Relationships**
   - Categories linked correctly
   - Tags assigned correctly
   - Menu items linked correctly

#### Output:

- [ ] Record counts match
- [ ] Content quality verified
- [ ] Relationships intact

### Step 2: URL Redirect Configuration

**Objective:** Set up redirects from old URLs to new URLs

#### Procedure:

1. **Create Redirect Map**
   ```php
   // database/seeders/RedirectSeeder.php
   $redirects = [
       '/old-url-1' => '/new-url-1',
       '/old-url-2' => '/new-url-2',
       // ... map all old URLs to new URLs
   ];

   foreach ($redirects as $from => $to) {
       App\Models\Redirect::create([
           'from' => $from,
           'to' => $to,
           'status' => 301,
       ]);
   }
   ```

2. **Run Seeder**
   ```bash
   php artisan db:seed --class=RedirectSeeder
   ```

3. **Test Redirects**
   ```bash
   curl -I http://new-site/old-url-1
   # Should return 301 redirect
   ```

#### Output:

- [ ] Redirect map created
- [ ] Redirects configured
- [ ] Redirects tested

### Step 3: SEO Verification

**Objective:** Ensure SEO is preserved

#### Tasks:

1. **Verify Meta Tags**
   - Check page titles
   - Check meta descriptions
   - Check meta keywords

2. **Verify Sitemap**
   ```bash
   php artisan sitemap:generate
   ```

3. **Verify Robots.txt**
   - Check robots.txt configuration
   - Verify sitemap reference

4. **Test Google Search Console**
   - Submit new sitemap
   - Monitor crawl errors
   - Check indexing

#### Output:

- [ ] Meta tags verified
- [ ] Sitemap generated
- [ ] Robots.txt configured
- [ ] Google Search Console configured

---

## Testing & Verification

### Step 1: Functional Testing

**Objective:** Test all functionality

#### Test Cases:

1. **Frontend Testing**
   - [ ] Homepage loads correctly
   - [ ] All menu links work
   - [ ] Pages display correctly
   - [ ] News section works
   - [ ] Documents accessible
   - [ ] Search functionality works

2. **Admin Panel Testing**
   - [ ] Admin login works
   - [ ] Can edit pages
   - [ ] Can create news
   - [ ] Can upload documents
   - [ ] Can manage menus

3. **SIPP Integration Testing**
   - [ ] Court schedules sync
   - [ ] Schedules display correctly
   - [ ] Case data loads

#### Output:

- [ ] All functional tests passed
- [ ] No critical bugs found

### Step 2: Performance Testing

**Objective:** Verify performance meets requirements

#### Tests:

1. **Page Load Time**
   ```bash
   # Test homepage
   curl -o /dev/null -s -w "%{time_total}\n" https://staging.pa-penajam.go.id/

   # Test should be < 2 seconds
   ```

2. **Database Query Performance**
   - Check for slow queries
   - Verify no N+1 queries
   - Check query counts per page

3. **Load Testing**
   - Test with 100 concurrent users
   - Test with 500 concurrent users
   - Monitor response times

#### Output:

- [ ] Page load times acceptable
- [ ] Database queries optimized
- [ ] Load testing passed

### Step 3: User Acceptance Testing

**Objective:** Get stakeholder approval

#### Process:

1. **Demo to Stakeholders**
   - Show frontend
   - Show admin panel
   - Demonstrate features
   - Answer questions

2. **Collect Feedback**
   - Document issues
   - Prioritize fixes
   - Create task list

3. **Implement Fixes**
   - Fix critical issues
   - Address concerns
   - Make improvements

4. **Re-Test**
   - Verify fixes
   - Re-demo to stakeholders
   - Get final approval

#### Output:

- [ ] Stakeholder demo completed
- [ ] Feedback collected
- [ ] Issues fixed
- [ ] Final approval received

---

## Go-Live Procedures

### Pre-Launch (1 Day Before)

#### Tasks:

1. **Final Backup**
   ```bash
   # Backup Joomla site
   ssh user@joomla-server "mysqldump joomla_db | gzip" > joomla-final-backup.sql.gz

   # Backup Laravel staging
   php artisan backup:run
   ```

2. **Final Migration**
   ```bash
   # Run final migration from Joomla
   php artisan joomla:migrate:all --final
   ```

3. **Final Validation**
   - Verify all data
   - Test all functionality
   - Check performance

4. **Prepare Team**
   - Assemble deployment team
   - Assign roles
   - Prepare communication channels

### Launch Day

#### Timeline:

**T-1 Hour: Final Preparation**
- [ ] Team assembled
- [ ] Communication channels open
- [ ] Emergency contacts verified
- [ ] Rollback plan ready

**T-30 Minutes: Final Checks**
- [ ] All systems green
- [ ] Backups completed
- [ ] Team ready
- [ ] Stakeholder notified

**T-0: Launch**
- [ ] Put Joomla in maintenance mode
- [ ] Run final data export
- [ ] Deploy Laravel to production
- [ ] Switch DNS to new server
- [ ] Monitor for issues

**T+30 Minutes: Verification**
- [ ] DNS propagated
- [ ] Site accessible
- [ ] All functionality working
- [ ] No critical errors

**T+1 Hour: Post-Launch**
- [ ] Continue monitoring
- [ ] Address any issues
- [ ] Update stakeholders
- [ ] Document lessons learned

---

## Rollback Procedures

### When to Rollback

Rollback to Joomla if:

- Critical functionality broken
- Data corruption detected
- Security vulnerability exposed
- Performance severely degraded
- Stakeholder requests rollback

### Rollback Process

#### Immediate Rollback (< 15 minutes):

1. **Assess Situation**
   - Identify issue
   - Determine impact
   - Make rollback decision

2. **Execute Rollback**
   ```bash
   # Point DNS back to Joomla server
   # Update DNS A record

   # Or switch Nginx configuration
   sudo ln -s /etc/nginx/sites-available/joomla /etc/nginx/sites-enabled/pa-penajam
   sudo systemctl reload nginx
   ```

3. **Verify Rollback**
   - Test Joomla site
   - Verify functionality
   - Monitor errors

4. **Communicate**
   - Notify stakeholders
   - Update status page
   - Document incident

### Post-Rollback

1. **Investigate Issue**
   - Identify root cause
   - Document findings
   - Create fix plan

2. **Fix and Retest**
   - Implement fix
   - Test thoroughly
   - Prepare for relaunch

---

## Troubleshooting

### Common Issues

#### Issue 1: Character Encoding Problems

**Symptoms:** Special characters display incorrectly

**Solution:**
```bash
# Ensure database is UTF-8
ALTER DATABASE pa_penajam_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Re-run migration with correct encoding
php artisan joomla:migrate:content --encoding=utf8mb4
```

#### Issue 2: Broken Image Links

**Symptoms:** Images not displaying

**Solution:**
```bash
# Run media migration again
php artisan joomla:migrate:media --fix-links

# Update content with new image URLs
php artisan joomla:update-image-links
```

#### Issue 3: Menu Structure Not Preserved

**Symptoms:** Menu hierarchy incorrect

**Solution:**
```bash
# Re-run menu migration
php artisan joomla:migrate:menu --rebuild

# Manually adjust menu items in admin panel
```

#### Issue 4: Performance Degradation

**Symptoms:** Slow page loads

**Solution:**
```bash
# Clear all caches
php artisan optimize:clear

# Re-optimize
php artisan optimize

# Check database indexes
php artisan db:index --check

# Restart services
sudo systemctl reload php8.5-fpm
sudo supervisorctl restart all
```

#### Issue 5: Missing Content

**Symptoms:** Some content not migrated

**Solution:**
```bash
# Check migration logs
tail -f storage/logs/migration.log

# Re-run migration for missing content
php artisan joomla:migrate:content --id=123

# Or re-run entire migration
php artisan joomla:migrate:content --force
```

### Getting Help

If you encounter issues not covered here:

1. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log
   tail -f storage/logs/migration.log
   ```

2. **Check Documentation**
   - Deployment Strategy: `/docs/DEPLOYMENT_STRATEGY.md`
   - Data Mapping: `/docs/JOOMLA_DATA_MAPPING.md`
   - Implementation: `/docs/JOOMLA_MIGRATION_IMPLEMENTATION.md`

3. **Contact Support**
   - Development Team: [contact info]
   - System Administrator: [contact info]
   - Laravel Community: https://laravel.com/docs
   - Stack Overflow: https://stackoverflow.com/questions/tagged/laravel

---

## Appendix

### A. Useful Commands

```bash
# Check migration status
php artisan joomla:status

# Re-run specific migration
php artisan joomla:migrate:content --force

# Rollback migration
php artisan joomla:rollback:content

# Verify data integrity
php artisan joomla:verify

# Generate migration report
php artisan joomla:report
```

### B. Migration Log Location

```bash
# Migration logs
storage/logs/migration.log

# Error logs
storage/logs/laravel.log

# Queue logs
storage/logs/queue-worker.log
```

### C. Contact Information

- **Project Manager:** [Name, Email, Phone]
- **Lead Developer:** [Name, Email, Phone]
- **System Administrator:** [Name, Email, Phone]
- **Joomla Expert:** [Name, Email, Phone]

---

**Document Version:** 1.0
**Last Updated:** 2026-01-18
**Next Review:** 2026-02-18
**Maintained By:** Development Team
