# Format CHANGELOG — Blogr

Target file: `CHANGELOG.md` at the repository root.

## Structure

```markdown
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- …

### Changed
- …

### Fixed
- …

### Removed
- …

## [0.1.0] - 2026-06-23

### Added
- …

[Unreleased]: https://github.com/happytodev/blogr/compare/v0.1.0...main
[0.1.0]: https://github.com/happytodev/blogr/releases/tag/v0.1.0
```

## Writing rules

1. **Language**: English, concise and user/maintainer-oriented style.
2. **One bullet = one notable change** visible (feature, fix, breaking change).
3. **Sections**:
   - `Added` — new capability
   - `Changed` — behavior or notable documentation change
   - `Fixed` — bug fix
   - `Removed` — removal
   - `Security` — security fix (if applicable)
4. **Omit**: internal refactoring, formatting, standalone test commits without documented impact (unless requested otherwise).
5. **Release version**: when tagging `vX.Y.Z`, rename `[Unreleased]` to `[X.Y.Z] - YYYY-MM-DD` and create a new empty `[Unreleased]` section.

## Examples

### Feature

```markdown
### Added
- Filter by status (published / draft) in the admin post list using Filament ToggleColumns
```

### Fix

```markdown
### Fixed
- Duplicate slugs now prevent post creation with an explicit error message
```

### Docs / skill

```markdown
### Added
- `git-changelog-workflow` skill for atomic commits and release preparation
```

## Validation before writing

Always show a **proposal** block to the user:

```markdown
## Proposed CHANGELOG ([Unreleased])

### Added
- …

### Fixed
- …
```

Only write to `CHANGELOG.md` after explicit approval.
