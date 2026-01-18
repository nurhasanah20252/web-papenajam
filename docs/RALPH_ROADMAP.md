# Ralph Loop Roadmap - Immediate Priorities

## Current Phase: Phase 1 - Foundation (Weeks 1-3)

### Immediate Tasks (Week 1)

**CRITICAL PATH - START HERE:**

1. **PH1.1.1: Design Core Database Schema** ⭐
   - **Priority:** Critical
   - **Dependencies:** None
   - **Description:** Create ERD for all core tables from PRD section 3
   - **Files to create:** `docs/ERD.md`, `database/schema.sql`
   - **Acceptance:** Complete ERD with all tables and relationships

2. **PH1.1.2: Create Laravel Migrations** ⭐
   - **Priority:** Critical
   - **Dependencies:** PH1.1.1
   - **Description:** Convert schema to Laravel migration files
   - **Files to create:** All migration files in `database/migrations/`
   - **Acceptance:** Migrations run without errors

3. **PH1.1.3: Create Eloquent Models** ⭐
   - **Priority:** Critical
   - **Dependencies:** PH1.1.2
   - **Description:** Create models with relationships and casts
   - **Files to create:** All model files in `app/Models/`
   - **Acceptance:** Models instantiate with proper relationships

### Week 2 Priorities

4. **PH1.2.1: Extend User Model with Roles**
   - **Priority:** Critical
   - **Dependencies:** PH1.1.2
   - **Description:** Add role system to User model
   - **Files:** `app/Models/User.php`, migration for new columns

5. **PH1.2.2: Implement Laravel Fortify Authentication**
   - **Priority:** Critical
   - **Dependencies:** PH1.2.1
   - **Description:** Set up authentication with 5 roles
   - **Files:** Configure `config/fortify.php`, create auth views

6. **PH1.2.3: Create Authorization Policies**
   - **Priority:** High
   - **Dependencies:** PH1.2.2
   - **Description:** Implement permission system
   - **Files:** Policy files in `app/Policies/`

### Week 3 Priorities

7. **PH1.3.1: Install and Configure Filament v5**
   - **Priority:** High
   - **Dependencies:** PH1.1.3, PH1.2.2
   - **Description:** Set up admin panel
   - **Files:** Filament configuration, theme setup

8. **PH1.3.2: Create User Management Resource**
   - **Priority:** High
   - **Dependencies:** PH1.3.1
   - **Description:** Build Filament resource for users
   - **Files:** `app/Filament/Resources/UserResource.php`

9. **PH1.4.1: Configure Inertia.js v2 + React 19**
   - **Priority:** Medium
   - **Dependencies:** None (can run parallel)
   - **Description:** Set up frontend architecture
   - **Files:** Update `vite.config.ts`, TypeScript config

## Quick Start for Ralph

### Step 1: Analyze Existing Codebase
- Check current Laravel installation
- Review existing models and migrations
- Understand current project structure

### Step 2: Begin with Database Design
1. Read PRD section 3 (Database Schema Inti)
2. Create ERD diagram
3. Design migration strategy

### Step 3: Implement Phase 1 Sequentially
Follow the dependency chain:
```
PH1.1.1 → PH1.1.2 → PH1.1.3 → PH1.2.1 → PH1.2.2 → PH1.2.3 → PH1.3.1 → ...
```

## Success Checklist for Phase 1

### Database Layer ✓
- [ ] All core tables designed (pages, menus, news, documents, etc.)
- [ ] Migrations created and tested
- [ ] Models with proper relationships
- [ ] Factories for testing

### Authentication Layer ✓
- [ ] User model extended with roles
- [ ] Fortify configured with 5 roles
- [ ] Authentication views created
- [ ] Role-based middleware working

### Admin Panel ✓
- [ ] Filament v5 installed
- [ ] Admin accessible at `/admin`
- [ ] User management resource working
- [ ] Basic content resources created

### Frontend Foundation ✓
- [ ] Inertia.js v2 + React 19 configured
- [ ] Tailwind CSS v4 working
- [ ] Basic layout components
- [ ] shadcn/ui installed

## Key Files to Examine First

1. **Current project state:**
   - `composer.json` - Laravel version and packages
   - `package.json` - Frontend dependencies
   - `database/migrations/` - Existing migrations
   - `app/Models/` - Existing models

2. **Configuration files:**
   - `config/fortify.php` - Authentication setup
   - `config/filament.php` - Admin panel config
   - `vite.config.ts` - Frontend build config

3. **Documentation:**
   - `docs/PRD.md` - Full requirements
   - `docs/API_INTEGRATION_DESIGN.md` - SIPP API design
   - `docs/SIPP_WEB_TABLES_ANALYSIS.md` - SIPP database analysis

## Technical Constraints to Remember

- **Laravel 12** with new streamlined structure
- **Filament v5** for admin panel
- **Inertia.js v2** with React 19
- **Wayfinder** for type-safe routing
- **Tailwind CSS v4** (CSS-first configuration)
- **PHP 8.5+** with type declarations
- **Follow Laravel Boost guidelines** in CLAUDE.md

## Testing Strategy from Day 1

1. Write tests for each model
2. Test authentication flows
3. Test Filament resources
4. Use Pest v4 for all tests
5. Run `php artisan test --compact` frequently

## Common Pitfalls to Avoid

1. **Don't skip database design** - Get schema right first
2. **Follow dependencies strictly** - PH1.1 before PH1.2, etc.
3. **Write tests early** - Don't leave testing until end
4. **Follow project conventions** - Check existing code patterns
5. **Use Artisan commands** - `php artisan make:model`, etc.

## Ralph Loop Specific Instructions

- **Loop on each task** until acceptance criteria met
- **Check existing code** before creating new files
- **Run tests** after each significant change
- **Use Laravel Boost tools** for documentation and debugging
- **Follow the task IDs** (PH1.1.1, PH1.1.2, etc.) for tracking

## Next Immediate Action

**Start with:** `PH1.1.1 - Design Core Database Schema`

1. Read PRD section 3 (lines 31-95)
2. Create ERD diagram in `docs/ERD.md`
3. Design table structures with columns
4. Define relationships between tables
5. Review with existing database structure

---

*Last Updated: 2026-01-18*
*Total Tasks: 132 subtasks across 4 phases*
*Current Focus: Phase 1 - Foundation*