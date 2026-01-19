# Phase 1 Testing Strategy: Foundation Layer

**Project:** PA Penajam Website - Laravel 12 + Pest v4
**Created:** 2026-01-18
**Testing Architect:** System Generated

## Executive Summary

This document outlines the comprehensive testing strategy for Phase 1: Foundation layer of the PA Penajam website. The strategy focuses on ensuring database integrity, authentication security, authorization correctness, and frontend reliability using Pest v4 testing framework.

## Testing Philosophy

### Core Principles
1. **Test Pyramid**: 70% unit tests, 20% feature tests, 10% browser/E2E tests
2. **Risk-Based Testing**: Prioritize tests based on business impact and failure probability
3. **Deterministic Tests**: Every test must be repeatable and independent
4. **Fast Feedback**: Unit tests should run in milliseconds, feature tests in seconds
5. **Living Documentation**: Tests serve as executable documentation of system behavior

### Coverage Goals
- **Unit Tests**: 90%+ coverage for models, services, helpers
- **Feature Tests**: 100% coverage for authentication, 90% for authorization
- **Browser Tests**: Critical user journeys only (login, registration, password reset)
- **Edge Cases**: All validation rules, null values, boundary conditions

## Testing Architecture

### Test Structure
```
tests/
├── Unit/
│   ├── Models/
│   │   ├── UserTest.php              # User model logic
│   │   ├── PageTest.php              # Page model relationships
│   │   ├── MenuTest.php              # Menu hierarchy
│   │   └── ...
│   ├── Services/
│   │   └── (Future: SippApiClientTest, etc.)
│   └── Helpers/
│       └── (Future: Helper functions)
│
├── Feature/
│   ├── Auth/
│   │   ├── AuthenticationTest.php    # Login flows
│   │   ├── RegistrationTest.php      # User registration
│   │   ├── PasswordResetTest.php     # Password reset
│   │   ├── TwoFactorChallengeTest.php # 2FA flows
│   │   └── RolesAndPermissionsTest.php # Role checks
│   ├── Policies/
│   │   ├── PagePolicyTest.php        # Page authorization
│   │   ├── UserPolicyTest.php        # User management auth
│   │   └── ...
│   ├── Models/
│   │   └── ModelTest.php             # Model instantiation
│   └── (Other feature tests)
│
└── Browser/
    ├── AuthenticationBrowserTest.php # E2E auth flows
    ├── HomePageTest.php              # Homepage smoke test
    └── RegistrationBrowserTest.php   # Registration flow
```

## Phase 1 Test Coverage Matrix

### PH1.1: Database Schema & Migrations

#### Unit Tests - Model Layer
**Priority**: Critical

| Test Category | Test Cases | Status | File |
|--------------|-----------|---------|------|
| **User Model** | User instantiation with all roles | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | Role assignment and checking | ✅ Existing | `tests/Feature/Auth/RolesAndPermissionsTest.php` |
| | Permission checking (custom and role-based) | ✅ Existing | `tests/Feature/Auth/RolesAndPermissionsTest.php` |
| | Profile completion tracking | ✅ Existing | `tests/Feature/Auth/RolesAndPermissionsTest.php` |
| | Last login timestamp updates | ✅ Existing | `tests/Feature/Auth/RolesAndPermissionsTest.php` |
| **Page Model** | Page creation with all types | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | Page creation with all statuses | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | Page-blocks relationship | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | Page metadata handling | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| **Menu System** | Menu creation with locations | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | Hierarchical menu items | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | Menu item route types | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| **News System** | News creation with tags | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | News with featured image | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| **Document System** | Document with file info | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| **Category System** | Category with children | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| **PPID System** | PPID request with all statuses | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | PPID request priority handling | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | PPID request with attachments | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | PPID request number generation | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| **Court Schedule** | Court schedule from SIPP | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | Court schedule with parties | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | Court schedule formatted dates | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| **Budget & Statistics** | Budget transparency with amount | ✅ Existing | `tests/Feature/Models/ModelTest.php` |
| | Case statistics calculations | ✅ Existing | `tests/Feature/Models/ModelTest.php` |

#### Feature Tests - Database Layer
**Priority**: High

