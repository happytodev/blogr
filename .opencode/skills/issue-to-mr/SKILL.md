---
name: issue-to-mr
description: >-
  Orchestrates the full cycle from a need or bug to pull request:
  GitHub issue creation, code analysis, development generation,
  then calls the git-changelog workflow for atomic commits and PR.
  Use when the user reports a bug, requests a feature, or describes a need
  without specifying the implementation steps.
compatibility: >-
  OpenCode agent with git, shell, and gh CLI access. Remote GitHub (origin).
  Requires the git-changelog-workflow skill in .opencode/skills/.
metadata:
  author: happytodev
  version: "1.0"
  dependencies: git-changelog-workflow
---

# Workflow issue → PR

Full cycle: issue → analysis → development → commit → PR.

**Never** push, commit, or open a PR without explicit user validation
at the indicated steps.

## When to use

- User reports a bug
- User requests a new feature
- User describes a need without knowing where to start
- User asks "can you handle this issue?"

## Checklist

```
Progress:
- [ ] 1. Understand the need and ask clarifying questions if ambiguous
- [ ] 2. Create the GitHub issue (title + description)
- [ ] 3. Analyze existing code (search, architecture, tests)
- [ ] 4. Generate / develop the fix or feature
- [ ] 5. **Present modified files to the user for validation** ← mandatory human validation
- [ ] 6. Run tests (vendor/bin/pest --parallel)
- [ ] 7. **Present PR proposal to the user for validation** ← mandatory human validation
- [ ] 8. Call the git-changelog workflow (branch, commits, PR)
- [ ] 9. Close the issue with reference to commit/PR
```

---

## Step 1 — Needs analysis

Before any action, clarify with the user if necessary:

| Question | For |
|----------|-----|
| What is the expected vs actual behavior? | Bug |
| Are there reproduction steps? | Bug |
| Which files does the user think are involved? | Bug / Feat |
| Is there already an open issue on the topic? | Both |

Determine the type and branch:

| Type | Branch prefix | Issue label |
|------|---------------|-------------|
| New feature | `feat/` | `feature` |
| Bug fix | `fix/` | `bug` |
| Documentation | `docs/` | `documentation` |
| Refactoring | `refactor/` | `refactor` |
| Security | `fix/` | `security` |

## Step 2 — Create the GitHub issue

```bash
gh issue create \
  --label "$LABEL" \
  --title "type: short description" \
  --body "$DESCRIPTION"
```

The description must include:
- **Context**: what was reported
- **Reproduction** (bug): steps, expected, actual
- **Clues** (optional): likely files, hypotheses
- **Acceptance criteria**: what validates the resolution

## Step 3 — Code analysis

Explore the codebase to understand where the change belongs:

```bash
# Search for relevant classes/models
rg -n "ClassName|method_name" src/ --type php

# Search for related routes (registered in BlogrServiceProvider)
rg -n "pattern" src/BlogrServiceProvider.php

# Search for existing tests
rg -n "pattern" tests/ --type php

# View the relevant directory structure
ls src/Filament/Resources/
```

**Deliverable:** summary of files to modify with estimated scope.

## Step 4 — Development

1. Start from up-to-date `main`: `git checkout main && git pull origin main`
2. Ensure `vendor/bin/pest --parallel` passes before any modification
3. Make changes following project conventions (see AGENTS.md)
4. For bugs, write a test that reproduces the error before fixing

### Quick conventions (see AGENTS.md)

- **Translation-First**: main tables hold only IDs/timestamps; translation tables hold title, slug, content, SEO. Use `Model::with('translations')`.
- **Filament v4**: use `Filament\Schemas\Schema` (not `Filament\Forms`). Sections from `Filament\Schemas\Components\Section`.
- **Form delegation**: `BlogPostForm::configure($schema)`, `BlogPostTable::configure($table)`.
- **Translations UI**: `Repeater::make('translations')->relationship()` — never the `Tabs\Tab` pattern.
- **Markdown rendering**: use `MarkdownHelper` class, never raw `Str::markdown()`.
- **Routes**: registered in `BlogrServiceProvider::packageBooted()`, not in routes files. Use `SetLocale` middleware for localization.
- **Config**: read from `config('blogr.*')`. Be aware of duplicate keys in `config/blogr.php`.
- **Validation**: always `$request->validated()`, never trust client input.

## Step 4b — Mandatory human validation

Before any commit, **present to the user** the modified files for validation:

```bash
git diff --stat
git diff --name-only
```

**Message:** "Here are the files I modified. I can show you the details of each file if you want. Do you validate these changes before I commit?"

**Do not commit under any circumstances** without explicit user validation (keyword: "OK", "valid", "go", "yes").

## Step 5 — Tests

```bash
vendor/bin/pest --parallel
```

**Criteria:**
- No regressions (all existing tests pass)
- If bug: at least one test that reproduces the case and passes after fix
- If feature: tests covering the new behavior
- Feature tests must declare `uses()` individually (see AGENTS.md)

## Step 6 — Mandatory PR validation

Before creating the PR, **present to the user**:

```markdown
## Pull request proposal

**Branch:** `feat/my-feature`
**Commits:**
- `hash1` — type(scope): message
- `hash2` — type(scope): message

### Summary of changes
- [List of modified files with functional impact]

Do you want me to push and create the PR?
```

**Do not push or create the PR** without explicit user validation.

## Step 7 — Workflow git-changelog

Call the `git-changelog-workflow` skill to:

1. Create the dedicated branch (from up-to-date `main`)
2. Propose CHANGELOG entries
3. Atomic commits (Conventional Commits, English)
4. Push + create the GitHub PR

```bash
# After commit plan validation:
git push -u origin HEAD
gh pr create --base main --head "$BRANCH" --title "type(scope): summary" --body "..."
```

## Step 7 — Close the issue

```bash
gh issue comment $ISSUE_ID --body "Fixed in PR #$PR_NUMBER ($HASH)"
gh issue close $ISSUE_ID
```

---

## Full example (bug)

```markdown
User: "the save button on blog post form does nothing"

1. Issue created: `gh issue create --label bug --title "fix: blog post save button has no effect" --body "…"`
2. Analysis: `src/Filament/Resources/BlogPosts/BlogPostForm.php` — the `Repeater` translations field is missing `->relationship('translations')`
3. Dev: add missing `->relationship('translations')` call + test verifying translations persist
4. Test: `vendor/bin/pest --parallel` → 142 passed
5. git-changelog: branch `fix/save-button`, commit, PR #12
6. Issue closed after merge
```

## Full example (feature)

```markdown
User: "add a view counter on blog posts"

1. Issue: label `feature`
2. Analysis: modify `BlogPost` model (add `views_count` column to main table), `BlogController::show` (increment), translation not needed
3. Dev: migration, model, controller, view, test
4. Test + PR
5. Issue closed
```

## Commit policy

**This project uses the `release-manager` workflow.** Never commit, amend, tag, or push unless the user explicitly loads the `release-manager` skill and requests a release. All commits must go through the `release-manager` workflow.
