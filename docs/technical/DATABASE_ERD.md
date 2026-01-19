# PA Penajam Website - Database ERD Documentation

## Overview

This document provides a comprehensive Entity-Relationship Diagram (ERD) description and schema documentation for the PA Penajam website database.

---

## Database Structure Overview

### Database Name: `pa_penajam_prod` (production)

### Connection Details
- **Development:** SQLite (`database/database.sqlite`)
- **Production:** MySQL 8.0+ / PostgreSQL 12+
- **Charset:** UTF8MB4
- **Collation:** `utf8mb4_unicode_ci`

---

## Entity Relationship Diagram

### Core Entity Groups

```
┌─────────────────────────────────────────────────────────────┐
│                     USERS & AUTH                            │
│  users                                                      │
│  user_activity_logs                                         │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                  CONTENT MANAGEMENT                         │
│  pages → page_blocks                                        │
│  page_templates                                             │
│  menus → menu_items                                         │
│  categories                                                 │
│  news                                                       │
│  documents → document_versions                              │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                   COURT DATA (SIPP)                         │
│  court_schedules                                           │
│  sipp_cases                                                 │
│  sipp_judges                                                │
│  sipp_court_rooms                                           │
│  sipp_case_types                                            │
│  sipp_sync_logs                                             │
│  case_statistics                                            │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│              TRANSPARENCY & PUBLIC INFO                     │
│  budget_transparency                                        │
│  ppid_requests                                              │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                      SYSTEM CONFIG                          │
│  settings                                                   │
│  migrations                                                 │
│  jobs                                                       │
└─────────────────────────────────────────────────────────────┘
```

---

## Table Definitions

### 1. Users & Authentication

#### `users`
Extends Laravel's default users table with role-based access control.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | User ID |
| `name` | VARCHAR(255) | NOT NULL | Full name |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | Email address |
| `email_verified_at` | TIMESTAMP | NULLABLE | Email verification timestamp |
| `password` | VARCHAR(255) | NOT NULL | Bcrypt hashed password |
| `role` | ENUM | NOT NULL | Role: super_admin, admin, author, designer, subscriber |
| `permissions` | JSON | NULLABLE | Custom permissions object |
| `last_login_at` | TIMESTAMP | NULLABLE | Last login timestamp |
| `profile_completed` | BOOLEAN | Default: false | Profile completion status |
| `remember_token` | VARCHAR(100) | NULLABLE | "Remember me" token |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- Has Many `pages` (created_by)
- Has Many `pages` (updated_by)
- Has Many `news` (created_by)
- Has Many `documents` (created_by)
- Has Many `budget_transparency` (created_by)
- Has Many `user_activity_logs`

**Indexes:**
- Primary: `id`
- Unique: `email`
- Index: `role`

---

#### `user_activity_logs`
Audit trail for user actions.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Log ID |
| `user_id` | BIGINT | FK → users.id, NULLABLE | User who performed action |
| `action` | VARCHAR(255) | NOT NULL | Action performed (create, update, delete) |
| `subject_type` | VARCHAR(255) | NOT NULL | Type of affected model |
| `subject_id` | BIGINT | NOT NULL | ID of affected model |
| `changes` | JSON | NULLABLE | Before/after changes |
| `ip_address` | VARCHAR(45) | NULLABLE | User IP address |
| `user_agent` | TEXT | NULLABLE | Browser user agent |
| `created_at` | TIMESTAMP | Auto | Timestamp of action |

**Relationships:**
- Belongs To `user`

**Indexes:**
- Primary: `id`
- Index: `user_id`
- Index: `subject_type`, `subject_id`

---

### 2. Pages & Page Builder

