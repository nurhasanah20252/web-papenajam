# Rancangan Website Pengadilan Agama Penajam - Migrasi dari Joomla 3

## **1. Konteks & Latar Belakang**
- Migrasi dari Joomla 3 ke Laravel 12 + Inertia.js + Filament
- Data Joomla sudah diekspor dalam format JSON
- Fokus pada: Page Builder, Menu Dinamis, dan fitur website pengadilan
- Developer-friendly: semua konfigurasi melalui dashboard admin

## **2. Arsitektur Teknologi**

### **Backend:**
- **Framework:** Laravel 12 (PHP 8.5+)
- **Admin Panel:** Filament v5
- **Database:** SQLite (dev) / MySQL (production)
- **Authentication:** Laravel Fortify dengan 5 role level

### **Frontend Public:**
- **Framework:** Inertia.js v2 + React 19 + TypeScript
- **UI Components:** shadcn/ui untuk komponen konsisten
- **UX/Animations:** Magic UI Design untuk efek visual dan interaksi
- **Styling:** Tailwind CSS v4
- **Routing:** Wayfinder untuk type-safe routing

### **Testing & Quality:**
- **Testing:** Pest v4 dengan browser testing
- **Code Quality:** Laravel Pint, ESLint, Prettier
- **Build Tool:** Vite

## **3. Database Schema Inti**

### **3.1 Pages & Page Builder**
```php
// Pages
- id, title, slug, content (JSON untuk page builder), meta_description, status
- page_type (static, dynamic, template), template_id (nullable)
- published_at, created_by, updated_by

// Page Templates
- id, name, description, content (JSON), is_system (boolean)

// Page Blocks (untuk page builder)
- id, page_id, type (text, image, gallery, form, etc.)
- content (JSON), order, settings (JSON)
```

### **3.2 Menus & Navigation**
```php
// Menus
- id, name, location (header, footer, sidebar, mobile), max_depth

// Menu Items
- id, menu_id, parent_id, title, url_type (route, page, custom)
- route_name, page_id, custom_url, order, is_active
```

### **3.3 Content Management**
```php
// News & Announcements
- id, title, slug, content, category_id, published_at
- is_featured, views_count, created_by

// Categories
- id, name, slug, description, parent_id, type (news, document, etc.)

// Documents & Downloads
- id, title, description, file_path, file_size, download_count
- category_id, published_at, is_public

// Court Schedules
- id, case_number, case_title, judge, room, schedule_date, status
- notes, created_by
```

### **3.4 Public Transparency & PPID**
```php
// Budget Transparency
- id, year, title, description, amount, document_path
- published_at, created_by

// Case Statistics
- id, year, month, case_type, total_cases, resolved_cases
- pending_cases, average_duration

// PPID Requests
- id, requester_name, email, request_type, description
- status (pending, processed, completed), response, responded_at
```

### **3.5 User Management**
```php
// Extending Laravel's User model
- role (super_admin, admin, author, designer, subscriber)
- permissions (JSON untuk custom permissions)
- last_login_at, profile_completed
```

## **4. API SIPP Integration**

### **4.1 Database Schema untuk API Integration**

#### **Court Schedule System (Revisi):**
```php
// court_schedules table - akan diisi dari API SIPP
- id, external_id (ID dari SIPP), case_number, case_title
- case_type, register_date, register_number, case_status
- judge_id, judge_name (from API), room, room_code
- schedule_date, schedule_time, schedule_status (scheduled/postponed/etc)
- parties (JSON: penggugat, tergugat, kuasa_hukum)
- last_sync_at, sync_status (pending/success/error)
- created_at, updated_at

// court_schedule_sync_logs table
- id, sync_type (full/incremental), start_time, end_time
- records_fetched, records_updated, records_created
- error_message (nullable), created_by (system/user)
```

#### **Case Data & Statistics:**
```php
// cases table (cache dari API SIPP)
- id, external_id, case_number, case_title, case_type
- register_date, register_number, case_status, priority
- plaintiff, defendant, attorney, subject_matter
- last_hearing_date, next_hearing_date, final_decision_date
- decision_summary, document_references (JSON)
- last_sync_at, sync_status

// case_statistics table (aggregate dari API)
- id, year, month, case_type, court_type
- total_filed, total_resolved, pending_carryover
- avg_resolution_days, settlement_rate
- external_data_hash (untuk detect changes)
- last_sync_at
```

