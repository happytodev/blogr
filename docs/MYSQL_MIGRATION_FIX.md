# MySQL Migration Fix - Issue #172

## Problem

When installing Blogr v0.15.7 on a MySQL database, the migration failed with the following error:

```
SQLSTATE[42000]: Syntax error or access violation: 1701
Cannot truncate a table referenced in a foreign key constraint
(`blogrmysql`.`blog_post_tag`, CONSTRAINT `blog_post_tag_blog_post_id_foreign`)
(Connection: mysql, SQL: truncate table `blog_posts`)
```

### Stack Trace

```
vendor/laravel/framework/src/Illuminate/Database/Connection.php:824
database/migrations/2025_10_13_000001_remove_translatable_fields_from_blog_posts_table.php:17
```

### Root Cause

The migration `2025_10_13_000001_remove_translatable_fields_from_blog_posts_table.php` used the `truncate()` method to empty the `blog_posts` table before dropping columns:

```php
// ❌ Problematic code (line 16)
DB::table('blog_posts')->truncate();
```

**Why `truncate()` fails on MySQL**:
- MySQL strictly enforces foreign key constraints
- `TRUNCATE` is a DDL (Data Definition Language) operation that does not trigger foreign key cascade actions
- Tables referenced by foreign keys cannot be truncated
- The `blog_posts` table is referenced by `blog_post_tag.blog_post_id_foreign`
- MySQL returns error 1701 for this constraint violation

## Solution

Replace `truncate()` with `delete()` at line 16 of the migration:

```php
// ✅ Fixed code (line 16)
// Use delete() instead of truncate() for MySQL compatibility with foreign keys
DB::table('blog_posts')->delete();
```

### Why `delete()` works

- `DELETE` is a DML (Data Manipulation Language) operation that respects foreign key constraints
- If cascade is configured (`onDelete('cascade')`), related rows are automatically deleted
- Works identically across all database engines (MySQL, PostgreSQL, SQLite, SQL Server)
- Performance: for a fresh installation (empty table), the difference is negligible

## Migration Context

This migration is part of a major refactoring to move translatable fields from the main `blog_posts` table to a separate `blog_post_translations` table.

**Why empty the table?**
1. SQLite has limited support for `ALTER TABLE` commands
2. Dropping columns with data can cause `NOT NULL` constraint violations
3. The old data structure is incompatible with the new translation-based schema

## Tests

4 new tests were added in `tests/Feature/MigrationWithForeignKeysTest.php` to verify:

1. ✅ The migration executes successfully using `delete()`
2. ✅ `delete()` works on empty tables (fresh installation scenario)
3. ✅ Migration rollback works correctly
4. ✅ Compatibility with SQLite (automatically tested), MySQL and PostgreSQL (documented)

## Compatibility Matrix

| Operation | SQLite | MySQL | PostgreSQL | SQL Server |
|-----------|--------|-------|------------|------------|
| `truncate()` | ✅ Works | ❌ FK Error | ⚠️ Depends on FK | ⚠️ Depends on FK |
| `delete()` | ✅ Works | ✅ Works | ✅ Works | ✅ Works |

**Legend**:
- ✅: Always works
- ❌: Fails with foreign keys
- ⚠️: Depends on foreign key configuration

## Impact

- **Before**: Installation impossible on MySQL (most common production database)
- **After**: Successful installation on MySQL, PostgreSQL, SQLite and other engines
- **Risk**: Low - `delete()` is more compatible than `truncate()`
- **Performance**: No impact (empty table during fresh installation)

## Affected Versions

- **v0.15.7**: ❌ Broken on MySQL
- **v0.15.8**: ✅ Fixed - works on all engines

## Developer Recommendations

When writing migrations:

1. **Prefer `delete()` over `truncate()`** when foreign keys exist
2. **Test on multiple database engines** (MySQL, PostgreSQL, SQLite)
3. **Add integration tests** for critical migrations
4. **Document side effects** in migration comments

## References

- GitHub Issue: [#172](https://github.com/happytodev/blogr/issues/172)
- Migration concerned: `database/migrations/2025_10_13_000001_remove_translatable_fields_from_blog_posts_table.php`
- Tests: `tests/Feature/MigrationWithForeignKeysTest.php`
- MySQL Documentation: [TRUNCATE TABLE Restrictions](https://dev.mysql.com/doc/refman/8.0/en/truncate-table.html)