#### `pages`
Website pages with page builder content.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Page ID |
| `title` | VARCHAR(255) | NOT NULL | Page title |
| `slug` | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly identifier |
| `content` | JSON | NULLABLE | Page builder JSON content |
| `meta_description` | TEXT | NULLABLE | SEO meta description |
| `page_type` | ENUM | NOT NULL | Type: static, dynamic, template |
| `template_id` | BIGINT | FK → page_templates.id, NULLABLE | Template used |
| `status` | ENUM | NOT NULL | Status: draft, published, scheduled |
| `published_at` | TIMESTAMP | NULLABLE | Publication date |
| `created_by` | BIGINT | FK → users.id, NOT NULL | Creator |
| `updated_by` | BIGINT | FK → users.id, NULLABLE | Last editor |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- Belongs To `template` (page_templates)
- BelongsTo `creator` (users)
- BelongsTo `updater` (users)
- Has Many `blocks` (page_blocks)
- Has Many `menu_items` (when url_type = 'page')

**Indexes:**
- Primary: `id`
- Unique: `slug`
- Index: `status`, `published_at`
- Index: `page_type`, `template_id`

---

#### `page_templates`
Reusable page templates.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Template ID |
| `name` | VARCHAR(255) | NOT NULL | Template name |
| `description` | TEXT | NULLABLE | Template description |
| `content` | JSON | NOT NULL | Template content (blocks) |
| `is_system` | BOOLEAN | Default: false | System template (non-deletable) |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- Has Many `pages`

**Indexes:**
- Primary: `id`
- Index: `is_system`

---

#### `page_blocks`
Individual page builder components.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Block ID |
| `page_id` | BIGINT | FK → pages.id, NOT NULL | Parent page |
| `type` | VARCHAR(50) | NOT NULL | Component type |
| `content` | JSON | NULLABLE | Block configuration |
| `order` | INT | NOT NULL | Display order |
| `settings` | JSON | NULLABLE | Block-specific settings |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- BelongsTo `page` (pages)

**Indexes:**
- Primary: `id`
- Index: `page_id`, `order`
- Index: `type`

---

### 3. Menus & Navigation

#### `menus`
Website menu containers.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Menu ID |
| `name` | VARCHAR(255) | NOT NULL | Menu name |
| `location` | ENUM | NOT NULL | Location: header, footer, sidebar, mobile |
| `max_depth` | INT | Default: 3 | Maximum nesting level |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- Has Many `items` (menu_items)

**Indexes:**
- Primary: `id`
- Index: `location`

---

#### `menu_items`
Individual menu items with hierarchy.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Menu item ID |
| `menu_id` | BIGINT | FK → menus.id, NOT NULL | Parent menu |
| `parent_id` | BIGINT | FK → menu_items.id, NULLABLE | Parent item (for nesting) |
| `title` | VARCHAR(255) | NOT NULL | Display title |
| `url_type` | ENUM | NOT NULL | Type: route, page, custom, external |
| `route_name` | VARCHAR(255) | NULLABLE | Laravel route name |
| `page_id` | BIGINT | FK → pages.id, NULLABLE | Linked page |
| `custom_url` | VARCHAR(500) | NULLABLE | Custom URL |
| `target` | ENUM | Default: _self | Link target: _self, _blank |
| `order` | INT | NOT NULL | Display order |
| `is_active` | BOOLEAN | Default: true | Visibility status |
| `conditional_rules` | JSON | NULLABLE | Display conditions |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- BelongsTo `menu` (menus)
- BelongsTo `parent` (menu_items, self-referential)
- HasMany `children` (menu_items, self-referential)
- BelongsTo `page` (pages)

**Indexes:**
- Primary: `id`
- Index: `menu_id`, `order`
- Index: `parent_id`
- Index: `is_active`

---

### 4. Content Management

#### `categories`
Hierarchical categories for content organization.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Category ID |
| `name` | VARCHAR(255) | NOT NULL | Category name |
| `slug` | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly identifier |
| `description` | TEXT | NULLABLE | Category description |
| `parent_id` | BIGINT | FK → categories.id, NULLABLE | Parent category |
| `type` | ENUM | NOT NULL | Type: news, document, page |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- BelongsTo `parent` (categories, self-referential)
- HasMany `children` (categories, self-referential)
- HasMany `news`
- HasMany `documents`

