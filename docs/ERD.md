# ERD - Pengadilan Agama Penajam Website

**Project:** Website Pengadilan Agama Penajam
**Version:** 1.0
**Last Updated:** 2026-01-18
**Database:** MySQL 8.0+ / SQLite (dev)

---

## 📋 Entity Relationship Diagram

### Core Tables Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER MANAGEMENT                         │
└─────────────────────────────────────────────────────────────────┘

users
├── id (PK)
├── name
├── email
├── email_verified_at
├── password
├── two_factor_secret
├── two_factor_recovery_codes
├── remember_token
├── role (enum: super_admin, admin, author, designer, subscriber)
├── permissions (JSON)
├── last_login_at
├── profile_completed (boolean)
├── created_at
└── updated_at

user_activity_logs
├── id (PK)
├── user_id (FK → users.id)
├── action (string)
├── description (text)
├── ip_address
├── user_agent
├── metadata (JSON)
└── created_at

└─────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────┐
│                         PAGE BUILDER                            │
└─────────────────────────────────────────────────────────────────┘

pages
├── id (PK)
├── title
├── slug (unique)
├── content (JSON) ← Page Builder data
├── meta_description
├── meta_keywords
├── status (enum: draft, published, archived)
├── page_type (enum: static, dynamic, template)
├── template_id (FK → page_templates.id, nullable)
├── author_id (FK → users.id)
├── published_at
├── view_count
├── created_at
└── updated_at

page_templates
├── id (PK)
├── name
├── description
├── content (JSON) ← Template structure
├── is_system (boolean)
├── thumbnail
├── created_by (FK → users.id)
├── created_at
└── updated_at

page_blocks
├── id (PK)
├── page_id (FK → pages.id)
├── type (enum: text, image, gallery, form, video, html, etc.)
├── content (JSON) ← Block-specific data
├── settings (JSON) ← Styling, layout options
├── order (integer)
├── created_at
└── updated_at

└─────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────┘
│                         MENU SYSTEM                             │
└─────────────────────────────────────────────────────────────────┘

menus
├── id (PK)
├── name
├── location (enum: header, footer, sidebar, mobile)
├── max_depth (integer) ← Maximum nesting level
├── description
├── created_at
└── updated_at

menu_items
├── id (PK)
├── menu_id (FK → menus.id)
├── parent_id (FK → menu_items.id, nullable) ← For hierarchy
├── title
├── url_type (enum: route, page, custom, external)
├── route_name (string, nullable)
├── page_id (FK → pages.id, nullable)
├── custom_url (string, nullable)
├── icon (string, nullable)
├── order (integer)
├── is_active (boolean)
├── target_blank (boolean)
├── conditions (JSON) ← Conditional display rules
├── created_at
└── updated_at

└─────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────┘
│                         CONTENT MANAGEMENT                      │
└─────────────────────────────────────────────────────────────────┘

categories
├── id (PK)
├── name
├── slug (unique)
├── description
├── parent_id (FK → categories.id, nullable)
├── type (enum: news, document, page, budget)
├── icon (string, nullable)
├── order (integer)
├── created_at
└── updated_at

news
├── id (PK)
├── title
├── slug (unique)
├── excerpt
├── content (text)
├── category_id (FK → categories.id)
├── author_id (FK → users.id)
├── is_featured (boolean)
├── is_published (boolean)
├── published_at
├── views_count (integer)
├── thumbnail
├── meta_description
├── tags (JSON) ← Array of tags
├── created_at
└── updated_at

documents
├── id (PK)
├── title
├── description
├── file_path
├── file_name
├── file_size (integer)
├── file_type (string)
├── mime_type
├── category_id (FK → categories.id)
├── uploaded_by (FK → users.id)
├── download_count (integer)
├── is_public (boolean)
├── published_at
├── version (string)
├── checksum (string) ← For integrity verification
├── created_at
└── updated_at

└─────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────┘
│                         COURT SCHEDULE (SIPP)                   │
└─────────────────────────────────────────────────────────────────┘

court_schedules
├── id (PK)
├── external_id (string) ← ID from SIPP API
├── case_number
├── case_title
├── case_type
├── register_date
├── register_number
├── case_status (enum: pending, in_progress, postponed, closed)
├── judge_id (FK → sipp_judges.id, nullable)
├── judge_name (string) ← From API
├── room (string)
├── room_code (string)
├── schedule_date
├── schedule_time
├── schedule_status (enum: scheduled, postponed, cancelled, completed)
├── parties (JSON) ← {penggugat, tergugat, kuasa_hukum}
├── notes
├── last_sync_at
├── sync_status (enum: pending, success, error)
├── created_at
└── updated_at

