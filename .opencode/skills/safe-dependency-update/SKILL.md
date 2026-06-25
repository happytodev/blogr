---
name: safe-dependency-update
description: >-
  Scans Composer and npm dependencies, applies security filters
  (minimum age 72h, CVE detection), classifies updates by type
  (patch/minor/major), and presents a human-validated plan before
  execution and testing. Use when the user asks to update dependencies,
  review outdated packages, check for security advisories,
  or run composer audit / npm audit.
compatibility: >-
  OpenCode agent with shell access. Requires composer, npm, and network
  access to Packagist API and npm registry for release date lookups.
  Test suite must pass before starting (validates baseline).
metadata:
  author: happytodev
  version: "1.0"
  reference: https://packagist.org/api, https://registry.npmjs.org
---
# Safe Dependency Update

Keep dependencies (Composer + npm) up to date with safeguards
against supply-chain attacks and mandatory human validation.

## When to use

- The user asks "update the dependencies"
- Periodically (every 2-4 weeks)
- After a `composer audit` reports vulnerabilities
- Before a production release
- Before starting a new sprint to start from a clean baseline

## Security policy

Full rules in [references/policy.md](references/policy.md).
Summary:

| Criteria | Rule |
|----------|------|
| **Minimum age** | 72h since publication — unless security fix |
| **Patch** | Proposed automatically (near-zero risk) |
| **Minor** | Proposed with changelog analysis |
| **Major** | Proposed with breaking changes analysis + manual testing recommended |
| **Known CVE** | Priority, no waiting period |
| **Rollback** | Automatic if tests fail after update |

## Checklist

```
Progress:
- [ ] 0. Verify tests pass (baseline)
- [ ] 1. Scan dependencies (outdated + audit)
- [ ] 2. Enrich each candidate (version, age, changelog)
- [ ] 3. Apply security filters (policy.md)
- [ ] 4. Present table to user (validation)
- [ ] 5. Execute validated updates
- [ ] 6. Run tests
- [ ] 7. [IF TESTS OK] Commit lock + CHANGELOG
- [ ] 8. [IF TESTS FAIL] Rollback and report
```

---

## Step 0 — Baseline

Before any modification, verify the test suite is green:

```bash
vendor/bin/pest --parallel
```

If tests fail, **do not continue**. Inform the user.

---

## Step 1 — Dependency scan

Run in parallel:

```bash
# Composer — direct dependencies only (not transitive)
composer outdated --direct -D -f json 2>/dev/null | jq '.'

# Composer — security audit
composer audit --format=json 2>/dev/null | jq '.'

# npm
npm outdated --json 2>/dev/null

# Lock state (to check if already modified)
git diff --name-only
```

**Note:** if the lock is already dirty (`composer.lock` or `package-lock.json`),
ask the user whether to commit first or cancel.

---

## Step 2 — Enrich each candidate

For each outdated package, fetch:

### Composer (Packagist API)
```bash
package="laravel/framework"
version="12.1.2"
curl -s "https://repo.packagist.org/p2/$package.json" | \
  jq ".packages.\"$package\"[] | select(.version == \"$version\") | {version, time}"
```

### npm (npm registry)
```bash
package="tailwindcss"
version="4.1.0"
curl -s "https://registry.npmjs.org/$package/$version" | jq '{version, date}'
```

### Changelog / Release notes
Try to find a readable source:
- `https://github.com/$vendor/$repo/releases/tag/v$version`
- `https://github.com/$vendor/$repo/blob/main/CHANGELOG.md`
- For Laravel: `https://laravel.com/docs/12.x/releases`

### Build candidate entry
```json
{
  "package": "laravel/framework",
  "current": "12.0.0",
  "latest": "12.1.2",
  "type": "minor",
  "age_hours": 120,
  "has_cve": false,
  "changelog_summary": "Added SQLite WAL support, Blade fixes…",
  "risk": "low"
}
```

---

## Step 3 — Security filters

Apply rules from [references/policy.md](references/policy.md):

1. **Exclude** packages with age < 72h **unless** known CVE
2. **Mark priority** if known CVE (even if < 72h)
3. **Classify** according to `policy.md`

**Deliverable:** a filtered and sorted list, ready for the proposal.

---

## Step 4 — Human proposal

Present a full table:

```markdown
## Dependency update proposal

**Baseline tests:** 45 passed (OK)

### Proposed packages

| Package | Current | Available | Type | Age | Risk | Action |
|---------|---------|-----------|------|-----|------|--------|
| laravel/framework | 12.0.0 | 12.1.2 | minor | 120h | low | `composer update` |
| filament/filament | 4.0.0 | 4.1.5 | minor | 200h | low | `composer update` |
| tailwindcss | 3.1.0 | 4.1.0 | major | 800h | high | needs analysis |
| axios | 1.11.0 | 1.12.5 | minor | 96h | low | `npm install` |

### Excluded (age < 72h, no CVE)
| Package | Current | Available | Age | Reason |
|---------|---------|-----------|-----|--------|
| vite | 7.0.7 | 7.1.0 | 48h | < 72h |

### Notes
- `tailwindcss` v4 is a **major**: check breaking changes (see CHANGELOG)
- No CVE detected by `composer audit`

### Default validated actions:
1. `composer update laravel/framework filament/filament`
2. `npm install axios@latest`

⚠️ **Major requires explicit validation.**
Do you approve these updates?
```

**Do not execute anything until the user responds "yes" or equivalent.**

---

## Step 5 — Execution

Apply the validated updates, in order:

```bash
# Composer updates (one by one for isolation)
composer update "laravel/framework" --with-dependencies
composer update "filament/filament" --with-dependencies

# npm updates
npm install axios@latest
```

**Do not update** unvalidated packages. If the user
rejected `tailwindcss` major, do not touch it.

---

## Step 6 — Tests

```bash
vendor/bin/pest --parallel
```

- **Success:** → step 7
- **Failure:** → step 8

---

## Step 7 — Commit (success)

If updates were applied:

```bash
# Add modified lock files
git add composer.lock package-lock.json composer.json package.json

# Update CHANGELOG.md (section Unreleased > Changed)
# Suggestion:
# ### Changed
# - Dependencies: laravel/framework 12.0.0 → 12.1.2, filament/filament 4.0.0 → 4.1.5

git commit -m "$(cat <<'EOF'
chore(deps): update dependencies

- laravel/framework 12.0.0 → 12.1.2
- filament/filament 4.0.0 → 4.1.5
- axios 1.11.0 → 1.12.5

Tests: 45 passed
EOF
)"
```

Note: only commit what the user validated. If the change
is part of other work in progress, do not commit without agreement.

---

## Step 8 — Rollback (failure)

```bash
# Restore lock files
git checkout -- composer.lock package-lock.json

# If composer.json was modified
git checkout -- composer.json
```

Then report:

```markdown
## Rollback performed

Tests failed after the following updates:
- laravel/framework 12.0.0 → 12.1.2

**Error:** [copy error message]

Changes reverted via `git checkout`.
```

---

## Important rules

- **Never** run `composer update` without parameters (updates everything, including transitive)
- **Never** modify `composer.json` unless the user asked for it (version constraints)
- **Always** use `composer update pkg --with-dependencies` rather than `composer require pkg:^x.y`
- **Always** verify constraints in `composer.json` are compatible with the target version
- Do not update packages blocked by PHP constraint (check `composer.json` `require.php`)

## Resources

- [references/policy.md](references/policy.md) — detailed rules (72h, patch/minor/major, security exceptions)
- [composer.json](../../composer.json) — Composer dependencies
- [package.json](../../package.json) — npm dependencies
