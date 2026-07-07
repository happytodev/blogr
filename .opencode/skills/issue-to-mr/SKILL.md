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
- [ ] 7. **Comment on issue** with status update if iteration is needed
- [ ] 8. Call `git-changelog-workflow` (branch, commits, PR)
- [ ] 9. **Close the issue** with reference to commit/PR
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

## Step 4 — Development: TDD (RED → GREEN)

1. Start from up-to-date `main`: `git checkout main && git pull origin main`
2. Ensure `vendor/bin/pest --parallel` passes before any modification

### Step 4a — RED phase (mandatory before any implementation)

1. Write the **test** that proves the bug exists or validates the feature behaviour
2. Naming convention:
   - **Bug fix**: `regression_<issue_number>_<description>` (e.g. `regression_42_save_button_does_nothing`)
   - **Feature**: `feature_<description>` (e.g. `feature_view_counter_increments_on_show`)
3. Run the test **alone** to confirm it fails:
   ```bash
   vendor/bin/pest --filter <test_name>
   ```
4. A passing test at this stage means the test does not adequately detect the problem — rewrite it

### Step 4b — GREEN phase (implementation)

1. Implement the fix or feature following project conventions (see AGENTS.md)
2. Run the test again to confirm it passes:
   ```bash
   vendor/bin/pest --filter <test_name>
   ```
3. **Anti-false-positive gate**: Comment out the new implementation code and re-run the test — it must **fail** again. If it still passes, the test is a false positive; rewrite it before proceeding
4. Run the full suite to confirm no regressions:
   ```bash
   vendor/bin/pest --parallel
   ```

### Quick conventions (see AGENTS.md)

- **Translation-First**: main tables hold only IDs/timestamps; translation tables hold title, slug, content, SEO. Use `Model::with('translations')`.
- **Filament v4**: use `Filament\Schemas\Schema` (not `Filament\Forms`). Sections from `Filament\Schemas\Components\Section`.
- **Form delegation**: `BlogPostForm::configure($schema)`, `BlogPostTable::configure($table)`.
- **Translations UI**: `Repeater::make('translations')->relationship()` — never the `Tabs\Tab` pattern.
- **Markdown rendering**: use `MarkdownHelper` class, never raw `Str::markdown()`.
- **Routes**: registered in `BlogrServiceProvider::packageBooted()`, not in routes files. Use `SetLocale` middleware for localization.
- **Config**: read from `config('blogr.*')`. Be aware of duplicate keys in `config/blogr.php`.
- **Validation**: always `$request->validated()`, never trust client input.

## Step 4c — Mandatory human validation

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
- If bug: at least one `regression_<issue_number>_<description>` test that reproduces the case and passes after fix
- If feature: `feature_<description>` tests covering the expected behaviour
- Feature tests must declare `uses()` individually (see AGENTS.md)
- **Anti-regression gate**: The regression test becomes part of the permanent test suite. Any future re-introduction of the bug will be caught by `vendor/bin/pest --parallel` on CI

## Step 7 — Comment on issue during iteration

If the fix requires multiple iterations or the user asks for changes,
update the issue with a status comment:

```bash
gh issue comment $ISSUE_ID --body "Status update: $MESSAGE"
```

This keeps the issue thread readable and avoids stale issues.

## Step 8 — Call git-changelog-workflow

Delegate all commit, branch, CHANGELOG and PR creation to `git-changelog-workflow`:

```
Load the git-changelog-workflow skill (branch → CHANGELOG → atomic commits → PR).
```

This skill handles:
1. Batch analysis (multi-domain grouping)
2. Dedicated branch from up-to-date `main`
3. CHANGELOG proposal (user validation required)
4. Atomic commits (Conventional Commits)
5. Push + PR creation

**Do not create the PR manually** — let `git-changelog-workflow` handle it.

## Step 9 — Close the issue

The issue is auto-closed on merge via `Closes #<issue_number>` in the PR
description (see git-changelog-workflow Step 5). No manual close is needed.

If the PR was created without `Closes #<issue_number>`, close manually:

```bash
gh issue close $ISSUE_ID --comment "Fixed in $HASH"
```

---

## Full examples

### Bug

```markdown
User: "the save button on blog post form does nothing"

1. Issue created: `gh issue create --label bug --title "fix: blog post save button has no effect" --body "…"`
2. Analysis: `src/Filament/Resources/BlogPosts/BlogPostForm.php` — the `Repeater` translations field is missing `->relationship('translations')`
3. RED phase: write `regression_42_save_button_has_no_effect` test → `vendor/bin/pest --filter regression_42` → fails (RED ✓)
4. GREEN phase: add missing `->relationship('translations')` → test passes (GREEN ✓)
5. Anti-false-positive: comment out `->relationship()`, test fails again (regression detected)
6. Full suite: `vendor/bin/pest --parallel` → 142 passed
7. git-changelog: branch `fix/save-button`, commit with body `Regression test: #42`, PR #12
8. Issue closed: `gh issue close 42 --comment "Fixed in PR #12 (abc123)"`
```

### Feature

```markdown
User: "add a view counter on blog posts"

1. Issue: label `feature`
2. Analysis: modify `BlogPost` model, `BlogController::show`, migration
3. RED phase: write `feature_view_counter_increments_on_show` test → fails (RED ✓)
4. GREEN phase: migration, model, controller, view → test passes (GREEN ✓)
5. Full suite: `vendor/bin/pest --parallel` → all pass
6. git-changelog: branch `feat/view-counter`, commit with body `Feature test: view_counter_increments_on_show`, PR #13
7. Issue closed
```

## Commit policy

**This project uses the `release-manager` workflow.** Never commit, amend, tag, or push unless the user explicitly loads the `release-manager` skill and requests a release. All commits must go through the `release-manager` workflow.
