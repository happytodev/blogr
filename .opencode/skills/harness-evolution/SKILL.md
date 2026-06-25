---
name: harness-evolution
description: >-
  Audits all quality axes of the Blogr package (tests, static analysis,
  code style, JS/CSS linting, CI, coverage, pre-commit hooks) and proposes
  one improvement at a time, validated by the human before execution.
  Use when starting a new development phase, after a sprint, or when the user
  asks to review or improve the project's quality infrastructure.
compatibility: >-
  OpenCode agent with read/write access to the repository. Requires shell access
  for composer, npm, vendor/bin/pest, git commands. PHP 8.3+, Node.js, Pest,
  Pint, and optional tools (PHPStan, ESLint, Prettier) installed globally
  or via the project's vendor.
metadata:
  author: happytodev
  version: "1.0"
---
# Harness Evolution — Continuous Quality Harness Improvement

Audits and improves the project's quality infrastructure, **one step at a time**,
with mandatory human validation before each change.

## When to use

- At the start or end of a development sprint
- When the user asks "what can we improve in the project's quality?"
- When new files / features are added without tests or checks
- When CI fails or a quality tool is missing
- Periodically (every 2-3 sprints) to keep the harness up to date

## General principle

This skill **never** makes multiple changes at once. It presents
**one proposal at a time**, waits for explicit validation, executes, then
can be re-invoked for the next one.

```
Loop:
  ┌───────────────────────────────────────┐
  │  1. Full audit of all axes            │
  │  2. Identify the priority gap         │
  │  3. Propose the improvement (human)   │
  │  4. Validated ? → Execute + test      │
  │  5. Update the state report           │
  └─────────┬─────────────────────────────┘
            └── Propose next one or finish
```

## Checklist

```
Progress:
- [ ] 0. Read the last state report (state-report.md in .opencode/ if exists)
- [ ] 1. Full audit (quality-checklist.md)
- [ ] 2. Prioritize gap #1 (effort vs impact)
- [ ] 3. Present the proposal to the user
- [ ] 4. [IF validated] Execute the improvement
- [ ] 5. Run tests post-change
- [ ] 6. Update .opencode/harness-state-report.md
- [ ] 7. Propose the next improvement or conclude
```

---

## Step 0 — Previous state report

If the file `.opencode/harness-state-report.md` exists, read it to know
the reference state and avoid re-proposing what has already been handled.

Otherwise, everything is up for audit.

---

## Step 1 — Full audit

Run the checks from the [quality checklist](references/quality-checklist.md).

For each axis, collect a simple verdict:
- **green** = OK, nothing to do
- **yellow** = partial, improvement possible
- **red** = missing, improvement needed

**Diagnostic commands:**

```bash
# Tests
vendor/bin/pest --parallel 2>&1 | tail -5

# Pint (code style)
vendor/bin/pint --test 2>&1

# PHPStan (if configured)
vendor/bin/phpstan analyse --memory-limit=1G 2>&1 | tail -10

# Vite build
npm run build 2>&1 | tail -5

# CI
ls .github/workflows/

# phpunit.xml coverage config
grep -n "coverage" phpunit.xml.dist phpunit.xml 2>/dev/null

# Pint config
test -f pint.json && echo "exists" || echo "missing"

# Pre-commit hooks
test -f .husky/pre-commit && echo "husky exists" || echo "no husky"
test -f lefthook.yml && echo "lefthook exists" || echo "no lefthook"

# ESLint / Prettier
test -f eslint.config.js && echo "eslint exists" || echo "no eslint"
test -f .prettierrc && echo "prettier exists" || echo "no prettier"
```

**Deliverable:** synthetic state report (template: [state-report-template.md](references/state-report-template.md)).

---

## Step 2 — Prioritization

Rank detected gaps (red first, then yellow) according to:

| Criterion | Weight |
|-----------|--------|
| **Impact** : how many future developments does this protect? | high/medium/low |
| **Effort** : estimated time to set up | hours |
| **Regression risk** : can the change break something? | yes/no |
| **Dependency** : does another gap block this one? | yes/no |

Rule: **always propose the highest-impact, lowest-effort gap**
(quick win) first.

Example priority order for this project:
1. Configure Pint (add to require-dev + create pint.json)
2. Configure PHPStan (extension installer is already allowed in composer.json)
3. Add a GitHub Actions CI workflow with lint + static analysis jobs
4. Set up ESLint (Prettier is already installed)
5. Set up pre-commit hooks

---

## Step 3 — Human proposal

Present a structured message to the user:

```markdown
## Improvement proposal n°X

**Axis:** [tests | static-analysis | code-style | js-linting | ci | coverage | hooks]

**Observation:** [audit result for this axis]
**Proposal:** [technical description of what needs to be done]
**Estimated effort:** [e.g. 15 min]
**Risk:** [none / low / medium]

### Execution plan
1. [step 1]
2. [step 2]
3. ...

Do you validate this improvement? (yes/no)
```