**Indexes:**
- Primary: `id`
- Unique: `slug`
- Index: `parent_id`, `type`

---

#### `news`
News and announcement articles.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Article ID |
| `title` | VARCHAR(255) | NOT NULL | Article title |
| `slug` | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly identifier |
| `content` | LONGTEXT | NOT NULL | Article content (HTML) |
| `category_id` | BIGINT | FK → categories.id, NULLABLE | Category |
| `is_featured` | BOOLEAN | Default: false | Featured flag |
| `views_count` | INT | Default: 0 | View counter |
| `published_at` | TIMESTAMP | NULLABLE | Publication date |
| `created_by` | BIGINT | FK → users.id, NOT NULL | Author |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- BelongsTo `category` (categories)
- BelongsTo `author` (users)
- HasMany `menu_items` (if linked)

**Indexes:**
- Primary: `id`
- Unique: `slug`
- Index: `category_id`, `published_at`
- Index: `is_featured`

---

#### `documents`
Downloadable documents with version control.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Document ID |
| `title` | VARCHAR(255) | NOT NULL | Document title |
| `description` | TEXT | NULLABLE | Document description |
| `file_path` | VARCHAR(500) | NOT NULL | File storage path |
| `file_size` | BIGINT | NOT NULL | File size in bytes |
| `download_count` | INT | Default: 0 | Download counter |
| `category_id` | BIGINT | FK → categories.id, NULLABLE | Category |
| `is_public` | BOOLEAN | Default: true | Public visibility |
| `published_at` | TIMESTAMP | NULLABLE | Publication date |
| `created_by` | BIGINT | FK → users.id, NOT NULL | Uploader |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- BelongsTo `category` (categories)
- BelongsTo `uploader` (users)
- HasMany `versions` (document_versions)

**Indexes:**
- Primary: `id`
- Index: `category_id`, `published_at`
- Index: `is_public`

---

#### `document_versions`
Document version history.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Version ID |
| `document_id` | BIGINT | FK → documents.id, NOT NULL | Parent document |
| `version` | INT | NOT NULL | Version number |
| `file_path` | VARCHAR(500) | NOT NULL | File storage path |
| `file_size` | BIGINT | NOT NULL | File size in bytes |
| `uploaded_by` | BIGINT | FK → users.id, NOT NULL | Uploader |
| `created_at` | TIMESTAMP | Auto | Upload timestamp |

**Relationships:**
- BelongsTo `document` (documents)
- BelongsTo `uploader` (users)

**Indexes:**
- Primary: `id`
- Index: `document_id`, `version`

---

### 5. Court Data (SIPP Integration)

#### `court_schedules`
Court hearing schedules synced from SIPP.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Schedule ID |
| `case_number` | VARCHAR(255) | NOT NULL | Case number |
| `case_title` | VARCHAR(500) | NOT NULL | Case title |
| `sipp_case_id` | BIGINT | FK → sipp_cases.id, NULLABLE | SIPP case reference |
| `judge_id` | BIGINT | FK → sipp_judges.id, NULLABLE | Presiding judge |
| `court_room_id` | BIGINT | FK → sipp_court_rooms.id, NULLABLE | Courtroom |
| `schedule_date` | DATE | NOT NULL | Hearing date |
| `time` | TIME | NULLABLE | Hearing time |
| `status` | ENUM | NOT NULL | Status: scheduled, in_progress, completed, postponed |
| `notes` | TEXT | NULLABLE | Additional notes |
| `synced_at` | TIMESTAMP | NULLABLE | Last sync timestamp |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- BelongsTo `sipp_case` (sipp_cases)
- BelongsTo `judge` (sipp_judges)
- BelongsTo `court_room` (sipp_court_rooms)

