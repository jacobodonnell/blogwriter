# Test Groups

The test suite uses Pest test groups to separate fast tests from slow tests.

## Test Group: `slow`

The `slow` group contains tests that take significant time to run (20+ seconds each), primarily seeder tests that create and seed full datasets.

**Files:**
- `tests/Feature/SeederTest.php` - All seeder validation tests

## Running Tests

### Default (Fast Tests Only)
Excludes slow tests automatically via `phpunit.xml`:
```bash
php artisan test --compact
```
**Performance:** ~56 tests in ~6 seconds

### All Tests (Including Slow)
Uses `phpunit.all.xml` configuration which doesn't exclude any groups:
```bash
php artisan test -c phpunit.all.xml --compact
```
**Performance:** 61 tests in ~72 seconds

### Only Slow Tests
Runs just the slow group when you need to verify seeding:
```bash
php artisan test --group=slow --compact
```
**Performance:** 5 tests in ~67 seconds

### With Parallel Execution
Run all tests faster using parallel execution:
```bash
php artisan test -c phpunit.all.xml --parallel --compact
```
**Performance:** 61 tests in ~20-30 seconds (depends on CPU cores)

### Profile Tests
Identify slow tests to add to the slow group:
```bash
php artisan test --profile
php artisan test -c phpunit.all.xml --profile
```

## Configuration Files

- `phpunit.xml` - Default config, excludes `slow` group
- `phpunit.all.xml` - Runs all tests, no exclusions

## Adding Tests to the Slow Group

Wrap tests in a `describe()` block with `->group('slow')`:

```php
describe('my slow tests', function (): void {
    it('performs slow operation', function (): void {
        // test code
    });

    it('another slow test', function (): void {
        // test code
    });
})->group('slow');
```

## Benefits

- **Faster development cycle:** Default test runs are 12x faster (~6s vs ~72s)
- **Still thorough:** Slow tests remain available when needed
- **CI/CD friendly:** Use `-c phpunit.all.xml --parallel` for fastest complete testing
- **Flexible:** Run specific groups as needed
