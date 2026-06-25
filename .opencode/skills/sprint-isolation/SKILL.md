---
name: sprint-isolation
description: >-
  Isolate code from parallel sprints mixed in the same working tree into clean
  Git branches. Backup the working tree, then cherry-pick files sprint by
  sprint into dedicated branches from main.
compatibility: >-
  Git, shell access, working tree with mixed code from multiple sprints
  or uncommitted feature branches.
metadata:
  author: happytodev
  version: "1.0"
---

# Parallel Sprint Isolation

When multiple agents work in parallel on the same repository without
committing, the working tree contains code from all sprints mixed together.
This skill extracts each sprint cleanly.

## When to use

- Multiple `feat/sprint-*` branches pointing at the same commit
- `git status` shows modified + untracked files from multiple sources
- Cannot open clean MRs without separation

## Workflow

```
Progress:
- [ ] 1. Backup the working tree into a temporary branch
- [ ] 2. Checkout updated main
- [ ] 3. For each sprint, create a dedicated branch
- [ ] 4. Restore only the sprint's files from the backup
- [ ] 5. Commit + test in isolation
- [ ] 6. Repeat for each sprint
```

## Step 1 — Full Backup

```bash
git checkout -b backup/all-sprints
git add -A
git commit -m "temp: full backup before separation"
git checkout main
git pull origin main
```

## Step 2 — Isolate a Sprint

Identify the sprint's files (new + modified):

```bash
# List new sprint files
git diff main backup/all-sprints --name-only --diff-filter=A | grep -i pattern

# List modified files
git diff main backup/all-sprints --name-only --diff-filter=M | grep -i pattern
```

Create the branch and restore files:

```bash
git checkout -b feat/sprint-N-description main
git checkout backup/all-sprints -- path/file1 path/file2
```

For files modified by multiple sprints, use `git diff`
to extract only the current sprint's changes:

```bash
git diff main backup/all-sprints -- app/Models/MyModel.php
# Manually apply the relevant hunks
```

## Step 3 — Verification

```bash
composer test
git log --oneline main..HEAD
```

## Pitfalls (Blogr)

- `src/Models/BlogPost.php` or `BlogPostTranslation.php` may be touched by multiple sprints — check for conflicting scopes, accessors, or relationship changes
- `src/Filament/Resources/BlogPostResource/` forms use `Filament\Schemas\Schema` (not `Filament\Forms`) — ensure form schema methods are not duplicated across sprints
- Migration files (`2026_06_*` pattern) must never be split across sprints without checking for timestamp collisions
- Translation table schemas (e.g. `blog_post_translations`, `cms_page_translations`) share `[entity_id, locale]` unique keys — concurrent schema changes can break foreign keys
- `resources/views/components/blocks/` Blade files may contain Tailwind classes — remember to rebuild dist CSS with `npm run build` before testing
- Do not version `storage/framework/testing/`, `storage/framework/views/`, `.phpunit.result.cache`, or `test-results.txt`
- Never forget to run `vendor/bin/pest --parallel` after isolating a sprint — tests are in `tests/Feature/` and `tests/Unit/`