#### **Master Data References:**
```php
// judges table
- id, external_id, judge_code, full_name, title
- specialization, chamber, is_active, last_sync_at

// court_rooms table
- id, external_id, room_code, room_name, building
- capacity, facilities (JSON), is_active, last_sync_at

// case_types table
- id, external_id, type_code, type_name, category
- legal_basis, procedure_type, is_active
```

### **4.2 API Integration Architecture**

#### **Service Pattern:**
```php
// app/Services/SippApiClient.php
- Base API client dengan retry logic dan error handling
- Support untuk multiple endpoint dengan configurable base URL
- Authentication management (API key, OAuth, etc.)
- Rate limiting dan request throttling
- Response caching dengan TTL configurable

// app/Services/SippDataSync.php
- Scheduled synchronization service
- Incremental vs full sync strategies
- Conflict resolution (last-write-wins atau manual review)
- Data validation dan transformation
- Notification system untuk sync failures
```

#### **Sync Strategies:**
- **Real-time Polling:** Untuk jadwal sidang (setiap 15-30 menit)
- **Daily Aggregation:** Untuk statistik (setiap hari jam 2 AM)
- **On-demand Sync:** Saat user request data tertentu
- **Webhook (jika available):** Push notifications dari SIPP

### **4.3 Required API Endpoints**

#### **Dari SIPP System:**
```
GET /api/v1/court-schedules?date=YYYY-MM-DD&court_room=ROOM_CODE
GET /api/v1/cases?case_number=CASE_NUM&status=STATUS
GET /api/v1/cases/statistics?year=YYYY&month=MM
GET /api/v1/master/judges?active=true
GET /api/v1/master/court-rooms
GET /api/v1/master/case-types
```

#### **Parameter Umum:**
- `date_range` (start_date, end_date)
- `court_room` (filter by room)
- `judge_id` (filter by judge)
- `case_type` (perdata, pidana, etc.)
- `case_status` (pending, in_progress, closed)
- `pagination` (page, per_page)

#### **Authentication Methods:**
- API Key (header: `X-API-Key`)
- OAuth2 (client credentials)
- Basic Auth
- IP Whitelisting

## **5. Fitur Utama**

### **5.1 Page Builder System**
- **Drag & Drop Interface:** Susun layout dengan blok komponen
- **WYSIWYG Editor:** Format konten dalam blok teks
- **Template System:** Simpan desain halaman sebagai template
- **Real-time Preview:** Preview perubahan sebelum publish
- **Component Library:** shadcn/ui + custom components untuk pengadilan

### **5.2 Dynamic Menu Management**
- **Hierarchical Menus:** Multi-level dropdown support
- **Location-based Menus:** Header, footer, sidebar, mobile
- **Conditional Display:** Tampilkan menu berdasarkan role/user
- **Visual Editor:** Drag & drop untuk struktur menu

### **5.3 Content Management**
- **News & Announcements:** Dengan kategori dan tagging
- **Document Library:** Upload dan organisasi file publik
- **Court Schedule:** Kalender persidangan dengan filter
- **Public Transparency:** Data APBN dan statistik perkara
- **PPID Portal:** Layanan informasi publik

### **5.4 Admin Panel (Filament)**
- **Dashboard:** Overview website activity
- **Page Management:** CRUD pages dengan page builder
- **Menu Management:** Visual menu editor
- **Content Management:** Berita, dokumen, jadwal
- **User Management:** 5 role levels dengan permissions
- **Settings:** Konfigurasi website, SEO, dll.

## **6. Migrasi Data Joomla**

### **6.1 Data Sources:**
- `joomla_content.json` → Pages & News
- `joomla_categories.json` → Categories
- `joomla_menu.json` → Dynamic Menus
- `joomla_images.json` → Media Library
- `joomla_users.json` → User accounts (optional)

### **6.2 Migration Strategy:**
1. **Analysis Phase:** Map Joomla structure ke schema baru
2. **Data Cleaning:** Convert Joomla HTML ke format clean
3. **Batch Import:** Import dalam chunks untuk performance
4. **Validation:** Verify data integrity post-migration
5. **Fallback Plan:** Rollback capability jika ada issues

## **7. Frontend Architecture**

### **7.1 Component Structure:**
- **Layout Components:** Header, Footer, Sidebar, Navigation
- **Page Components:** Homepage, News, Documents, Schedule
- **UI Components:** shadcn/ui based dengan custom styling
- **Interactive Components:** Magic UI animations dan effects