**Do not execute anything until the user has replied "yes", "OK", "validate", or equivalent.**

---

## Step 4 — Execution

Apply the validated plan. For each type of improvement, here are typical actions:

### Tests
```bash
# Create a Feature test for a missing controller
vendor/bin/pest --parallel
```
→ Write test content according to the project's Pest conventions.
→ Use `vendor/bin/pest --parallel` to verify.

For new tests, check existing test base classes (`TestCase`, `LocalizedTestCase`, `CmsTestCase`, etc.) in `tests/` and match the convention.

### PHPStan / Larastan
```bash
# Ensure larastan is in require-dev first
composer require --dev larastan/larastan

# Initialize config
vendor/bin/phpstan init
# Generates phpstan.neon.dist with level 1 and the right paths
```

Typical config for `phpstan.neon.dist`:
```neon
includes:
    - vendor/larastan/larastan/extension.neon
    - phpstan-baseline.neon

parameters:
    level: 5
    paths:
        - src/
    tmpDir: storage/framework/cache/phpstan
```

### Coverage
Coverage is already configured in `phpunit.xml.dist` with `./src` as the source directory. If a threshold is desired:

```xml
<source>
    <include>
        <directory suffix=".php">./src</directory>
    </include>
</source>
```

Optionally a minimum threshold:
```xml
<coverage>
    <report>
        <html outputDirectory="build/coverage"/>
    </report>
</coverage>
```

### CI
Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: true
      matrix:
        php: [8.3, 8.4]
        laravel: [12.*]
        stability: [prefer-stable]

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          coverage: pcov

      - name: Install dependencies
        run: |
          composer install --no-interaction --prefer-dist --no-progress

      - name: Run tests
        run: vendor/bin/pest --parallel --ci

  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.4
      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist --no-progress
      - name: Pint
        run: vendor/bin/pint --test
```

### ESLint
```bash
npm install -D eslint eslint-config-prettier
```

### Pre-commit hooks (via Lefthook — lighter than Husky)
```bash
composer require --dev evp/lefthook
npx lefthook install
```

Config `lefthook.yml`:
```yaml
pre-commit:
    parallel: true
    commands:
        pint:
            run: vendor/bin/pint --test
```

---

## Step 5 — Post-change tests

Always run after execution:

```bash
vendor/bin/pest --parallel
```

- If tests pass → step 6
- If tests fail → fix or rollback and inform the user

---

## Step 6 — Update state report

Write or update `.opencode/harness-state-report.md` with the new status
of the modified axis, the date, and the next improvement track.

Format: [state-report-template.md](references/state-report-template.md).

---

## Step 7 — Next improvement

After a successful improvement:

> "Improvement applied successfully. I can propose another one if you want."

If the user says yes → return to step 2 (next gap in the prioritisation).
Otherwise → done.

---

## Pitfalls (this repo)

- Pint (`laravel/pint`) is **not** currently in `composer.json` require-dev — do not run `vendor/bin/pint --test` without adding it first. Install with `composer require --dev laravel/pint` and create a `pint.json` if needed.
- Rust-based Pint (v2.x+) requires PHP 8.2+; this project uses PHP 8.3+, so it is compatible.
- `larastan/larastan` is **not** in `composer.json` require-dev, but `phpstan/extension-installer` is already allowed in `composer.json` (`"allow-plugins"`). This means the project is ready for PHPStan but it hasn't been installed yet.
- ESLint is **not** installed; only Prettier exists (in `devDependencies`). If adding ESLint, use `npm install -D eslint` and configure it.
- No GitHub Actions workflows exist yet (`.github/workflows/` is empty or missing). First CI setup must create the directory and workflow files.
- Coverage is **already configured** in `phpunit.xml.dist` with `./src` as the source directory and HTML/text/Clover reports — this axis is green.
- This is a **Laravel package**, not a full Laravel application — do NOT use `php artisan` commands that assume a full app context. Use `vendor/bin/testbench` for Artisan-like commands and `vendor/bin/pest` for tests.
- Test base classes vary: `TestCase` (standard), `LocalizedTestCase` (locales enabled), `CmsTestCase` (CMS + homepage), and combinations. Always check which base class a test file should extend — see AGENTS.md for details.
- The architecture is **translation-first**: main tables hold only non-translatable fields; translations live in separate tables with `[entity_id, locale]` unique constraints. Tests must cover translation tables too.
- Pivot tables link **translations**, not main entities (`blog_post_translation_category`, `blog_post_translation_tag`).
- **Never commit, tag, or push** — the `release-manager` skill handles releases. Violating this is a process error.
- The state report file is versioned (`.opencode/harness-state-report.md`) — commit it alongside changes only through the release-manager workflow.

## Resources

- [references/quality-checklist.md](references/quality-checklist.md) — exhaustive grid of axes
- [references/state-report-template.md](references/state-report-template.md) — audit report template
- [AGENTS.md](../../AGENTS.md) — project conventions
- [issue-to-mr](../issue-to-mr/SKILL.md) — if the improvement generates code to ship
