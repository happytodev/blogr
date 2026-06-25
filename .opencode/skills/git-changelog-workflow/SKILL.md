---
name: git-changelog-workflow
description: >-
  Analyzes Git changes, proposes CHANGELOG.md entries (user validation required),
  creates atomic Conventional Commits, places work on a type-named branch
  (feat, fix, docs…), and proposes a GitHub Pull Request. Use when the user asks
  to commit changes, update the changelog, prepare a branch/PR, or finalize work
  with atomic commits.
compatibility: >-
  OpenCode agent with git and shell access. Remote GitHub (origin). Prefer gh CLI
  for PR and issue creation; otherwise provide the GitHub compare URL.
  Note: if git HTTPS fails with HTTP Basic Access Denied, configure the remote
  with a token: `git remote set-url origin https://USER:TOKEN@github.com/happytodev/blogr.git`
  then restore the clean URL after push.
metadata:
  author: happytodev
  version: "1.1"
  reference: https://opencode.ai
---

# Git, CHANGELOG and Pull Request Workflow

Orchestrates end-of-work on this repository: analysis → branch → CHANGELOG proposal → atomic commits → PR.

**Never** push, commit, or open a PR without explicit user validation at the steps outlined below.

## When to use

- The user asks to commit, prepare a branch, update the CHANGELOG, or create a PR
- A code task is complete and needs to be delivered cleanly
- Several heterogeneous changes need to be split into atomic commits

## Checklist

```
Progress:
- [ ] 1. Analyze Git state and classify changes
- [ ] 2. Verify / create the dedicated branch
- [ ] 3. Propose CHANGELOG entries (await validation)
- [ ] 4. Atomic commits (await validation of the plan)
- [ ] 5. Propose the Pull Request (await validation)
```

---

## Step 1 — Analyze changes

Run **in parallel**:

```bash
git status
git diff
git diff --staged
git log -10 --oneline
git branch --show-current
```

For each batch of modified files, determine:

| Type | Branch prefix | Commit type | CHANGELOG section |
|------|---------------|-------------|-------------------|
| New feature | `feat/` | `feat` | `### Added` |
| Bug fix | `fix/` | `fix` | `### Fixed` |
| Documentation only | `docs/` | `docs` | `### Changed` or `### Added` |
| Refactoring with no functional change | `refactor/` | `refactor` | `### Changed` |
| Tests only | `test/` | `test` | *(often omitted from CHANGELOG)* |
| Skill / agent tooling | `skill/` | `docs` or `chore` | `### Added` |
| Maintenance (deps, config) | `chore/` | `chore` | `### Changed` |

See conventions detail: [references/branch-and-commits.md](references/branch-and-commits.md).

**Deliverable**: structured summary for the user:

```markdown
## Change analysis

**Current branch:** `…`
**Primary type:** feat | fix | docs | …
**Recommended branch:** `type/short-description`

### Proposed atomic batches
1. `type(scope): message` — files: …
2. …

### Proposed CHANGELOG entries
- …
```

If multiple types coexist (e.g. `feat` + `fix`), **split into separate branches** and notify the user before continuing.

---

## Step 2 — Dedicated branch

1. Branch from up-to-date `main` (`git fetch origin` if network available).
2. Name: `{type}/{description-kebab-case}` — max ~50 characters, no `--`.
3. **One topic per branch**: no mixing unrelated feat + fix.

```bash
git checkout main
git pull origin main   # if possible
git checkout -b feat/short-name
```

If the user is already working on the correct branch, do not recreate unnecessarily.

**Repository examples:** `fix/restore-blog-admin-routes`, `skill/owasp-critical-audit`, `docs/agents-md`.

---

## Step 3 — Propose CHANGELOG (validation required)

Read `CHANGELOG.md` at root. Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) — see [references/changelog-format.md](references/changelog-format.md).

1. Draft entries under `## [Unreleased]` (create the section if absent).
2. **Present the proposal to the user** — do not modify the file until they have approved (keyword, correction, or "OK").
3. After validation only: apply the entries. Be factual, one bullet per notable change.

---

## Step 4 — Atomic commits

### Git rules (strict)

- **Never** modify `git config`
- **Never** commit without an explicit user request or validation
- **Never** `push --force` on `main` / `master`
- **Never** `--no-verify` unless explicitly requested
- **Never** commit: `.env`, secrets, `storage/app/private/`, Laravel caches, `node_modules/`, unversioned build artifacts

### Commit plan

1. Present the plan (one commit = one logical, testable or reviewable unit).
2. Await validation.
3. For each commit:
   - `git add` only the batch files
   - [Conventional Commits](https://www.conventionalcommits.org/) message: `type(scope): imperative summary`
   - Optional body: the *why*, not the line-by-line *what*

```bash
git add path/file1 path/file2
git commit -m "$(cat <<'EOF'
feat(blog): add published_at scopes to BlogPost model

EOF
)"
```

4. Recommended order: code/test commits first, `docs(changelog): …` commit last (after Step 3 validation).
5. `git status` after each commit to verify.

### Atomic splitting

| Do | Don't |
|----|-------|
| One commit per independent fix | A single "misc fixes" commit |
| Separate refactor from behavior | Mix unrelated feat + fix |
| Tests with the code they cover | CHANGELOG before user validation |

---

## Step 5 — Propose the Pull Request

After validated commits:

1. Verify: `git log main..HEAD --oneline` and `git diff main...HEAD --stat`
2. **Propose** the push and PR — execute only after agreement.

```bash
git push -u origin HEAD
```

### GitHub (remote `origin`)

If `gh` is available:

```bash
gh pr create --title "type(scope): short title" --body "$(cat <<'EOF'
## Summary
- …

## Test plan
- [ ] vendor/bin/pest --parallel
- [ ] …

EOF
)" --base main
```

Otherwise, provide the compare URL:

`https://github.com/happytodev/blogr/compare/main...BRANCH`

### Suggested PR body

```markdown
## Summary
- …

## CHANGELOG
- …

## Test plan
- [ ] `vendor/bin/pest --parallel`
- [ ] …
```

Return the URL of the created PR or the "New Pull Request" link.

---

## Creating GitHub issues

When the user asks to open a bug or issue:

```bash
gh issue create --label bug --title "Bug: short description" --body "..."
gh issue comment <id> --body "Fixed in commit HASH"
gh issue close <id>
```

---

## Git authentication

If `git push` fails with `HTTP Basic: Access denied`:

```bash
gh auth status
# If gh is authenticated, use the token in the remote URL:
git remote set-url origin "https://USER:TOKEN@github.com/happytodev/blogr.git"
git push ...
git remote set-url origin "https://github.com/happytodev/blogr.git"
```

---

## Pitfalls (this repository)

- Do not version locally generated `storage/` directories (often untracked after dev)
- Commit messages and CHANGELOG entries are in **English** for this project
- The version link at the bottom of the CHANGELOG points to GitHub: `https://github.com/happytodev/blogr/releases/tag/vX.Y.Z`
- Branch `skill/` for OpenCode agent skills in `.opencode/skills/`

## Resources

- [references/changelog-format.md](references/changelog-format.md) — project Keep a Changelog format
- [references/branch-and-commits.md](references/branch-and-commits.md) — branch and commit naming conventions