### **7.2 Routing Strategy:**
- **Static Pages:** `/pages/{slug}` untuk halaman dinamis
- **News:** `/news`, `/news/{slug}`
- **Documents:** `/documents`, `/documents/{category}`
- **Schedule:** `/schedule`, `/schedule/{date}`
- **Transparency:** `/transparency/budget`, `/transparency/statistics`
- **PPID:** `/ppid`, `/ppid/request`

### **7.3 Performance Optimizations:**
- **Lazy Loading:** Images dan components
- **Caching:** Laravel cache untuk frequent queries
- **CDN:** Untuk static assets dan documents
- **Image Optimization:** Automatic resizing dan webp conversion

## **8. Security & Compliance**

### **8.1 Authentication & Authorization:**
- **5 Role Levels:** Super Admin, Admin, Author, Designer, Subscriber
- **Permission Matrix:** Fine-grained access control
- **2FA Support:** Laravel Fortify integration
- **Session Management:** Secure session handling

### **8.2 Data Protection:**
- **Input Validation:** Laravel Form Requests
- **XSS Protection:** Automatic escaping di Blade/React
- **SQL Injection:** Eloquent ORM protection
- **File Upload Security:** Validation dan scanning

### **8.3 Compliance:**
- **PPID Compliance:** Sesuai regulasi informasi publik
- **Data Privacy:** Perlindungan data pribadi
- **Accessibility:** WCAG standards compliance

## **9. Implementation Phases**

### **Phase 1: Foundation (2-3 minggu)**
- Database schema dan migrations
- Basic models dan relationships
- Filament panel setup
- User management dengan 5 roles

### **Phase 2: Core Features (3-4 minggu)**
- Page builder system
- Dynamic menu management
- Joomla data migration
- Basic frontend components

### **Phase 3: Content Features (2-3 minggu)**
- News & announcements
- Document management
- Court schedule system
- Public transparency modules

### **Phase 4: Polish & Deployment (1-2 minggu)**
- Frontend polishing dengan shadcn/ui + Magic UI
- Performance optimization
- Testing dan bug fixing
- Deployment dan migration

## **10. Testing Strategy**

### **10.1 Test Types:**
- **Unit Tests:** Models, services, helpers
- **Feature Tests:** CRUD operations, business logic
- **Browser Tests:** User workflows dengan Pest v4
- **Performance Tests:** Load testing untuk critical paths

### **10.2 Test Coverage Goals:**
- Core business logic: 90%+
- Critical user flows: 100%
- Security features: 100%
- Page builder: 85%+

## **11. Maintenance & Scalability**

### **11.1 Monitoring:**
- **Application Health:** Laravel Horizon/Pulse
- **Error Tracking:** Laravel Telescope
- **Performance Metrics:** Response times, query performance
- **User Analytics:** Page views, popular content

### **11.2 Scalability Considerations:**
- **Database:** Read replicas untuk high traffic
- **Caching:** Redis untuk frequent queries
- **Queue:** Laravel Queues untuk heavy operations
- **CDN:** Untuk static assets global distribution

### **11.3 Documentation:**
- **Admin Guide:** Panduan penggunaan Filament panel
- **Developer Guide:** Setup local, deployment, troubleshooting
- **API Documentation:** Untuk future integrations
- **Maintenance Procedures:** Backup, updates, monitoring

## **12. Product Requirements Document (PRD)**

### **12.1 Executive Summary**
**Project:** Website Pengadilan Agama Penajam - Migrasi dari Joomla 3 ke Laravel 12
**Timeline:** 8-12 minggu (4 phases)
**Budget:** TBD (infrastructure + development)
**Stakeholders:** Pengadilan Agama Penajam, IT Department, Public Users

### **12.2 Problem Statement**
1. **Current System:** Joomla 3 (outdated, kurang flexible)
2. **Pain Points:**
   - Sulit customisasi dan maintenance
   - Developer dependency tinggi
   - Tidak ada page builder modern
   - Menu system static
   - Integrasi terbatas dengan sistem internal (SIPP)
3. **Business Impact:** Efisiensi operasional rendah, user experience kurang optimal

### **12.3 Solution Overview**
**Modern Web Platform dengan:**
- Laravel 12 + Inertia.js v2 + React 19
- Filament v5 Admin Panel
- Page Builder drag-and-drop
- Dynamic Menu Management
- API Integration dengan SIPP
- 5-level Role Management

### **12.4 User Personas & Stories**

