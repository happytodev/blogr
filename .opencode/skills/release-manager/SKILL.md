---
name: release-manager
description: Automate Blogr package releases: version bumping, CHANGELOG updates, tagging, and publishing to GitHub.
---

## When to use

Trigger phrases: "release", "tag a new version", "publish vX.Y.Z", "cut a release", "bump version".

## Workflow

### 1. Preview changes since the last release

- Run: `git log $(git describe --tags --abbrev=0)..HEAD --oneline --no-decorate`
- If no tags exist yet, use: `git log --oneline --no-decorate`
- Present the output to the user so they can see what changed before choosing a bump type

### 2. Determine the new version

- Read current version from `composer.json` (`"version": "0.x.y"`)
- Ask user for bump type: `patch`, `minor`, `major`, or an explicit version like `0.19.0`
- **Compute the new version using semver rules correctly**:

  | Current | patch (Z+1) | minor (Y+1, Z=0) | major (X+1, Y=Z=0) |
  |---------|-------------|------------------|-------------------|
  | `0.22.0` | `0.22.1` | `0.23.0` | `1.0.0` |
  | `0.22.5` | `0.22.6` | `0.23.0` | `1.0.0` |
  | `1.0.0` | `1.0.1` | `1.1.0` | `2.0.0` |

  ⚠️ **Common mistake**: patch is NOT `0.22.0 → 0.23.0` — that is a minor bump. Patch only increments the last digit.

- Present the computed version to the user for confirmation

### 3. Organize uncommitted changes into feature-grouped commits

- Run `git status --short` to list changed/new files
- **If there are no uncommitted changes**, skip this step
- **If there are uncommitted changes**, group files by feature area using path heuristics:

  | Pattern | Suggested commit message |
  |---|---|
  | `src/Services/LocaleService*`, `src/Traits/ClearsLocaleCache*`, `src/Helpers/LocaleHelper*`, view composer changes, route pattern changes in provider | `feat: locale auto-detection with cache invalidation` |
  | `config/blogr.php` (disabled keys), `CmsPageController*`, `src/Models/CmsPage*` (availableLocales) | `feat: disabled locales return 404 on frontend` |
  | `src/Filament/Pages/BlogrSettings*` | `feat: redesign multilingual settings UI` |
  | `src/Filament/Resources/CmsPage*`, `CmsBlockBuilder*` (non-import/export) | `feat: per-translation CMS page editing` |
  | `resources/views/components/*` (flag emojis) | `feat: flag emojis in navigation and language-switcher` |
  | `resources/views/cms/pages/*`, `resources/views/layouts/*` | `feat: CMS content rendering` |
  | `src/Commands/*Import*` | `fix: CLI import delegates to CmsPageImportExportService` |
  | `INSTALL.md`, `storage/app/blogr-exports/*` | `docs: install guide and install page translations` |
  | `tests/*` | `test: add tests for new features` (attach to relevant feature commit if possible, otherwise a single test commit) |

- **Heuristics**:
  - Files matching multiple patterns go with the *first* matching feature group
  - Config-only changes to keys unrelated to above → `chore: update config`
  - Dependabot / lockfile changes → `chore(deps): update dependencies`
- For each group, stage and commit:
  ```bash
  git add <file1> <file2> ...
  git commit -m "<type>(<scope>): <description>"
  ```

### 4. Generate and present release notes

- Use the commit log from step 1 to format as markdown with conventional commit categories (Features, Bug Fixes, Dependencies, etc.)
- **Always display the exact CHANGELOG.md entry** that will be prepended to the file. Show it to the user first using Markdown code block so they can see the full entry before approving.
- **Show the formatted markdown to the user using the `question` tool** with a "Looks good, proceed" option and an "Edit notes" option.
- **Do NOT just ask "proceed?"** — display the full formatted release notes in the question so the user can review every line before approving.
- Include a third option "Cancel" in case the user wants to abort.
- Only proceed when the user explicitly approves.

### 5. Run tests (ZERO TOLERANCE)

- Run: `vendor/bin/pest --parallel` (takes 4-5s)
- **Do NOT pipe through grep/tail/head — capture the raw output.** The last lines show the result:
  ```
  Tests:    56 skipped, 911 passed (2720 assertions)
  ```
- **If ANY test fails (even 1), abort immediately.** Do not proceed, do not commit, do not push.
- Zero tolerance: "skipped" and "passed" are OK; "failed" or "ERROR" means STOP.
- Report the failure count to the user and tell them what tests failed.

### 6. Update version files (atomic commit)

- **`composer.json`**: Edit the `"version"` field
- **`src/Blogr.php`**: Edit `const VERSION = '...'`
- **Commit** these two changes atomically:
  ```bash
  git add composer.json src/Blogr.php
  git commit -m "chore: bump version to v{version}"
  ```

### 7. Update CHANGELOG.md (atomic commit)

- Prepend a new entry at the top following the existing format:

  ```markdown
  ## [v{version}](https://github.com/happytodev/blogr/compare/v{version}...v{previous}) - {date}

  ### ✨ Features (or 🐛 Bug Fixes | ⬆️ Dependencies)

  - **{title}**: {description}
  ```

- Use the user-approved release notes content from step 4
- Keep existing entries intact
- **Commit** only CHANGELOG.md:
  ```bash
  git add CHANGELOG.md
  git commit -m "docs(changelog): v{version}"
  ```

### 8. Tag

```bash
git tag v{version}
```

### 9. Push commits and tag

```bash
git push origin main v{version}
```

### 10. Create GitHub Release

- Set `RELEASE_NOTES` to the *exact markdown* the user approved in step 4 (the body of the CHANGELOG entry, without the heading/date line)
- Run: `gh release create v{version} --title "v{version}" --notes "$RELEASE_NOTES"`

### 11. Confirm

- Inform the user the release was published with the URL and commit hash