| Test Category | Test Cases | Status | File |
|--------------|-----------|---------|------|
| **Migrations** | All migrations run successfully | 🔄 Pending | `tests/Feature/Database/MigrationsTest.php` |
| **Relationships** | Eager loading prevents N+1 | 🔄 Pending | `tests/Feature/Database/RelationshipTest.php` |
| **Factories** | All factories generate valid data | ✅ Existing | `tests/Feature/Models/ModelTest.php` |

### PH1.2: User Management & Authentication

#### Feature Tests - Authentication Layer
**Priority**: Critical

| Test Category | Test Cases | Status | File |
|--------------|-----------|---------|------|
| **Login Flow** | Login screen renders | ✅ Existing | `tests/Feature/Auth/AuthenticationTest.php` |
| | User can authenticate | ✅ Existing | `tests/Feature/Auth/AuthenticationTest.php` |
| | User cannot authenticate with wrong password | ✅ Existing | `tests/Feature/Auth/AuthenticationTest.php` |
| | User with 2FA redirected to challenge | ✅ Existing | `tests/Feature/Auth/AuthenticationTest.php` |
| | Rate limiting works | ✅ Existing | `tests/Feature/Auth/AuthenticationTest.php` |
| **Registration** | User can register | ✅ Existing | `tests/Feature/Auth/RegistrationTest.php` |
| | Registration assigns default role | ✅ Existing | `tests/Feature/Auth/RegistrationTest.php` |
| **Password Reset** | Password reset request works | ✅ Existing | `tests/Feature/Auth/PasswordResetTest.php` |
| | Password can be reset with token | ✅ Existing | `tests/Feature/Auth/PasswordResetTest.php` |
| **Email Verification** | Verification email sent | ✅ Existing | `tests/Feature/Auth/EmailVerificationTest.php` |
| | Email can be verified | ✅ Existing | `tests/Feature/Auth/EmailVerificationTest.php` |
| **2FA** | 2FA can be enabled | ✅ Existing | `tests/Feature/Settings/TwoFactorAuthenticationTest.php` |
| | 2FA can be confirmed | ✅ Existing | `tests/Feature/Settings/TwoFactorAuthenticationTest.php` |
| | 2FA challenge works | ✅ Existing | `tests/Feature/Auth/TwoFactorChallengeTest.php` |
| | Recovery codes work | ✅ Existing | `tests/Feature/Auth/TwoFactorChallengeTest.php` |
| **Profile Management** | User can update profile | ✅ Existing | `tests/Feature/Settings/ProfileUpdateTest.php` |
| | User can change password | ✅ Existing | `tests/Feature/Settings/PasswordUpdateTest.php` |
| | Password confirmation required | ✅ Existing | `tests/Feature/Auth/PasswordConfirmationTest.php` |

#### Browser Tests - Authentication
**Priority**: High

| Test Category | Test Cases | Status | File |
|--------------|-----------|---------|------|
| **Critical Flows** | Complete registration flow in browser | 🔄 Pending | `tests/Browser/AuthenticationBrowserTest.php` |
| | Complete login flow with 2FA | 🔄 Pending | `tests/Browser/AuthenticationBrowserTest.php` |
| | Complete password reset flow | 🔄 Pending | `tests/Browser/AuthenticationBrowserTest.php` |
| | Responsive design on mobile | 🔄 Pending | `tests/Browser/ResponsiveDesignTest.php` |

### PH1.2.3: Authorization Policies

#### Feature Tests - Authorization Layer
**Priority**: Critical

| Policy | Test Cases | Status | File |
|--------|-----------|---------|------|
| **UserPolicy** | Super admin can manage users | ✅ Existing | `tests/Feature/Policies/UserPolicyTest.php` |
| | Admin can view users | ✅ Existing | `tests/Feature/Policies/UserPolicyTest.php` |
| | Author cannot manage users | ✅ Existing | `tests/Feature/Policies/UserPolicyTest.php` |
| **PagePolicy** | View permissions by role | 🔄 Pending | `tests/Feature/Policies/PagePolicyTest.php` |
| | Create permissions by role | 🔄 Pending | `tests/Feature/Policies/PagePolicyTest.php` |
| | Update permissions by role | 🔄 Pending | `tests/Feature/Policies/PagePolicyTest.php` |
| | Delete permissions by role | 🔄 Pending | `tests/Feature/Policies/PagePolicyTest.php` |
| **NewsPolicy** | View permissions by role | 🔄 Pending | `tests/Feature/Policies/NewsPolicyTest.php` |
| | Create permissions by role | 🔄 Pending | `tests/Feature/Policies/NewsPolicyTest.php` |
| **DocumentPolicy** | View permissions by role | 🔄 Pending | `tests/Feature/Policies/DocumentPolicyTest.php` |
| | Download permissions by role | 🔄 Pending | `tests/Feature/Policies/DocumentPolicyTest.php` |
| **Other Policies** | Menu, Category, PPID, etc. | 🔄 Pending | `tests/Feature/Policies/*PolicyTest.php` |