#### **Persona 1: Admin Website (Super Admin)**
- **Goals:** Kelola seluruh konten, user management, system configuration
- **Pain Points:** Butuh fleksibilitas tinggi, mudah maintain
- **User Stories:**
  - Sebagai Super Admin, saya ingin membuat halaman baru dengan drag-and-drop builder
  - Sebagai Super Admin, saya ingin mengatur struktur menu secara visual
  - Sebagai Super Admin, saya ingin manage user roles dan permissions

#### **Persona 2: Content Editor (Author)**
- **Goals:** Publikasi berita, pengumuman, dokumen
- **Pain Points:** Proses publishing kompleks
- **User Stories:**
  - Sebagai Author, saya ingin publish berita dengan kategori dan tags
  - Sebagai Author, saya ingin upload dokumen publik dengan metadata
  - Sebagai Author, saya ingin jadwalkan publikasi otomatis

#### **Persona 3: Public User**
- **Goals:** Akses informasi pengadilan, jadwal sidang, dokumen
- **Pain Points:** Informasi sulit ditemukan, tidak update
- **User Stories:**
  - Sebagai User, saya ingin melihat jadwal sidang hari ini
  - Sebagai User, saya ingin download formulir dan peraturan
  - Sebagai User, saya ingin cari informasi perkara tertentu

#### **Persona 4: PPID Officer**
- **Goals:** Kelola permintaan informasi publik
- **Pain Points:** Proses manual, tracking sulit
- **User Stories:**
  - Sebagai PPID Officer, saya ingin track permintaan informasi
  - Sebagai PPID Officer, saya ingin generate laporan periodik
  - Sebagai PPID Officer, saya ingin response cepat ke pemohon

### **12.5 Functional Requirements**

#### **FR-01: Page Builder System**
- **FR-01.01:** Drag-and-drop interface untuk susun layout
- **FR-01.02:** WYSIWYG editor dalam blok teks
- **FR-01.03:** Library komponen (shadcn/ui + custom)
- **FR-01.04:** Template system (save/load halaman)
- **FR-01.05:** Real-time preview sebelum publish
- **FR-01.06:** Versioning dan revision history

#### **FR-02: Dynamic Menu Management**
- **FR-02.01:** Hierarchical menu builder (multi-level)
- **FR-02.02:** Location-based menus (header, footer, sidebar, mobile)
- **FR-02.03:** Conditional display rules
- **FR-02.04:** Visual drag-drop editor
- **FR-02.05:** Menu item types (page, route, custom URL, external)

#### **FR-03: Content Management**
- **FR-03.01:** News & Announcements dengan kategori/tagging
- **FR-03.02:** Document library dengan version control
- **FR-03.03:** Court schedule integration dengan SIPP API
- **FR-03.04:** Public transparency (APBN data, statistics)
- **FR-03.05:** PPID request management system

#### **FR-04: Admin Panel (Filament)**
- **FR-04.01:** Dashboard dengan website analytics
- **FR-04.02:** Page management dengan page builder
- **FR-04.03:** Menu management visual editor
- **FR-04.04:** Content moderation workflow
- **FR-04.05:** User management dengan 5 role levels
- **FR-04.06:** System settings dan configuration

#### **FR-05: API Integration**
- **FR-05.01:** SIPP API integration untuk jadwal sidang
- **FR-05.02:** SIPP API integration untuk data perkara
- **FR-05.03:** SIPP API integration untuk statistics
- **FR-05.04:** Master data sync (judges, rooms, case types)
- **FR-05.05:** Automatic synchronization schedules

#### **FR-06: User Management & Security**
- **FR-06.01:** 5 Role levels (Super Admin, Admin, Author, Designer, Subscriber)
- **FR-06.02:** Permission matrix fine-grained
- **FR-06.03:** Two-factor authentication
- **FR-06.04:** Activity logging dan audit trail
- **FR-06.05:** Session management secure

### **12.6 Non-Functional Requirements**

#### **NFR-01: Performance**
- **NFR-01.01:** Page load time < 2 seconds
- **NFR-01.02:** API response time < 500ms
- **NFR-01.03:** Support 1000+ concurrent users
- **NFR-01.04:** Database query optimization

#### **NFR-02: Security**
- **NFR-02.01:** OWASP Top 10 compliance
- **NFR-02.02:** Data encryption at rest dan transit
- **NFR-02.03:** Regular security audits
- **NFR-02.04:** Backup dan disaster recovery

#### **NFR-03: Usability**
- **NFR-03.01:** WCAG 2.1 AA compliance
- **NFR-03.02:** Responsive design (mobile, tablet, desktop)
- **NFR-03.03:** Intuitive admin interface
- **NFR-03.04:** Comprehensive documentation