sipp_cases
├── id (PK)
├── external_id (string)
├── case_number
├── case_title
├── case_type
├── register_date
├── register_number
├── case_status
├── priority (enum: normal, high, urgent)
├── plaintiff (JSON)
├── defendant (JSON)
├── attorney (JSON)
├── subject_matter
├── last_hearing_date
├── next_hearing_date
├── final_decision_date
├── decision_summary
├── document_references (JSON)
├── last_sync_at
├── sync_status
├── created_at
└── updated_at

sipp_judges
├── id (PK)
├── external_id (string)
├── judge_code
├── full_name
├── title
├── specialization
├── chamber
├── is_active (boolean)
├── last_sync_at
└── created_at

sipp_court_rooms
├── id (PK)
├── external_id (string)
├── room_code
├── room_name
├── building
├── capacity (integer)
├── facilities (JSON)
├── is_active (boolean)
├── last_sync_at
└── created_at

sipp_case_types
├── id (PK)
├── external_id (string)
├── type_code
├── type_name
├── category (enum: perdata, pidana, agama)
├── legal_basis
├── procedure_type
├── is_active (boolean)
└── created_at

sipp_sync_logs
├── id (PK)
├── sync_type (enum: full, incremental)
├── start_time
├── end_time
├── records_fetched (integer)
├── records_updated (integer)
├── records_created (integer)
├── error_message (text, nullable)
├── created_by (enum: system, user)
├── metadata (JSON)
└── created_at

└─────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────┘
│                         PUBLIC TRANSPARENCY                     │
└─────────────────────────────────────────────────────────────────┘

budget_transparency
├── id (PK)
├── year (integer)
├── title
├── description
├── amount (decimal)
├── document_path
├── document_name
├── category (enum: apbn, apbd, other)
├── published_at
├── author_id (FK → users.id)
├── created_at
└── updated_at

case_statistics
├── id (PK)
├── year (integer)
├── month (integer)
├── case_type
├── court_type (enum: perdata, pidana, agama)
├── total_filed (integer)
├── total_resolved (integer)
├── pending_carryover (integer)
├── avg_resolution_days (decimal)
├── settlement_rate (decimal)
├── external_data_hash (string)
├── last_sync_at
├── created_at
└── updated_at

└─────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────┘
│                         PPID (PUBLIC INFORMATION)               │
└─────────────────────────────────────────────────────────────────┘

ppid_requests
├── id (PK)
├── requester_name
├── email
├── phone
├── request_type (enum: information, document, clarification)
├── description (text)
├── status (enum: pending, processed, completed, rejected)
├── response (text, nullable)
├── responded_at
├── responded_by (FK → users.id, nullable)
├── attachments (JSON) ← Array of file paths
├── priority (enum: normal, high)
├── notes (JSON) ← Internal notes
├── created_at
└── updated_at

└─────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────┘
│                         JOOMLA MIGRATION                        │
└─────────────────────────────────────────────────────────────────┘

joomla_migrations
├── id (PK)
├── source_table (enum: content, categories, menu, images, users)
├── source_id (integer) ← Joomla ID
├── target_id (integer) ← Laravel ID
├── data_hash (string) ← For change detection
├── migration_status (enum: pending, success, failed)
├── error_message (text, nullable)
├── migrated_at
└── created_at

└─────────────────────────────────────────────────────────────────┘
```

---

## 🔗 Relationships Summary

### One-to-Many Relationships

| Parent | Child | Foreign Key | Description |
|--------|-------|-------------|-------------|
| `users` | `pages` | `author_id` | User authored pages |
| `users` | `news` | `author_id` | User authored news |
| `users` | `documents` | `uploaded_by` | User uploaded documents |
| `users` | `user_activity_logs` | `user_id` | User activity logs |
| `users` | `budget_transparency` | `author_id` | User created budget entries |
| `users` | `ppid_requests` | `responded_by` | User responded to PPID |
| `menus` | `menu_items` | `menu_id` | Menu items belong to menu |
| `menu_items` | `menu_items` | `parent_id` | Nested menu items (hierarchy) |
| `pages` | `page_blocks` | `page_id` | Blocks belong to page |
| `pages` | `page_templates` | `template_id` | Page uses template |
| `categories` | `news` | `category_id` | News belongs to category |
| `categories` | `documents` | `category_id` | Document belongs to category |
| `categories` | `categories` | `parent_id` | Nested categories |
| `sipp_judges` | `court_schedules` | `judge_id` | Schedule belongs to judge |
| `sipp_judges` | `sipp_cases` | `judge_id` | Case belongs to judge |

### Many-to-Many Relationships (via JSON)

| Entity 1 | Entity 2 | Field | Description |
|----------|----------|-------|-------------|
| `news` | `tags` | `tags (JSON)` | News with multiple tags |
| `documents` | `tags` | `tags (JSON)` | Documents with tags |
| `court_schedules` | `parties` | `parties (JSON)` | Multiple parties in case |
| `sipp_cases` | `document_references` | `document_references (JSON)` | Multiple documents |

---

## 📊 Indexes & Performance

### Recommended Indexes

```sql
-- users
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_last_login ON users(last_login_at);

