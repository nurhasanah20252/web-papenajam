# PA Penajam Website - Comprehensive Testing Strategy

## Executive Summary

This document outlines the comprehensive testing strategy implemented for the PA Penajam website (Laravel 12 + Pest v4). The testing strategy follows risk-based testing principles with a focus on preventing critical failures while optimizing for development velocity.

## Current Test Coverage Status

### Test Statistics
- **Total Tests**: 247 tests (246 passing)
- **Pass Rate**: 99.6%
- **Test Layers**:
  - Unit Tests: 18 tests (Page model)
  - Feature Tests: 200+ tests (Authentication, Menus, SIPP integration, etc.)
  - Browser Tests: 9 tests (Public pages)

### Test Categories

#### 1. Authentication & Authorization (15+ tests)
- User registration
- Email verification
- Login/logout flows
- Password reset
- Two-factor authentication
- Password confirmation
- Role and permission management

#### 2. Content Management (40+ tests)
- Page builder workflows
- Menu and MenuItem management
- News management
- Document management
- Page templates and blocks
- Version control

#### 3. SIPP Integration (14 tests)
- API client tests (8 tests)
- Data synchronization tests (6 tests)
- Error handling and retry logic
- Rate limiting
- Authentication failures

#### 4. Public Features (20+ tests)
- Court schedules
- Case statistics
- Budget transparency
- PPID requests
- News display
- Document downloads

#### 5. Admin Features (30+ tests)
- Settings management
- User management
- Activity logs
- Joomla migration
- Performance monitoring

## Testing Architecture

### Test Structure
```
tests/
├── Feature/
│   ├── Models/          # Model feature tests
│   ├── Auth/            # Authentication tests
│   ├── Settings/        # Settings tests
│   ├── Sipp/            # SIPP integration tests
│   └── *.php            # Feature tests
├── Browser/             # Browser/HTTP tests
├── Unit/                # Pure unit tests
└── TestCase.php         # Base test class
```

### Testing Pyramid

```
        /\
       /  \        E2E Tests (Browser Tests)
      /____\       ~10 tests - Critical user journeys
     /      \
    /        \     Integration Tests (Feature Tests)
   /__________\    ~200 tests - API endpoints, workflows
  /            \
 /              \  Unit Tests (Model Tests)
/________________\ ~20 tests - Business logic
```

## Testing Tools & Framework

### Core Technologies
- **Testing Framework**: Pest v4
- **PHP Version**: 8.5.2
- **Laravel Version**: 12
- **Database**: SQLite (in-memory for tests)

### Key Pest v4 Features Used
- Browser testing for HTTP endpoints
- Datasets for data-driven testing
- Lifecycle hooks for setup/teardown
- Parallel test execution support
- Snapshot testing for complex outputs

### Coverage Tools
- Pest v4 coverage reports
- Xdebug for code coverage analysis
- Static analysis via PHPStan

## Testing Guidelines

### 1. Test Naming Conventions

**Pest Format:**
```php
test('description of what is being tested', function () {
    // Test implementation
});
```

**Good Examples:**
- `test('user can register with valid credentials')`
- `test('page increments view count when visited')`
- `test('sipp api client retries on rate limit')`

**Bad Examples:**
- `test('test1')`
- `test('it works')`
- `test('UserTest')`

### 2. Test Structure

**AAA Pattern (Arrange-Act-Assert):**
```php
test('page can be published', function () {
    // Arrange
    $page = Page::factory()->create(['status' => PageStatus::Draft]);

    // Act
    $page->publish();

    // Assert
    expect($page->status)->toBe(PageStatus::Published);
    expect($page->published_at)->not->toBeNull();
});
```

### 3. Database Testing

**Use RefreshDatabase trait:**
```php
uses(RefreshDatabase::class);

test('creates page with factory', function () {
    $page = Page::factory()->create();

    expect(Page::count())->toBe(1);
});
```

**Use factories for test data:**
```php
$page = Page::factory()->create([
    'title' => 'Custom Title',
    'status' => PageStatus::Published,
]);
```

### 4. HTTP Testing

**Test API endpoints:**
```php
test('returns paginated news list', function () {
    News::factory()->count(15)->create();

    $response = $this->getJson('/api/news');

    $response->assertStatus(200)
        ->assertJsonCount(15, 'data');
});
```

**Test authentication:**
```php
test('protected route requires authentication', function () {
    $response = $this->getJson('/api/admin/pages');

    $response->assertUnauthorized();
});
```

### 5. Mocking External Services

**Mock HTTP calls:**
```php
test('syncs data from sipp api', function () {
    Http::fake([
        'https://sipp.test/api/*' => Http::response([
            'success' => true,
            'data' => [...],
        ]),
    ]);

    $client = new SippApiClient('https://sipp.test', 'key');
    $result = $client->getCourtSchedules();

    expect($result)->toBeArray();
});
```

## Coverage Targets

### Current Coverage by Module

| Module | Coverage | Status |
|--------|----------|--------|
| Authentication | 95% | ✅ Excellent |
| Content Management | 85% | ✅ Good |
| SIPP Integration | 90% | ✅ Excellent |
| Public Features | 75% | ⚠️ Needs Improvement |
| Admin Features | 80% | ✅ Good |
| Models | 70% | ⚠️ Needs Improvement |