#### Middleware Tests
**Priority**: High

| Middleware | Test Cases | Status | File |
|------------|-----------|---------|------|
| **CheckRole** | Super admin can access admin routes | ✅ Existing | `tests/Feature/Auth/RolesAndPermissionsTest.php` |
| | Subscriber cannot access admin routes | ✅ Existing | `tests/Feature/Auth/RolesAndPermissionsTest.php` |
| **CheckPermission** | Permission checks work | ✅ Existing | `tests/Feature/Auth/RolesAndPermissionsTest.php` |

### PH1.3: Filament Admin Panel

#### Feature Tests - Admin Panel
**Priority**: Medium (To be added in Phase 2)

| Test Category | Test Cases | Status | File |
|--------------|-----------|---------|------|
| **Admin Access** | Admin can access Filament panel | 🔄 Pending | `tests/Feature/Filament/AdminAccessTest.php` |
| | Non-admin cannot access Filament | 🔄 Pending | `tests/Feature/Filament/AdminAccessTest.php` |
| **Resources** | CRUD operations for each resource | 🔄 Pending | `tests/Feature/Filament/Resources/*ResourceTest.php` |

### PH1.4: Frontend Setup

#### Browser Tests - Frontend
**Priority**: Medium

| Test Category | Test Cases | Status | File |
|--------------|-----------|---------|------|
| **Smoke Tests** | All public pages load | ✅ Existing | `tests/Browser/HomePageTest.php` |
| | Navigation menu displays | ✅ Existing | `tests/Browser/HomePageTest.php` |
| | Featured content displays | ✅ Existing | `tests/Browser/HomePageTest.php` |
| **Responsive Design** | Mobile viewport (< 768px) | 🔄 Pending | `tests/Browser/ResponsiveDesignTest.php` |
| | Tablet viewport (768px - 1024px) | 🔄 Pending | `tests/Browser/ResponsiveDesignTest.php` |
| | Desktop viewport (> 1024px) | 🔄 Pending | `tests/Browser/ResponsiveDesignTest.php` |
| **Dark Mode** | Dark mode toggle works | 🔄 Pending | `tests/Browser/ThemeTest.php` |
| | Dark mode persists | 🔄 Pending | `tests/Browser/ThemeTest.php` |

## Risk Assessment & Test Priority

### High-Risk Areas (Critical Tests)
1. **Authentication & Authorization**: Security-critical, must have 100% coverage
2. **User Roles**: Access control depends on correct role handling
3. **Password Reset**: Security-sensitive feature
4. **2FA**: Security-sensitive feature
5. **Policy Checks**: Authorization depends on correct policy implementation

### Medium-Risk Areas (High Priority)
1. **Database Relationships**: Data integrity depends on correct relationships
2. **Model Factories**: Test data quality depends on factory correctness
3. **Form Validation**: Data integrity depends on validation rules
4. **Frontend Rendering**: User experience depends on correct rendering

### Low-Risk Areas (Standard Priority)
1. **UI Components**: Visual issues don't affect functionality
2. **Static Pages**: Low complexity, low risk
3. **Helper Functions**: Typically simple and isolated

## Test Implementation Guidelines

### Unit Test Guidelines
- **Focus**: Test individual methods in isolation
- **Speed**: Should run in < 100ms each
- **Isolation**: No database, filesystem, or network calls
- **Mocking**: Mock all external dependencies
- **Assertions**: Use Pest's expect() syntax for readability

**Example:**
```php
it('can check if user has role', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    expect($user->hasRole(UserRole::Admin))->toBeTrue();
    expect($user->hasRole(UserRole::SuperAdmin))->toBeFalse();
});
```

### Feature Test Guidelines
- **Focus**: Test HTTP requests and responses
- **Speed**: Should run in < 1 second each
- **Database**: Use RefreshDatabase trait
- **Authentication**: Use actingAs() for authenticated requests
- **Assertions**: Use Pest's HTTP test assertions

