# PA Penajam Website - Technical Architecture

## System Overview

The PA Penajam website is a modern court information system built with Laravel 12, featuring a comprehensive admin panel (Filament v5) and a responsive React 19 frontend.

## Technology Stack

### Backend
- **Framework:** Laravel 12 (PHP 8.5+)
- **Admin Panel:** Filament v5
- **Authentication:** Laravel Fortify
- **Database:** SQLite (development) / MySQL 8+ (production)
- **Queue:** Redis (production) / Database (development)
- **Cache:** Redis (production) / File (development)
- **Testing:** Pest v4

### Frontend
- **Framework:** Inertia.js v2
- **UI Library:** React 19 + TypeScript
- **Styling:** Tailwind CSS v4
- **Components:** shadcn/ui
- **Animations:** Magic UI Design
- **Routing:** Laravel Wayfinder (type-safe)
- **Build Tool:** Vite

### DevOps
- **Containerization:** Laravel Sail (Docker)
- **Code Quality:** Laravel Pint, ESLint, Prettier
- **Version Control:** Git
- **CI/CD:** GitHub Actions (optional)

## Directory Structure

```
web-papenajam/
├── app/
│   ├── Actions/              # Business logic actions
│   ├── Filament/             # Filament admin resources
│   │   └── Resources/        # Admin panel resources
│   ├── Http/                 # Controllers, middleware, requests
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic services
│   │   ├── JoomlaMigration/  # Joomla data migration
│   │   └── SippApi/          # SIPP API integration
│   └── Traits/               # Reusable traits
├── bootstrap/                # Framework bootstrap files
├── config/                   # Configuration files
├── database/                 # Migrations and seeders
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── docs/                     # Documentation
│   ├── admin/                # Admin user guides
│   ├── technical/            # Technical documentation
│   └── training/             # Training materials
├── public/                   # Public web root
├── resources/                # Frontend resources
│   ├── css/                  # Tailwind CSS
│   └── js/                   # React components
│       ├── Components/       # Reusable components
│       ├── Layouts/          # Page layouts
│       ├── Pages/            # Inertia pages
│       └── Types/            # TypeScript types
├── routes/                   # Route definitions
│   ├── api.php               # API routes
│   ├── console.php           # Console routes
│   └── web.php               # Web routes
├── storage/                  # Storage (app, framework)
├── tests/                    # Test files
│   ├── Browser/              # Browser tests (Pest v4)
│   ├── Feature/              # Feature tests
│   └── Unit/                 # Unit tests
├── vendor/                   # Composer dependencies
├── .env                      # Environment configuration
├── .env.example              # Environment template
├── artisan                   # Laravel artisan CLI
├── composer.json             # PHP dependencies
├── package.json              # Node dependencies
├── vite.config.ts            # Vite configuration
└── phpunit.xml               # PHPUnit configuration
```

## Database Schema

### Core Tables

#### Users & Authentication
```sql
users
- id (primary)
- name
- email (unique)
- email_verified_at
- password
- role (enum: super_admin, admin, author, designer, subscriber)
- permissions (json)
- last_login_at (timestamp)
- profile_completed (boolean)
- remember_token
- created_at, updated_at
```

#### Pages & Page Builder
```sql
pages
- id (primary)
- title
- slug (unique)
- content (json)              # Page builder JSON
- meta_description
- page_type (enum: static, dynamic, template)
- template_id (foreign: page_templates)
- status (enum: draft, published, scheduled)
- published_at (timestamp)
- created_by (foreign: users)
- updated_by (foreign: users)
- created_at, updated_at

page_templates
- id (primary)
- name
- description
- content (json)              # Template blocks
- is_system (boolean)
- created_at, updated_at

page_blocks
- id (primary)
- page_id (foreign: pages)
- type (enum: text, image, gallery, form, etc.)
- content (json)              # Block configuration
- order (integer)
- settings (json)             # Block-specific settings
- created_at, updated_at
```

#### Menus & Navigation
```sql
menus
- id (primary)
- name
- location (enum: header, footer, sidebar, mobile)
- max_depth (integer, default: 3)
- created_at, updated_at

menu_items
- id (primary)
- menu_id (foreign: menus)
- parent_id (foreign: menu_items, nullable)
- title
- url_type (enum: route, page, custom, external)
- route_name
- page_id (foreign: pages, nullable)
- custom_url
- target (enum: _self, _blank)
- order (integer)
- is_active (boolean)
- conditional_rules (json)     # Display conditions
- created_at, updated_at
```

