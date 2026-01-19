# Database Quick Reference
## Website Pengadilan Agama Penajam - Laravel 12

---

## 🗄️ Database Tables Overview

### User Management
| Table | Purpose | Key Features |
|-------|---------|--------------|
| `users` | Authentication & Authorization | 5-role system, 2FA, permissions JSON |
| `user_activity_logs` | Audit Trail | IP tracking, action logging, metadata JSON |

### Content Management
| Table | Purpose | Key Features |
|-------|---------|--------------|
| `pages` | Page Builder System | Dual content (legacy + builder), version control, SEO |
| `page_templates` | Reusable Templates | System/user templates, JSON structure |
| `page_blocks` | Page Components | Modular blocks, settings JSON, ordering |
| `categories` | Content Categorization | Hierarchical, type-based, icon support |
| `news` | News & Announcements | Tagging, featured, view tracking |
| `documents` | File Management | Version control, checksums, download tracking |
| `document_versions` | Document History | Version tracking, file integrity |

### Navigation
| Table | Purpose | Key Features |
|-------|---------|--------------|
| `menus` | Menu Management | Location-based (header/footer/sidebar/mobile) |
| `menu_items` | Menu Items | Hierarchical, URL types, conditional display |

### SIPP Integration
| Table | Purpose | Key Features |
|-------|---------|--------------|
| `court_schedules` | Court Schedules | External ID mapping, parties JSON, sync tracking |
| `sipp_cases` | Case Cache | Complete case data, sync tracking |
| `sipp_judges` | Master Data | Judge reference data |
| `sipp_court_rooms` | Master Data | Room reference data |
| `sipp_case_types` | Master Data | Case type reference |
| `sipp_sync_logs` | Sync Tracking | Batch sync monitoring, error tracking |

### Public Transparency
| Table | Purpose | Key Features |
|-------|---------|--------------|
| `budget_transparency` | Budget Data | Year-based, decimal precision, categories |
| `case_statistics` | Statistics | Aggregated data, external hash, performance metrics |
| `ppid_requests` | PPID Portal | Request tracking, SLA monitoring, attachments JSON |

### System
| Table | Purpose | Key Features |
|-------|---------|--------------|
| `settings` | Configuration | Type casting, public/private, groups |
| `joomla_migrations` | Migration Tracking | Batch tracking, error handling |
| `cache` | Cache Storage | Laravel cache system |
| `jobs` | Queue Jobs | Laravel queue system |

---

## 🔗 Key Relationships

```
users (1:N)
├── pages.author_id
├── pages.last_edited_by
├── news.author_id
├── documents.uploaded_by
├── budget_transparency.author_id
├── ppid_requests.processed_by
└── user_activity_logs.user_id

pages (1:N)
├── page_templates.template_id
└── page_blocks.page_id

menus (1:N)
└── menu_items.menu_id

menu_items (1:N, self-ref)
├── parent_id → menu_items.id
└── children ← menu_items.parent_id

categories (1:N, self-ref)
├── parent_id → categories.id
├── children ← categories.parent_id
├── news.category_id
└── documents.category_id

documents (1:N)
└── document_versions.document_id
```

---

## 📊 Important Enums

### User Roles
- `super_admin` - Full access
- `admin` - Content + user management
- `author` - Content creation
- `designer` - Page builder + templates
- `subscriber` - Read-only

### Page Status
- `draft` - Not published
- `published` - Live
- `archived` - Archived

### Menu Locations
- `header` - Top navigation
- `footer` - Footer navigation
- `sidebar` - Sidebar navigation
- `mobile` - Mobile navigation

### URL Types
- `route` - Laravel named route
- `page` - Dynamic page
- `custom` - Internal URL
- `external` - External URL

### Sync Status
- `pending` - Awaiting sync
- `success` - Synced successfully
- `error` - Sync failed

### PPID Status
- `submitted` - New request
- `reviewed` - Under review
- `processed` - Being processed
- `completed` - Resolved
- `rejected` - Rejected

---

## 🎯 Common Query Patterns

### Get Published Pages
```php
use App\Models\Page;

$pages = Page::published()
    ->with(['author', 'template'])
    ->orderBy('published_at', 'desc')
    ->get();
```

### Get Menu Tree
```php
use App\Models\Menu;

$menu = Menu::byLocation(MenuLocation::Header)->first();
$tree = $menu->getTree(); // Hierarchical array
```

### Get News by Category
```php
use App\Models\News;

$news = News::published()
    ->byCategory($categoryId)
    ->with('category')
    ->get();
```

### Get Court Schedules by Date
```php
use App\Models\CourtSchedule;

$schedules = CourtSchedule::byDate($date)
    ->with(['judge', 'room'])
    ->orderBy('schedule_time')
    ->get();
```

### Get Pending PPID Requests
```php
use App\Models\PpidRequest;

$requests = PpidRequest::pending()
    ->highPriority()
    ->with('processor')
    ->get();
```

---

## 📝 Model Helper Methods

### Page Model
```php
$page->isPublished()          // Check if published
$page->getUrl()               // Get page URL
$page->getMetaDescription()   // Get SEO description
$page->incrementViews()       // Increment view count
$page->incrementVersion()     // Increment version
$page->isBuilderEnabled()     // Check if builder enabled
```

### MenuItem Model
```php
$item->getUrl()              // Get item URL
$item->isActive($currentPath) // Check if active
$item->hasChildren()         // Check if has children
$item->withChildren()        // Get with children (tree)
```

