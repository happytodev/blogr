---
name: release-manager
description: Automate Blogr package releases: version bumping, CHANGELOG updates, tagging, and publishing to GitHub.
---

## When to use

Trigger phrases: "release", "tag a new version", "publish vX.Y.Z", "cut a release", "bump version".

## Workflow

### 1. Determine the new version

- Read current version from `composer.json` (`"version": "0.x.y"`)
- Ask user for bump type: `patch`, `minor`, `major`, or an explicit version like `0.19.0`
- Compute the new version accordingly

### 2. Generate release notes

- Run: `git log $(git describe --tags --abbrev=0)..HEAD --oneline --no-decorate`
- Format as markdown with conventional commit categories (Features, Bug Fixes, Dependencies, etc.)
- Present to user for editing/confirmation before proceeding

### 3. Run tests

- Run: `vendor/bin/pest --parallel`
- If any **real** failures (not flaky parallel artifacts), abort and tell the user
- If only the known flaky `blogr.php` config-not-found error, continue

### 4. Update version files

- **`composer.json`**: Edit the `"version"` field
- **`src/Blogr.php`**: Edit `const VERSION = '...'`

### 5. Update CHANGELOG.md

- Prepend a new entry at the top following the existing format:

  ```markdown
  ## [v{version}](https://github.com/happytodev/blogr/compare/v{version}...v{previous}) - {date}

  ### ✨ Features (or 🐛 Bug Fixes | ⬆️ Dependencies)

  - **{title}**: {description}
  ```

- Use the user-approved release notes content
- Keep existing entries intact

### 6. Commit, tag, push

```bash
git add -A
git commit -m "Release v{version}"
git tag v{version}
git push origin main v{version}
```

### 7. Create GitHub Release

- Run: `gh release create v{version} --generate-notes`

### 8. Confirm

- Inform the user the release was published with the URL and commit hash