#### Content Management
```sql
categories
- id (primary)
- name
- slug (unique)
- description
- parent_id (foreign: categories, nullable)
- type (enum: news, document, etc.)
- created_at, updated_at

news
- id (primary)
- title
- slug (unique)
- content (longtext)
- category_id (foreign: categories)
- is_featured (boolean)
- views_count (integer, default: 0)
- published_at (timestamp)
- created_by (foreign: users)
- created_at, updated_at

documents
- id (primary)
- title
- description
- file_path
- file_size (integer)
- download_count (integer, default: 0)
- category_id (foreign: categories)
- is_public (boolean, default: true)
- published_at (timestamp)
- created_by (foreign: users)
- created_at, updated_at

document_versions
- id (primary)
- document_id (foreign: documents)
- version (integer)
- file_path
- file_size (integer)
- uploaded_by (foreign: users)
- created_at
```

#### Court Data (SIPP Integration)
```sql
court_schedules
- id (primary)
- case_number
- case_title
- sipp_case_id (foreign: sipp_cases)
- judge_id (foreign: sipp_judges)
- court_room_id (foreign: sipp_court_rooms)
- schedule_date (date)
- status (enum: scheduled, in_progress, completed, postponed)
- notes
- synced_at (timestamp)
- created_at, updated_at

sipp_cases
- id (primary)
- sipp_id (unique)            # SIPP system ID
- case_number
- case_title
- case_type_id (foreign: sipp_case_types)
- party_names (json)
- registration_date (date)
- status (enum: active, completed, archived)
- created_at, updated_at

sipp_judges
- id (primary)
- sipp_id (unique)
- name
- nip (string)
- is_active (boolean)
- created_at, updated_at

sipp_court_rooms
- id (primary)
- sipp_id (unique)
- name
- capacity (integer)
- is_active (boolean)
- created_at, updated_at

sipp_case_types
- id (primary)
- sipp_id (unique)
- name
- code
- description
- created_at, updated_at

sipp_sync_logs
- id (primary)
- sync_type (enum: full, incremental, manual)
- status (enum: success, failed, partial)
- records_processed (integer)
- records_failed (integer)
- error_message (text, nullable)
- started_at (timestamp)
- completed_at (timestamp)
- created_at
```

#### Transparency & Statistics
```sql
budget_transparency
- id (primary)
- year (integer)
- title
- description
- amount (decimal, 15, 2)
- document_path
- published_at (timestamp)
- created_by (foreign: users)
- created_at, updated_at

case_statistics
- id (primary)
- year (integer)
- month (integer)
- case_type_id (foreign: sipp_case_types)
- total_cases (integer)
- resolved_cases (integer)
- pending_cases (integer)
- average_duration (decimal, 5, 2)
- created_at, updated_at
```

#### PPID Portal
```sql
ppid_requests
- id (primary)
- request_number (unique)      # Auto-generated tracking number
- requester_name
- email
- phone (nullable)
- request_type (enum: berkala, serta_merta, setiap_saat, dikecualikan)
- description
- status (enum: pending, processed, completed, rejected)
- response (text, nullable)
- responded_at (timestamp, nullable)
- created_at, updated_at
```

#### System
```sql
settings
- id (primary)
- key (unique)
- value (json)
- type (enum: text, number, boolean, json)
- group (string)
- description
- created_at, updated_at

user_activity_logs
- id (primary)
- user_id (foreign: users)
- action (string)
- subject_type (string)
- subject_id (integer)
- changes (json, nullable)
- ip_address (string)
- user_agent (string)
- created_at
```

## Application Architecture

### Request Lifecycle

```
1. User Request
   ↓
2. Web Server (Nginx/Apache)
   ↓
3. Laravel Router (routes/web.php, routes/api.php)
   ↓
4. Middleware (Authentication, Authorization, etc.)
   ↓
5. Controller (app/Http/Controllers)
   ↓
6. Service Layer (app/Services)
   ↓
7. Models (app/Models)
   ↓
8. Database (MySQL/SQLite)
   ↓
9. Response
   - Inertia: React component (resources/js/Pages)
   - API: JSON response
   - Redirect: Route redirect
```

### Key Architectural Patterns

#### 1. Service Layer Pattern

Business logic is encapsulated in service classes:

```php
// app/Services/SippApi/SippApiClient.php
class SippApiClient
{
    public function fetchSchedules(array $filters): Collection
    {
        // API logic
    }

    public function syncScheduleData(): SyncResult
    {
        // Sync logic
    }
}
```

#### 2. Repository Pattern (Optional)

For complex queries, use repositories:

```php
// app/Repositories/CourtScheduleRepository.php
class CourtScheduleRepository
{
    public function findByDateRange(Carbon $start, Carbon $end): Collection
    {
        return CourtSchedule::query()
            ->whereBetween('schedule_date', [$start, $end])
            ->with(['judge', 'courtRoom', 'caseType'])
            ->get();
    }
}
```