### Document Model
```php
$doc->getHumanFileSize()     // Get readable file size
$doc->getFileUrl()           // Get file URL
$doc->validateChecksum()     // Verify file integrity
$doc->incrementDownloads()   // Increment download count
```

### CourtSchedule Model
```php
$schedule->isUpcoming()      // Check if upcoming
$schedule->isToday()         // Check if today
$schedule->getFormattedDate() // Get formatted date
$schedule->getParties()      // Get parties array
```

### PpidRequest Model
```php
$request->getDaysPending()   // Get days pending
$request->getAttachmentCount() // Get attachment count
$request->markAsResponded($user, $response) // Mark complete
PpidRequest::generateRequestNumber() // Generate unique number
```

---

## 🔧 Database Migrations

### Run All Migrations
```bash
php artisan migrate
```

### Fresh Migration (Warning: Deletes Data)
```bash
php artisan migrate:fresh
```

### Fresh with Seeders
```bash
php artisan migrate:fresh --seed
```

### Rollback Last Migration
```bash
php artisan migrate:rollback
```

### Check Migration Status
```bash
php artisan migrate:status
```

---

## 🧪 Testing

### Run All Tests
```bash
php artisan test --compact
```

### Run Model Tests
```bash
php artisan test --compact tests/Feature/Models/
```

### Run Specific Test
```bash
php artisan test --compact --filter=PageModelTest
```

### Run with Coverage
```bash
php artisan test --coverage
```

---

## 📊 Database Statistics

### Quick Stats
```bash
# Total tables: 23
# Total migrations: 28
# Total models: 21
# Total factories: 22
# Total relationships: 40+
# Total indexes: 50+
# Total enums: 15+
# JSON columns: 12+
```

### Table Sizes (Approximate)
```sql
SELECT
    name,
    (SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name=main.name) as row_count
FROM sqlite_master
WHERE type='table'
ORDER BY row_count DESC;
```

---

## 🔍 Troubleshooting

### Migration Errors
```bash
# Check current migration status
php artisan migrate:status

# Rollback and retry
php artisan migrate:rollback
php artisan migrate
```

### Model Relationship Issues
```bash
# Test relationships in tinker
php artisan tinker

>>> $page = Page::with('author')->first();
>>> $page->author->name;
```

### JSON Column Issues
```php
// Ensure JSON is properly cast
protected function casts(): array
{
    return [
        'content' => 'array',
        'meta' => 'array',
    ];
}
```

### Index Performance
```sql
-- Check index usage
EXPLAIN QUERY PLAN SELECT * FROM pages WHERE status = 'published';

-- Add missing index if needed
CREATE INDEX idx_pages_status ON pages(status);
```

---

## 📚 Documentation Files

| Document | Path | Purpose |
|----------|------|---------|
| ERD | `/docs/ERD.md` | Complete database schema |
| Completion Report | `/docs/PH1.1_DATABASE_COMPLETION_REPORT.md` | Detailed completion report |
| Architect Summary | `/docs/PH1.1_DATABASE_ARCHITECT_SUMMARY.md` | Architecture overview |
| PRD | `/docs/PRD.md` | Requirements (Section 3: Database) |
| SIPP Analysis | `/docs/SIPP_WEB_TABLES_ANALYSIS.md` | SIPP integration |
| API Design | `/docs/API_INTEGRATION_DESIGN.md` | API architecture |
| Joomla Mapping | `/docs/JOOMLA_DATA_MAPPING.md` | Migration strategy |

---

## 🚀 Quick Commands

### Database Operations
```bash
# Migrate
php artisan migrate

# Fresh start
php artisan migrate:fresh --seed

# Check status
php artisan migrate:status

# Tinker (test queries)
php artisan tinker
```

### Model Operations
```bash
# Create model
php artisan make:model ModelName

# Create model with migration and factory
php artisan make:model ModelName -mf

# Create factory
php artisan make:factory ModelNameFactory
```

### Testing
```bash
# Run tests
php artisan test --compact

# Run specific test
php artisan test --compact --filter=TestName

# Run with coverage
php artisan test --coverage
```

### Code Quality
```bash
# Format code
vendor/bin/pint

# Check formatting
vendor/bin/pint --test
```

---

## 🎯 Best Practices

### Query Optimization
```php
// ✅ GOOD - Eager loading
$pages = Page::with('author')->get();

// ❌ BAD - N+1 problem
$pages = Page::all();
foreach ($pages as $page) {
    $page->author; // Queries every time
}
```

### JSON Columns
```php
// ✅ GOOD - Proper casting
protected function casts(): array
{
    return ['content' => 'array'];
}

// ❌ BAD - Manual JSON decode
$content = json_decode($this->attributes['content']);
```

### Relationships
```php
// ✅ GOOD - Type hints
public function author(): BelongsTo
{
    return $this->belongsTo(User::class, 'author_id');
}

// ❌ BAD - No type hint
public function author()
{
    return $this->belongsTo(User::class);
}
```

---

## 📞 Support

### For Issues
1. Check `/docs/ERD.md` for schema details
2. Review `/docs/PH1.1_DATABASE_COMPLETION_REPORT.md` for implementation
3. Run tests to verify: `php artisan test --compact`
4. Check logs: `storage/logs/laravel.log`

### For Questions
- Review model files in `/app/Models/`
- Check migration files in `/database/migrations/`
- Review test files in `/tests/Feature/Models/`

---

**Last Updated:** 2026-01-18
**Status:** ✅ Production Ready
**Version:** Laravel 12 / PHP 8.5+
