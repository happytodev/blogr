# Harness State Report

Last updated: 2026-06-26

## Axes audit

| Axis | Status | Notes |
|------|--------|-------|
| **Tests** | 🟢 | 1192 passed, 0 failed |
| **Static analysis** | 🟢 | `larastan/larastan` installed, level 5, 487 errors baselined |
| **Code style (Pint)** | 🟢 | `laravel/pint` installed, preset `laravel`, passes |
| **CSS/JS build** | 🟢 | Vite build succeeds |
| **CI** | 🟢 | `.github/workflows/run-tests.yml` + `fix-php-code-style-issues.yml` exist |
| **Pre-commit hooks** | 🟢 | Lefthook installed, runs Pint + PHPStan on pre-commit |
| **Coverage** | 🟢 | Configured in phpunit.xml.dist |

## Improvements applied

| Date | Change | Details |
|------|--------|---------|
| 2026-06-26 | Bug workflow rule in AGENTS.md | Forces `issue-to-mr` skill + issue lifecycle (creation → iteration → closure) |
| 2026-06-26 | Pint installed | `composer require --dev laravel/pint`, `pint.json` with `laravel` preset |
| 2026-06-26 | PHPStan installed | `composer require --dev larastan/larastan`, `phpstan.neon.dist` level 5, baseline generated |
| 2026-06-26 | Lefthook installed | `npm install -D lefthook`, `lefthook.yml` with Pint + PHPStan pre-commit hooks |

## Files modified/created

| File | Purpose |
|------|---------|
| `AGENTS.md` | Bug workflow + issue lifecycle rules |
| `pint.json` | Pint config (laravel preset) |
| `phpstan.neon.dist` | PHPStan config (level 5) |
| `phpstan-baseline.neon` | PHPStan baseline (487 errors) |
| `lefthook.yml` | Pre-commit hooks config |
| `.opencode/harness-state-report.md` | This report |

## Next tracks

All identified gaps from the initial audit are now resolved. Future improvements could include:
- ESLint for JS linting
- Higher PHPStan level (6+)
- Coverage threshold enforcement