-- pages
CREATE INDEX idx_pages_slug ON pages(slug);
CREATE INDEX idx_pages_status ON pages(status);
CREATE INDEX idx_pages_published ON pages(published_at);
CREATE INDEX idx_pages_author ON pages(author_id);

-- menu_items
CREATE INDEX idx_menu_items_menu ON menu_items(menu_id);
CREATE INDEX idx_menu_items_parent ON menu_items(parent_id);
CREATE INDEX idx_menu_items_order ON menu_items(order);

-- news
CREATE INDEX idx_news_slug ON news(slug);
CREATE INDEX idx_news_category ON news(category_id);
CREATE INDEX idx_news_published ON news(published_at);
CREATE INDEX idx_news_featured ON news(is_featured);

-- court_schedules
CREATE INDEX idx_court_schedules_date ON court_schedules(schedule_date);
CREATE INDEX idx_court_schedules_external ON court_schedules(external_id);
CREATE INDEX idx_court_schedules_sync ON court_schedules(sync_status);

-- ppid_requests
CREATE INDEX idx_ppid_requests_status ON ppid_requests(status);
CREATE INDEX idx_ppid_requests_email ON ppid_requests(email);
CREATE INDEX idx_ppid_requests_created ON ppid_requests(created_at);
```

### JSON Indexes (MySQL 8.0+)

```sql
-- For searching within JSON columns
CREATE INDEX idx_pages_content_title ON pages((CAST(content->>'$.title' AS CHAR(255))));
CREATE INDEX idx_news_tags ON news((CAST(tags AS CHAR(255))));
```

---

## 🔐 Security Considerations

### Data Privacy
1. **User passwords** - Hashed with bcrypt
2. **Two-factor secrets** - Encrypted at rest
3. **PPID requests** - Access controlled by role
4. **Audit logs** - IP addresses and user agents stored

### Access Control
- **Super Admin** - Full access
- **Admin** - Content + user management
- **Author** - Content creation only
- **Designer** - Page builder + templates
- **Subscriber** - Read-only access

### Data Retention
- **Activity logs** - 90 days
- **Sync logs** - 1 year
- **PPID requests** - Permanent (legal requirement)
- **User sessions** - 30 days

---

## 🔄 Data Flow

### SIPP Integration Flow

```
SIPP API → SippApiClient → Data Sync Service → Database
    ↓           ↓               ↓               ↓
  JSON      Validation     Transformation   Eloquent
```

### Page Builder Flow

```
Admin UI → Page Blocks → JSON Content → Frontend Renderer
    ↓          ↓            ↓              ↓
Filament  Components   pages.content  Inertia/React
```

### Menu Rendering Flow

```
Database → Menu Service → Hierarchical Tree → Frontend Menu
    ↓          ↓              ↓                ↓
menus     buildTree()    Recursive      React Component
```

---

## 📝 Migration Strategy

### Phase 1: Core Tables (Completed)
- ✅ Users & authentication
- ✅ Pages & templates
- ✅ Menus & navigation
- ✅ Categories
- ✅ News & documents
- ✅ Court schedules (SIPP)
- ✅ Budget transparency
- ✅ Case statistics
- ✅ PPID requests

### Phase 2: Relationships & Constraints
- Foreign keys
- Cascade deletes
- Unique constraints
- Check constraints

### Phase 3: Indexes & Optimization
- Performance indexes
- Full-text search
- Query optimization

### Phase 4: Data Migration
- Joomla → Laravel mapping
- Data validation
- Rollback procedures

---

## 🧪 Testing Strategy

### Factory Data
- Users with all 5 roles
- Pages with various statuses
- Menu hierarchies (3+ levels)
- News with categories & tags
- Court schedules with SIPP data
- PPID requests in all statuses

### Test Scenarios
1. **CRUD operations** for all entities
2. **Relationship integrity** (cascade deletes)
3. **Unique constraints** (slugs, emails)
4. **Data validation** (required fields, formats)
5. **Performance** (N+1 queries, indexes)

---

## 📚 References

- **PRD:** `docs/PRD.md` (Section 3: Database Schema Inti)
- **SIPP Analysis:** `docs/SIPP_WEB_TABLES_ANALYSIS.md`
- **API Design:** `docs/API_INTEGRATION_DESIGN.md`
- **Ralph Tasks:** `docs/RALPH_LOOP_TASKS.md`
- **Roadmap:** `docs/RALPH_ROADMAP.md`

---

## ✅ Acceptance Criteria

- [ ] All tables designed with proper columns
- [ ] All relationships defined
- [ ] Indexes documented for performance
- [ ] Security considerations documented
- [ ] Data flow diagrams created
- [ ] Migration strategy defined
- [ ] Testing strategy documented

---

**Document Status:** ✅ Complete
**Last Review:** 2026-01-18
**Next Step:** Create Laravel migrations (PH1.1.2)