#### 3. Action Classes

Discrete actions use PHP 8 single-action classes:

```php
// app/Actions/Fortify/CreateNewUser.php
class CreateNewUser
{
    public function create(array $input): User
    {
        // User creation logic
    }
}
```

#### 4. Resource Classes

Filament resources define admin interfaces:

```php
// app/Filament/Resources/PageResource.php
class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    public static function form(Form $form): Form
    {
        return $form->schema([...]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([...]);
    }
}
```

### Frontend Architecture

#### Inertia.js Integration

Inertia bridges Laravel and React:

```php
// Controller
public function index()
{
    return Inertia::render('News/Index', [
        'news' => News::latest()->paginate(12),
        'categories' => Category::all(),
    ]);
}
```

```tsx
// resources/js/Pages/News/Index.tsx
export default function NewsIndex({ news, categories }: Props) {
    return (
        <div>
            <NewsList items={news.data} />
            <Pagination meta={news.meta} />
        </div>
    );
}
```

#### React Component Structure

```
resources/js/
├── Components/           # Reusable components
│   ├── ui/              # shadcn/ui components
│   ├── layouts/         # Layout components
│   └── forms/           # Form components
├── Layouts/             # Inertia layouts
│   ├── AuthLayout.tsx   # Authenticated layout
│   └── PublicLayout.tsx # Public layout
├── Pages/               # Inertia pages
│   ├── Home.tsx
│   ├── News/
│   │   ├── Index.tsx
│   │   └── Show.tsx
│   └── ...
└── Types/               # TypeScript types
```

#### State Management

- **Server State:** Managed by Inertia (props from Laravel)
- **Client State:** React hooks (useState, useContext)
- **Form State:** Inertia's useForm hook

#### Type Safety with Wayfinder

Wayfinder generates TypeScript types for routes:

```typescript
import { show } from '@/actions/App/Http/Controllers/NewsController'

show.url(1) // "/news/1"
```

### Authentication & Authorization

#### Role-Based Access Control (RBAC)

5-tier role system:

1. **Super Admin** - Full access
2. **Admin** - Admin features, no user management
3. **Author** - Create/edit content, publish with approval
4. **Designer** - Page builder, menu management
5. **Subscriber** - Read-only

#### Laravel Policies

Policies define authorization logic:

```php
// app/Policies/PagePolicy.php
class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'author', 'designer']);
    }

    public function update(User $user, Page $page): bool
    {
        return in_array($user->role, ['super_admin', 'admin'])
            || ($user->role === 'author' && $page->created_by === $user->id);
    }
}
```

#### Middleware

- **auth** - Require authentication
- **verified** - Require email verification
- **role:super_admin,admin** - Require specific role
- **can:publish,page** - Require policy permission

### Caching Strategy

#### Cache Drivers

- **Development:** File cache
- **Production:** Redis cache

#### Cache Keys

```
pages.{slug}              # Page content
menus.{location}          # Menu structure
categories.{type}         # Category trees
settings                  # System settings
sipp.schedules.{date}     # Court schedules
```

#### Cache Invalidation

- **Manual:** `Cache::forget('key')`
- **Tags:** `Cache::tags(['pages'])->flush()`
- **Events:** Listeners on model events

### Queue System

#### Queue Drivers

- **Development:** Database queue
- **Production:** Redis queue

#### Queued Jobs

```php
// app/Jobs/SyncSippData.php
class SyncSippData implements ShouldQueue
{
    public function handle(): void
    {
        // Sync logic
    }
}
```

#### Scheduled Tasks

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    $schedule->job(new SyncSippData)->hourly();
    $schedule->command('cache:prune-stale-tags')->daily();
}
```

## API Integration

### SIPP API Integration

#### SippApiClient Service

```php
// app/Services/SippApi/SippApiClient.php
class SippApiClient
{
    private string $baseUrl;
    private string $apiKey;

    public function fetchSchedules(array $params): array
    {
        $response = Http::withToken($this->apiKey)
            ->get("{$this->baseUrl}/schedules", $params);

        return $response->json();
    }
}
```

#### Data Synchronization

```php
// app/Services/SippApi/SippDataSync.php
class SippDataSync
{
    public function syncDaily(): SyncResult
    {
        $schedules = $this->api->fetchSchedules([
            'start_date' => today(),
            'end_date' => today()->addDays(7),
        ]);

        foreach ($schedules as $schedule) {
            CourtSchedule::updateOrCreate(
                ['sipp_case_id' => $schedule['id']],
                $schedule
            );
        }
    }
}
```

## Performance Optimization

### Database Optimization

#### Indexing

```sql
-- Pages
CREATE INDEX idx_pages_slug ON pages(slug);
CREATE INDEX idx_pages_status ON pages(status);
CREATE INDEX idx_pages_published_at ON pages(published_at);

