# SEO Tests for Blogr

This directory contains tests for the SEO features of the Blogr package.

## Available Tests

### SEOHelperTest.php
Unit tests for the `SEOHelper` class:
- Metadata generation for listing pages (index, category, tag)
- Metadata generation for blog posts
- JSON-LD structured data generation
- Special characters handling
- Canonical URLs management
- Images and tags management

### BlogSEOTest.php
Integration tests for blog pages:
- Meta tags verification in views
- JSON-LD rendering validation
- Structured data enabled/disabled testing
- Article tags testing
- Author information testing
- Images in metadata testing

### SEOConfigTest.php
Configuration tests:
- SEO configuration loading verification
- SEOHelper methods existence testing
- Non-empty results validation

## Running Tests

### Run all SEO tests
```bash
./vendor/bin/pest tests/Feature/SEOHelperTest.php
./vendor/bin/pest tests/Feature/BlogSEOTest.php
./vendor/bin/pest tests/Feature/SEOConfigTest.php
```

### Run all package tests
```bash
./vendor/bin/pest
```

## Test Configuration

Tests use:
- In-memory SQLite database
- Factories for creating test data
- Test routes defined in `tests/Feature/routes.php`
- Test migrations for the users table

## Test Coverage

Tests cover:
- ✅ Basic metadata generation (title, description, keywords)
- ✅ Open Graph and Twitter Cards data
- ✅ Canonical URLs
- ✅ JSON-LD structured data
- ✅ Blog posts management with custom metadata
- ✅ Tags and categories management
- ✅ Images management
- ✅ Structured data activation/deactivation
- ✅ Special characters escaping
- ✅ JSON-LD validation

## Test Data

Tests use factories to create:
- Users
- Categories
- Blog posts
- Tags

This data is automatically cleaned up after each test.
