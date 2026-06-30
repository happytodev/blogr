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
- [ ] 0. Batch analysis — detect multi-domain changes, propose group or split
- [ ] 1. Analyze Git state and classify changes
- [ ] 2. Verify / create the dedicated branch
- [ ] 3. Propose CHANGELOG entries (await validation)
- [ ] 4. Atomic commits (await validation of the plan)
- [ ] 5. Propose the Pull Request (await validation)
- [ ] 6. Merge the Pull Request (await validation)
- [ ] 7. Post-merge cleanup — switch to main, pull, delete branch
```

---

## Step 0 — Multi-change batch analysis

Run **in parallel** to detect whether the working tree contains changes from multiple domains:

```bash
git diff --stat main 2>/dev/null || git status --short
git log main..HEAD --oneline 2>/dev/null
git branch --show-current
```

Classify all modified files by domain:

| File pattern | Domain |
|-------------|--------|
| `src/**`, `resources/views/**`, `tests/**` | `feat` or `fix` |
| `*.md`, `docs/**` | `docs` |
| `.opencode/skills/**`, `AGENTS.md` | `skill` |
| `composer.json`, `composer.lock`, `package.json`, `package-lock.json` | `chore(deps)` |

If changes span **exactly one domain**, skip this step and proceed to Step 1.

If changes span **multiple domains** (e.g. `feat` + `fix` + `skill`), present the user with two options:

```markdown
## Batch analysis

Changes detected across {{N}} domains:

| Domain | Files |
|--------|-------|
| feat   | ... |
| fix    | ... |
| skill  | ... |

➡  **Option A (recommended)**: Group all into a single branch
   Atomic commits per domain, one PR. Branch name based on the
   dominant domain (e.g. `feat/artist-portfolio-blocks`).

➡  **Option B**: Split into separate branches per domain
   (default if changes are unrelated).

Which do you prefer?
```

**Option A — Grouped branch**: Create one branch named after the dominant domain.
Commits are ordered: `fix` commits first, then `feat`, then `docs/skill` last.
This keeps the PR reviewable while avoiding branch proliferation.

**Option B — Split**: The original behavior. Each domain gets its own branch
and PR. Continue to Step 1 for each domain separately.

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

If multiple types coexist and Step 0 was **skipped** (single domain), proceed normally.
If Step 0 already resolved a multi-domain batch, use the agreed grouping strategy — do not re-propose splitting.

---

## Step 2 — Dedicated branch

1. Branch from up-to-date `main` (`git fetch origin` if network available).
2. Name: `{type}/{description-kebab-case}` — max ~50 characters, no `--`.
3. **One topic per branch**: no mixing unrelated feat + fix **unless Step 0 grouped them**.

```bash
git checkout main
git pull origin main   # if possible
git checkout -b feat/short-name
```

If the user is already working on the correct branch, do not recreate unnecessarily.

**Repository examples:** `fix/restore-blog-admin-routes`, `skill/owasp-critical-audit`, `docs/agents-md`.

**For a batch branch** (Step 0 Option A): name the branch after the dominant domain.
Example: `feat/artist-portfolio-blocks` may contain fix, feat and skill commits
as atomic units within the same branch.

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
   - **Body conventions** (required when applicable):
     - `fix:` commits MUST include `Regression test: #<issue_number>` in the body
     - `feat:` commits MUST include `Feature test: <description>` in the body
   - Additional body context: the *why*, not the line-by-line *what*

```bash
git add path/file1 path/file2
git commit -m "$(cat <<'EOF'
fix(blog): restore save button on post form

Regression test: #42
EOF
)"
```

4. Recommended order: code/test commits first, `docs(changelog): …` commit last (after Step 3 validation).
   For batch branches: `fix` commits first, then `feat`, then `docs/skill` last.
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

## Step 6 — Merge the Pull Request (optional)

After the PR is created, propose to merge it immediately:

```bash
gh pr merge $PR_NUMBER --squash --subject "type(scope): summary"
```

If `gh` is not available, provide the GitHub merge URL:
`https://github.com/happytodev/blogr/pull/$PR_NUMBER`

This step is **optional** — PRs can also be merged later as part of a
release batch (see `release-manager`). If the user plans to accumulate
multiple PRs before releasing, skip this step.

**Do not merge without explicit user validation.**

## Step 7 — Post-merge cleanup (required after merge)

After the PR is merged, switch back to `main` and update the local repository:

```bash
# Fetch the latest main with the merged changes
git fetch origin main

# Switch back to main
git checkout main

# Merge or rebase to catch up with the remote
git pull origin main

# Delete the local feature branch (optional but recommended)
git branch -D $BRANCH_NAME
```

**Important:** If the branch has additional commits pushed *after* the PR was merged
(which can happen when fixes are pushed post-merge), the branch will still be ahead
of `main`. Those commits should be delivered via a **new PR** rather than force-pushing
the already-merged branch.

If the working tree has uncommitted changes that would be overwritten by the checkout,
stash them first:

```bash
git stash
git checkout main
git pull origin main
git stash pop
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