#### **NFR-04: Reliability**
- **NFR-04.01:** 99.9% uptime SLA
- **NFR-04.02:** Automated monitoring dan alerting
- **NFR-04.03:** Graceful degradation
- **NFR-04.04:** Data integrity guarantees

#### **NFR-05: Maintainability**
- **NFR-05.01:** Code documentation dan comments
- **NFR-05.02:** Comprehensive test coverage (>85%)
- **NFR-05.03:** Modular architecture
- **NFR-05.04:** Easy deployment process

### **12.7 Technical Specifications**

#### **Backend Stack:**
- PHP 8.5+, Laravel 12, Filament v5
- Database: MySQL/PostgreSQL (production), SQLite (development)
- Cache: Redis
- Queue: Laravel Queues
- Search: Laravel Scout (optional)

#### **Frontend Stack:**
- Inertia.js v2, React 19, TypeScript
- UI: shadcn/ui components
- UX: Magic UI Design animations
- Styling: Tailwind CSS v4
- Build: Vite

#### **DevOps & Infrastructure:**
- Version Control: Git
- CI/CD: GitHub Actions / GitLab CI
- Hosting: VPS / Cloud (AWS/DigitalOcean)
- Monitoring: Laravel Pulse/Telescope
- Backup: Automated daily

### **12.8 Success Metrics**

#### **Business Metrics:**
- **BM-01:** Website traffic increase 30% dalam 6 bulan
- **BM-02:** User satisfaction score > 4.5/5
- **BM-03:** Admin productivity improvement 40%
- **BM-04:** PPID request processing time reduced 50%

#### **Technical Metrics:**
- **TM-01:** Page load time < 2s (target)
- **TM-02:** API availability 99.9%
- **TM-03:** Zero critical security incidents
- **TM-04:** Test coverage > 85%

#### **User Engagement Metrics:**
- **UE-01:** Average session duration > 3 minutes
- **UE-02:** Bounce rate < 40%
- **UE-03:** Returning visitors > 30%
- **UE-04:** Document downloads per month

### **12.9 Constraints & Dependencies**

#### **Constraints:**
- **C-01:** Migrasi data dari Joomla 3 harus complete dan accurate
- **C-02:** Integrasi dengan SIPP API (existing system)
- **C-03:** Compliance dengan regulasi PPID
- **C-04:** Budget dan timeline limitations

#### **Dependencies:**
- **D-01:** SIPP API availability dan documentation
- **D-02:** Joomla data export completeness
- **D-03:** Infrastructure provisioning
- **D-04:** Stakeholder availability untuk review

### **12.10 Risks & Mitigation**

#### **Technical Risks:**
- **Risk-01:** SIPP API integration complexity
  - **Mitigation:** Phase approach, mock API selama development
- **Risk-02:** Joomla data migration issues
  - **Mitigation:** Comprehensive testing, rollback plan
- **Risk-03:** Performance bottlenecks
  - **Mitigation:** Load testing early, optimization iterations

#### **Project Risks:**
- **Risk-04:** Timeline delays
  - **Mitigation:** Agile methodology, weekly progress review
- **Risk-05:** Scope creep
  - **Mitigation:** Clear requirements, change control process
- **Risk-06:** Resource constraints
  - **Mitigation:** Priority-based implementation, MVP first

### **12.11 Timeline & Milestones**

#### **Phase 1: Foundation (Weeks 1-3)**
- Database schema dan migrations
- Basic models dan Eloquent relationships
- Filament panel setup
- User management dengan 5 roles
- Authentication system

#### **Phase 2: Core Features (Weeks 4-7)**
- Page builder system
- Dynamic menu management
- Joomla data migration
- Basic frontend components
- Admin panel refinement

#### **Phase 3: Content Features (Weeks 8-10)**
- News & announcements system
- Document management
- Court schedule integration
- Public transparency modules
- PPID portal

#### **Phase 4: Polish & Deployment (Weeks 11-12)**
- Frontend polishing (shadcn/ui + Magic UI)
- Performance optimization
- Comprehensive testing
- Deployment dan migration
- User training dan documentation

### **12.12 Approval & Sign-off**

**Approved by:**
- [ ] Project Sponsor: ____________________ Date: ________
- [ ] Technical Lead: ____________________ Date: ________
- [ ] Product Owner: ____________________ Date: ________

**Next Steps:**
1. Finalize design dan architecture
2. Begin Phase 1 implementation
3. Weekly progress reporting
4. Stakeholder review setiap phase completion