-- Menu Items
CREATE INDEX idx_menu_items_menu_id ON menu_items(menu_id);
CREATE INDEX idx_menu_items_parent_id ON menu_items(parent_id);
CREATE INDEX idx_menu_items_order ON menu_items(order);

-- Court Schedules
CREATE INDEX idx_court_schedules_date ON court_schedules(schedule_date);
CREATE INDEX idx_court_schedules_status ON court_schedules(status);
```

#### Eager Loading

```php
// Prevents N+1 queries
Page::with('blocks', 'author', 'template')->get();

CourtSchedule::with(['judge', 'courtRoom', 'caseType'])->get();
```

### Frontend Optimization

#### Code Splitting

```typescript
// Lazy load pages
const NewsShow = lazy(() => import('@/Pages/News/Show'));
```

#### Image Optimization

- Use WebP format
- Responsive images with `srcset`
- Lazy loading with `loading="lazy"`

#### Bundle Size

- Tree-shaking with Wayfinder
- Dynamic imports for large components
- Minification in production

### Caching Layers

1. **HTTP Cache** - Browser caching (headers)
2. **Vite Cache** - Build artifacts
3. **Laravel Cache** - Application cache
4. **Database Cache** - Query cache
5. **CDN** - Static assets (optional)

## Security

### Authentication

- **Laravel Fortify** - Headless auth backend
- **2FA** - Two-factor authentication (optional)
- **Session Management** - Secure session handling
- **Password Hashing** - bcrypt/hashing

### Authorization

- **Policies** - Resource-based authorization
- **Gates** - Custom authorization logic
- **Middleware** - Route-level protection
- **Roles** - Role-based access control

### Data Protection

- **CSRF Protection** - Token verification
- **XSS Protection** - Input sanitization
- **SQL Injection** - Eloquent ORM protection
- **File Upload** - Validation and scanning

### Configuration

```php
// config/fortify.php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::twoFactorAuthentication(),
],
```

## Testing Strategy

### Unit Tests

Test individual components:

```php
// tests/Unit/Models/PageTest.php
it('has a slug', function () {
    $page = Page::factory()->create(['title' => 'Test Page']);
    expect($page->slug)->toBe('test-page');
});
```

### Feature Tests

Test user workflows:

```php
// tests/Feature/PageManagementTest.php
it('can create a page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post('/admin/pages', [
            'title' => 'New Page',
            'content' => [],
        ])
        ->assertRedirect();
});
```

### Browser Tests (Pest v4)

Test end-to-end:

```php
// tests/Browser/AdminPanelTest.php
it('can login to admin panel', function () {
    visit('/admin')
        ->fill('email', 'admin@example.com')
        ->fill('password', 'password')
        ->click('Login')
        ->assertSee('Dashboard');
});
```

### Test Coverage Goals

- **Unit Tests:** 90%+ coverage
- **Feature Tests:** 100% critical path coverage
- **Browser Tests:** Key user journeys

## Deployment

### Server Requirements

- **PHP:** 8.5+
- **Database:** MySQL 8+ or PostgreSQL 12+
- **Web Server:** Nginx or Apache
- **PHP Extensions:** mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath
- **Composer:** 2.x
- **Node.js:** 20.x (for build)

### Environment Setup

```bash
# Clone repository
git clone <repository-url>
cd web-papenajam

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# Environment configuration
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --force
php artisan db:seed --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Deployment Process

1. **Prepare:** Run tests locally
2. **Backup:** Backup database and files
3. **Deploy:** Pull latest code
4. **Build:** Install dependencies and build assets
5. **Migrate:** Run database migrations
6. **Cache:** Clear and warm cache
7. **Verify:** Smoke tests

### Monitoring

- **Application Logs:** `storage/logs/laravel.log`
- **Queue Workers:** Supervisor monitoring
- **Server Metrics:** CPU, memory, disk usage
- **Error Tracking:** Sentry/Bugsnag (optional)

## Maintenance

### Regular Tasks

- **Daily:** Review error logs
- **Weekly:** Check disk space, update dependencies
- **Monthly:** Review analytics, backup database
- **Quarterly:** Security audit, performance review

### Backup Strategy

- **Database:** Daily backups, retain 30 days
- **Storage:** Weekly backups, retain 4 weeks
- **Code:** Git versioning
- **Off-site:** Cloud storage for critical backups

---

**Document Version:** 1.0.0
**Last Updated:** 2026-01-18
**Maintainer:** Development Team