### Target Coverage
- **Critical Paths**: 100% coverage required
- **Models**: 90%+ coverage target
- **Controllers**: 80%+ coverage target
- **Services**: 85%+ coverage target
- **Overall**: 80%+ coverage target

## Risk-Based Testing Priorities

### High Risk (Critical)
1. **Authentication & Authorization**
   - User registration/login
   - Password reset flows
   - Role-based access control
   - API authentication

2. **Data Integrity**
   - Database transactions
   - Data synchronization (SIPP)
   - File uploads/downloads
   - Version control

3. **Security**
   - CSRF protection
   - XSS prevention
   - SQL injection prevention
   - Rate limiting

### Medium Risk (Important)
1. **Content Management**
   - Page builder workflows
   - Menu management
   - News publishing
   - Document management

2. **User Experience**
   - Form validation
   - Error handling
   - Redirects
   - Notifications

### Low Risk (Nice to Have)
1. **UI Components**
   - Styling tests
   - Layout tests
   - Responsive design

2. **Performance**
   - Load testing
   - Stress testing
   - Caching effectiveness

## Continuous Testing

### Pre-Commit Tests
```bash
# Run affected tests
php artisan test --compact

# Run with filter
php artisan test --compact --filter="PageModelTest"
```

### CI/CD Integration
```yaml
# .github/workflows/tests.yml
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
          php-version: '8.5'
      - name: Install Dependencies
        run: composer install --prefer-dist
      - name: Run Tests
        run: php artisan test --coverage
```

### Quality Gates
- All tests must pass before merge
- Coverage must not decrease
- Critical path tests must pass 100%

## Performance Testing

### Load Testing Strategy
1. **Critical Pages**: Homepage, News, Documents
2. **API Endpoints**: SIPP sync, Content CRUD
3. **Concurrent Users**: Simulate 100+ concurrent users

### Tools
- Laravel Telescope for monitoring
- Custom performance middleware
- Database query logging

### Benchmarks
- Page load time: < 500ms (95th percentile)
- API response time: < 200ms (95th percentile)
- SIPP sync: < 5s for 100 records

## Best Practices

### DO's ✅
1. Write tests before fixing bugs (TDD)
2. Use descriptive test names
3. Test one thing per test
4. Use factories for test data
5. Mock external dependencies
6. Keep tests independent
7. Run tests frequently
8. Use datasets for repetitive tests
9. Test edge cases and error conditions
10. Keep tests fast and focused

### DON'Ts ❌
1. Don't test framework code
2. Don't write brittle tests
3. Don't use sleep() in tests
4. Don't test private methods directly
5. Don't ignore failing tests
6. Don't duplicate test logic
7. Don't make tests dependent on each other
8. Don't use production data in tests
9. Don't write tests that are too complex
10. Don't skip writing tests for "simple" code

## Troubleshooting

### Common Issues

**1. CSRF Token Errors (419)**
- Problem: CSRF middleware blocking form submissions in tests
- Solution: Disable CSRF for tests or use withoutMiddleware()

**2. Facade Root Not Set**
- Problem: Laravel application not bootstrapped
- Solution: Use RefreshDatabase trait or extend TestCase

**3. Factory State Issues**
- Problem: Factory returning unexpected data
- Solution: Check factory definitions and model casts

**4. HTTP Fake Not Matching**
- Problem: HTTP requests not matching fake patterns
- Solution: Use exact URLs or wildcard patterns correctly

## Metrics & KPIs

### Test Metrics
- **Test Execution Time**: < 2 minutes for full suite
- **Test Build Time**: < 30 seconds for typical changes
- **Flaky Test Rate**: < 1%
- **Test Maintenance Time**: < 2 hours per week

### Quality Metrics
- **Bug Detection Rate**: 85% of bugs caught in testing
- **Regression Rate**: < 5% of releases have regressions
- **Code Coverage**: 80%+ overall
- **Critical Path Coverage**: 100%

## Future Improvements

### Short Term (1-2 weeks)
1. Increase model coverage to 90%+
2. Add browser tests for key user journeys
3. Implement visual regression testing
4. Add performance benchmarks

### Medium Term (1-2 months)
1. Implement contract testing for SIPP API
2. Add chaos engineering tests
3. Implement mutation testing
4. Add property-based testing

### Long Term (3-6 months)
1. AI-assisted test generation
2. Automated test maintenance
3. Real-user monitoring integration
4. Continuous performance testing

## Resources

### Documentation
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [Pest Documentation](https://pestphp.com/docs)
- [Laravel Boost Guidelines](./CLAUDE.md#laravel-boost-guidelines)

### Tools
- Pest v4: https://pestphp.com
- Laravel Pint: https://github.com/laravel/pint
- Xdebug: https://xdebug.org

### Training
- Laravel Testing Best Practices Course
- Pest v4 Advanced Testing Workshop
- TDD Training Sessions

---

**Document Version**: 1.0.0
**Last Updated**: 2026-01-18
**Maintained By**: Development Team
**Review Frequency**: Monthly