**Indexes:**
- Primary: `id`
- Index: `schedule_date`, `status`
- Index: `judge_id`, `court_room_id`
- Index: `sipp_case_id`

---

#### `sipp_cases`
Cases cached from SIPP system.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Case ID |
| `sipp_id` | VARCHAR(100) | UNIQUE, NOT NULL | SIPP system ID |
| `case_number` | VARCHAR(255) | NOT NULL | Case number |
| `case_title` | VARCHAR(500) | NOT NULL | Case title |
| `case_type_id` | BIGINT | FK → sipp_case_types.id, NULLABLE | Case type |
| `party_names` | JSON | NULLABLE | Parties involved |
| `registration_date` | DATE | NULLABLE | Registration date |
| `status` | ENUM | NOT NULL | Status: active, completed, archived |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- BelongsTo `case_type` (sipp_case_types)
- HasMany `court_schedules`

**Indexes:**
- Primary: `id`
- Unique: `sipp_id`
- Index: `case_number`, `status`

---

#### `sipp_judges`
Judges from SIPP system.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Judge ID |
| `sipp_id` | VARCHAR(100) | UNIQUE, NOT NULL | SIPP system ID |
| `name` | VARCHAR(255) | NOT NULL | Judge name |
| `nip` | VARCHAR(50) | NULLABLE | Civil servant number |
| `is_active` | BOOLEAN | Default: true | Active status |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- HasMany `court_schedules`

**Indexes:**
- Primary: `id`
- Unique: `sipp_id`
- Index: `is_active`

---

#### `sipp_court_rooms`
Courtrooms from SIPP system.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Room ID |
| `sipp_id` | VARCHAR(100) | UNIQUE, NOT NULL | SIPP system ID |
| `name` | VARCHAR(255) | NOT NULL | Room name |
| `capacity` | INT | NULLABLE | Room capacity |
| `is_active` | BOOLEAN | Default: true | Active status |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- HasMany `court_schedules`

**Indexes:**
- Primary: `id`
- Unique: `sipp_id`
- Index: `is_active`

---

#### `sipp_case_types`
Case types from SIPP system.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Type ID |
| `sipp_id` | VARCHAR(100) | UNIQUE, NOT NULL | SIPP system ID |
| `name` | VARCHAR(255) | NOT NULL | Type name |
| `code` | VARCHAR(50) | NULLABLE | Type code |
| `description` | TEXT | NULLABLE | Type description |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- HasMany `sipp_cases`
- HasMany `case_statistics`

**Indexes:**
- Primary: `id`
- Unique: `sipp_id`

---

#### `sipp_sync_logs`
SIPP synchronization logs.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Log ID |
| `sync_type` | ENUM | NOT NULL | Type: full, incremental, manual |
| `status` | ENUM | NOT NULL | Status: success, failed, partial |
| `records_processed` | INT | Default: 0 | Records processed |
| `records_failed` | INT | Default: 0 | Records failed |
| `error_message` | TEXT | NULLABLE | Error details |
| `started_at` | TIMESTAMP | NOT NULL | Start timestamp |
| `completed_at` | TIMESTAMP | NULLABLE | Completion timestamp |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |

**Relationships:**
- None

**Indexes:**
- Primary: `id`
- Index: `sync_type`, `status`
- Index: `started_at`

---

### 6. Transparency & Statistics

#### `budget_transparency`
Budget information for transparency.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Budget ID |
| `year` | INT | NOT NULL | Budget year |
| `title` | VARCHAR(255) | NOT NULL | Budget title |
| `description` | TEXT | NULLABLE | Budget description |
| `amount` | DECIMAL(15,2) | NOT NULL | Budget amount |
| `document_path` | VARCHAR(500) | NULLABLE | Supporting document |
| `published_at` | TIMESTAMP | NULLABLE | Publication date |
| `created_by` | BIGINT | FK → users.id, NOT NULL | Creator |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- BelongsTo `creator` (users)

