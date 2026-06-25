# Quality Checklist — Comprehensive Harness Audit

Use this grid in step 1 of the `harness-evolution` skill.

Verdict: **green** = OK / **yellow** = partial / **red** = missing / **N/A** = not applicable

---

## 1. Tests

| # | Check | Comment | Command / method |
|---|-------|---------|------------------|
| T1 | All public controllers have a Feature test | Check `tests/Feature/` vs `src/Http/Controllers/` | `ls src/Http/Controllers/` then grep in tests |
| T2 | All admin (Filament) controllers have a test | At least one Filament render test per resource | `tests/Feature/Admin/` |
| T3 | Support classes have a Unit test | `WaveSeparatorService`, `VersioningService`, `BlogrImportService`, `BlogrExportService`, etc. | `tests/Unit/` |
| T4 | Form Requests have a validation test | `authorize()` behavior + rules | grep `authorize` in `src/Http/Requests/` |
| T5 | Policies have an authorization test | `UserPolicy` | grep `tests/` for each policy |
| T6 | Artisan commands have a test | — | grep for command name |
| T7 | `vendor/bin/pest --parallel` passes | Global integrity | `vendor/bin/pest --parallel 2>&1` |
| T8 | An XSS regression test for `{!! !!}` | Check `assertDontSee('alert', false)` | grep in `tests/` |

---

## 2. Static analysis (PHPStan / Larastan)

| # | Check | Comment | Command / method |
|---|-------|---------|------------------|
| S1 | `phpstan.neon.dist` or `phpstan.neon` exists | Configuration file | `test -f phpstan.neon.dist` |
| S2 | `phpstan` is in `composer.json` require-dev | Explicit dependency (not just transitive) | `grep -q "phpstan" composer.json` |
| S3 | Analysis passes at configured level | No errors at minimum level 1 | `vendor/bin/phpstan analyse --memory-limit=1G 2>&1` |
| S4 | Level is appropriate for the project | level 5 minimum, aim for level 6+ | Read `parameters.level` in config |
| S5 | Analyzed paths cover `src/` | Included in config | Check `parameters.paths` |

---

## 3. Code style (Pint)

| # | Check | Comment | Command / method |
|---|-------|---------|------------------|
| C1 | `pint.json` exists | Dedicated configuration | `test -f pint.json` |
| C2 | `vendor/bin/pint --test` passes | No style deviations | `vendor/bin/pint --test 2>&1` |
| C3 | Pint is in `composer.json` scripts | For easy execution | `grep -q "pint" composer.json` |
| C4 | CI runs Pint | Dedicated job in `.github/workflows/` | `grep -q "pint" .github/workflows/` |

---

## 4. Linting JS / CSS

| # | Check | Comment | Command / method |
|---|-------|---------|------------------|
| J1 | ESLint is installed (devDependencies) | Static JS analysis | `grep -q "eslint" package.json` |
| J2 | `eslint.config.js` or `.eslintrc.*` exists | ESLint configuration | `test -f eslint.config.js` |
| J3 | Prettier is installed (devDependencies) | JS/CSS formatter | `grep -q "prettier" package.json` |
| J4 | `.prettierrc` or `prettier.config.js` exists | Prettier configuration | `test -f .prettierrc` |
| J5 | Stylelint is installed (if complex CSS) | CSS analysis | `grep -q "stylelint" package.json` |
| J6 | npm scripts `lint` and `format` exist | `package.json` scripts | `grep -qE "lint|format" package.json` |
| J7 | CI runs JS lint | — | `grep -qE "eslint|prettier" .github/workflows/` |

---

## 5. CI / Continuous Integration

| # | Check | Comment | Command / method |
|---|-------|---------|------------------|
| I1 | `composer audit` is run in CI | Vulnerability check | See `.github/workflows/` |
| I2 | `vendor/bin/pest --parallel` is run | Automated tests | See `.github/workflows/` |
| I3 | A lint job (Pint) is present | PHP code quality | See `.github/workflows/` |
| I4 | A static analysis job (PHPStan) is present | Silent bug detection | See `.github/workflows/` |
| I5 | Composer cache is used | CI speedup | See `.github/workflows/` |
| I6 | Node modules (npm) cache is used | CI speedup | See `.github/workflows/` |
| I7 | CI stops on job failure | `|| exit 1` or fail-fast | See `.github/workflows/` |
| I8 | Coverage reports are collected (optional) | CI artifacts | See `.github/workflows/` |

---

## 6. Code coverage

| # | Check | Comment | Command / method |
|---|-------|---------|------------------|
| V1 | `phpunit.xml` has a `<source>` section | Defines directories to cover | `grep -q "source" phpunit.xml` |
| V2 | `phpunit.xml` has a `<coverage>` section | Coverage report | `grep -q "coverage" phpunit.xml` |
| V3 | A minimum coverage threshold is configured (optional) | Ex: `<require><coverage><function>80</...>` | See phpunit.xml |
| V4 | Coverage can be generated locally | PCOV or Xdebug installed | `php -m | grep -qE "pcov|xdebug"` |

---

## 7. Pre-commit hooks

| # | Check | Comment | Command / method |
|---|-------|---------|------------------|
| H1 | Lefthook or Husky is installed | Hook tool | `test -f lefthook.yml || test -f .husky/pre-commit` |
| H2 | hooks configured: Pint (staged) | Style check before commit | Read `lefthook.yml` or `.husky/pre-commit` |
| H3 | hooks configured: ESLint (staged) | JS check before commit | Read hook config file |
| H4 | hooks configured: tests (staged) minimal | Not mandatory if CI covers it | Read hook config file |
| H5 | `--no-verify` documented in AGENTS.md | Exceptional use | `grep -q "no-verify" AGENTS.md` |