**Example:**
```php
it('user can login with correct credentials', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});
```

### Browser Test Guidelines
- **Focus**: Test critical user journeys end-to-end
- **Speed**: Can run in 2-5 seconds each (use sparingly)
- **Browser**: Real browser (Chrome) via Playwright
- **Interactions**: Click, type, scroll, submit
- **Assertions**: Use Pest browser assertions

**Example:**
```php
it('user can complete registration flow', function () {
    visit('/register')
        ->fill('name', 'John Doe')
        ->fill('email', 'john@example.com')
        ->fill('password', 'password123')
        ->fill('password_confirmation', 'password123')
        ->click('Register')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
});
```

## Test Data Management

### Factory Strategy
- **Use Factories**: Always use factories for test data
- **Factory States**: Create reusable states for common scenarios
- **Relationships**: Use factory relationships for related models
- **Unique Data**: Use sequences for unique constraints

### Database Cleanup
- **RefreshDatabase**: Use for most feature tests
- **DatabaseMigrations**: Use for migration tests
- **DatabaseTransactions**: Use for performance-critical tests

## Running Tests

### Run All Tests
```bash
php artisan test --compact
```

### Run Specific Test File
```bash
php artisan test --compact tests/Feature/Auth/AuthenticationTest.php
```

### Run Filtered Tests
```bash
php artisan test --compact --filter=login
```

### Run Browser Tests
```bash
php artisan test --compact --testsuite=Browser
```

### Parallel Testing
```bash
php artisan test --parallel
```

## Coverage Goals & Metrics

### Target Coverage
- **Overall Coverage**: 85%+
- **Models**: 95%+
- **Policies**: 100% (security-critical)
- **Controllers**: 80%+
- **Services**: 90%+

### Measuring Coverage
```bash
# Generate coverage report
php artisan test --coverage --min=85

# HTML coverage report
php artisan test --coverage-html coverage
```

## Continuous Integration

### CI Pipeline Requirements
1. **All tests must pass** before merge
2. **Coverage minimum 85%** for new code
3. **Browser tests run** on every PR
4. **Tests run in parallel** for speed
5. **Coverage badge** in README

### GitHub Actions Example
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.5
          coverage: xdebug
      - name: Install Dependencies
        run: composer install --no-interaction
      - name: Run Tests
        run: php artisan test --coverage --min=85
```

## Test Maintenance

### Keeping Tests Healthy
1. **Run tests locally** before committing
2. **Fix flaky tests** immediately
3. **Update tests** when changing code
4. **Remove obsolete tests** when features are removed
5. **Refactor duplicated test code** into helpers

### Test Smells to Avoid
1. **Flaky tests**: Tests that sometimes fail
2. **Slow tests**: Tests taking > 5 seconds
3. **Brittle tests**: Tests breaking on minor changes
4. **Testing implementation**: Testing how instead of what
5. **Hardcoded values**: Tests depending on specific data

## Next Steps

### Immediate Actions (Phase 1)
1. ✅ **Review existing tests**: Audit current test coverage
2. 🔄 **Create missing unit tests**: Fill gaps in model tests
3. 🔄 **Create missing feature tests**: Fill gaps in auth tests
4. 🔄 **Create browser tests**: Add E2E tests for critical flows
5. 🔄 **Set up CI**: Configure GitHub Actions for automated testing

### Future Enhancements (Phase 2+)
1. **Performance tests**: Load testing for API endpoints
2. **Contract tests**: API contract validation
3. **Integration tests**: Test external service integrations
4. **Visual regression tests**: UI consistency testing
5. **Chaos engineering**: Test resilience under failure

## Conclusion

This testing strategy ensures that Phase 1: Foundation layer has comprehensive test coverage focusing on:
- **Database integrity** through model and relationship tests
- **Authentication security** through comprehensive auth flow tests
- **Authorization correctness** through policy and middleware tests
- **Frontend reliability** through browser tests for critical journeys

The strategy follows testing best practices with a balanced test pyramid, risk-based prioritization, and deterministic, fast-running tests that serve as living documentation.

**Target Coverage**: 90%+ for core logic
**Test Execution Time**: < 2 minutes for full suite
**CI/CD Integration**: Automated testing on every commit