**Indexes:**
- Primary: `id`
- Index: `year`, `published_at`

---

#### `case_statistics`
Court case statistics.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Stat ID |
| `year` | INT | NOT NULL | Year |
| `month` | INT | NULLABLE | Month (nullable for yearly stats) |
| `case_type_id` | BIGINT | FK → sipp_case_types.id, NULLABLE | Case type |
| `total_cases` | INT | Default: 0 | Total cases |
| `resolved_cases` | INT | Default: 0 | Resolved cases |
| `pending_cases` | INT | Default: 0 | Pending cases |
| `average_duration` | DECIMAL(5,2) | NULLABLE | Avg duration (days) |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- BelongsTo `case_type` (sipp_case_types)

**Indexes:**
- Primary: `id`
- Index: `year`, `month`
- Index: `case_type_id`

---

### 7. PPID Portal

#### `ppid_requests`
Public information requests.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Request ID |
| `request_number` | VARCHAR(50) | UNIQUE, NOT NULL | Tracking number |
| `requester_name` | VARCHAR(255) | NOT NULL | Requester name |
| `email` | VARCHAR(255) | NOT NULL | Requester email |
| `phone` | VARCHAR(50) | NULLABLE | Requester phone |
| `request_type` | ENUM | NOT NULL | Type: berkala, serta_merta, setiap_saat, dikecualikan |
| `description` | TEXT | NOT NULL | Request description |
| `status` | ENUM | NOT NULL | Status: pending, processed, completed, rejected |
| `response` | TEXT | NULLABLE | Admin response |
| `responded_at` | TIMESTAMP | NULLABLE | Response timestamp |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- None

**Indexes:**
- Primary: `id`
- Unique: `request_number`
- Index: `status`, `created_at`

---

### 8. System Configuration

#### `settings`
System-wide configuration.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `id` | BIGINT | PK, Auto Increment | Setting ID |
| `key` | VARCHAR(255) | UNIQUE, NOT NULL | Setting key |
| `value` | JSON | NOT NULL | Setting value |
| `type` | ENUM | NOT NULL | Type: text, number, boolean, json |
| `group` | VARCHAR(100) | NULLABLE | Setting group |
| `description` | TEXT | NULLABLE | Setting description |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

**Relationships:**
- None

**Indexes:**
- Primary: `id`
- Unique: `key`
- Index: `group`

---

## Relationship Summary

### One-to-Many Relationships

```
users (1) → (N) pages [created_by]
users (1) → (N) pages [updated_by]
users (1) → (N) news
users (1) → (N) documents
users (1) → (N) budget_transparency
users (1) → (N) user_activity_logs
users (1) → (N) ppid_requests [if requester tracking enabled]

page_templates (1) → (N) pages
pages (1) → (N) page_blocks

menus (1) → (N) menu_items
menu_items (1) → (N) menu_items [parent_id, self-referential]

categories (1) → (N) categories [parent_id, self-referential]
categories (1) → (N) news
categories (1) → (N) documents

documents (1) → (N) document_versions

sipp_case_types (1) → (N) sipp_cases
sipp_case_types (1) → (N) case_statistics

sipp_cases (1) → (N) court_schedules
sipp_judges (1) → (N) court_schedules
sipp_court_rooms (1) → (N) court_schedules
```

### Many-to-Many Relationships

None currently. All relationships are hierarchical or parent-child.

---

## Database Migrations

### Migration Files

Migrations are located in `database/migrations/`:

```
2024_01_01_000001_create_users_table.php
2024_01_01_000002_create_user_activity_logs_table.php
2024_01_01_000003_create_page_templates_table.php
2024_01_01_000004_create_pages_table.php
2024_01_01_000005_create_page_blocks_table.php
2024_01_01_000006_create_menus_table.php
2024_01_01_000007_create_menu_items_table.php
2024_01_01_000008_create_categories_table.php
2024_01_01_000009_create_news_table.php
2024_01_01_000010_create_documents_table.php
2024_01_01_000011_create_document_versions_table.php
2024_01_01_000012_create_sipp_case_types_table.php
2024_01_01_000013_create_sipp_judges_table.php
2024_01_01_000014_create_sipp_court_rooms_table.php
2024_01_01_000015_create_sipp_cases_table.php
2024_01_01_000016_create_court_schedules_table.php
2024_01_01_000017_create_sipp_sync_logs_table.php
2024_01_01_000018_create_budget_transparency_table.php
2024_01_01_000019_create_case_statistics_table.php
2024_01_01_000020_create_ppid_requests_table.php
2024_01_01_000021_create_settings_table.php
```

### Running Migrations

```bash
# Run all migrations
php artisan migrate

# Run migrations for production
php artisan migrate --force

# Rollback last migration
php artisan migrate:rollback

# Reset all migrations
php artisan migrate:reset

# Refresh all migrations (rollback + migrate)
php artisan migrate:refresh
```

---

## Seeders

### Available Seeders

Located in `database/seeders/`:

```
DatabaseSeeder.php
UserSeeder.php
CategorySeeder.php
SettingSeeder.php
SippDataSeeder.php
```

### Running Seeders

```bash
# Seed database
php artisan db:seed

# Seed specific class
php artisan db:seed --class=UserSeeder

# Run migrations and seed
php artisan migrate:fresh --seed
```

---

## Performance Optimization

### Indexes Summary

All tables have appropriate indexes for:
- Primary keys (all tables)
- Foreign keys (relational columns)
- frequently queried columns (status, dates, slugs)
- Unique constraints (email, slug, sipp_id, request_number)

### Query Optimization Tips

1. **Use Eager Loading**
   ```php
   // Bad (N+1 queries)
   Page::all()->each(fn($page) => $page->author);

   // Good (2 queries)
   Page::with('author')->get();
   ```

2. **Select Specific Columns**
   ```php
   // Bad (retrieves all columns)
   Page::all();

   // Good (only needed columns)
   Page::select('id', 'title', 'slug')->get();
   ```

3. **Use Chunking for Large Operations**
   ```php
   // Bad (loads all into memory)
   Page::all()->each(...);

   // Good (processes in chunks)
   Page::chunk(100, function ($pages) {
       foreach ($pages as $page) {
           // Process
       }
   });
   ```

---

## Backup & Restore

### Backup Commands

```bash
# MySQL backup
mysqldump -u user -p pa_penajam_prod > backup_$(date +%Y%m%d).sql

# Compressed backup
mysqldump -u user -p pa_penajam_prod | gzip > backup_$(date +%Y%m%d).sql.gz

# SQLite backup
cp database/database.sqlite database/backup_$(date +%Y%m%d).sqlite
```

### Restore Commands

```bash
# MySQL restore
mysql -u user -p pa_penajam_prod < backup_20260118.sql

# Compressed restore
gunzip < backup_20260118.sql.gz | mysql -u user -p pa_penajam_prod

# SQLite restore
cp database/backup_20260118.sqlite database/database.sqlite
```

---

## Troubleshooting

### Common Issues

#### Issue: Foreign Key Constraint Failure

**Cause:** Referential integrity violation

**Solution:**
1. Check parent record exists
2. Verify correct ID
3. Use `DB::enableQueryLog()` to see queries

#### Issue: Duplicate Entry

**Cause:** Unique constraint violation

**Solution:**
1. Check for existing record
2. Use `firstOrCreate()` or `updateOrCreate()`
3. Validate unique fields before insert

#### Issue: JSON Column Error

**Cause:** Invalid JSON data

**Solution:**
1. Validate JSON before save
2. Use `json_encode()` and `json_decode()`
3. Check for malformed JSON

---

**Document Version:** 1.0.0
**Last Updated:** 2026-01-18
**Database Version:** Laravel 12 Compatible
