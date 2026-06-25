---
name: hotfix-cherry-pick
description: >-
  Apply a fix from a feature branch already merged to main
  when main has advanced. Use `git cherry-pick` to port
  a fix commit without rebasing the entire branch.
compatibility: Git, shell access, main already ahead of the fix branch.
metadata:
  author: happytodev
  version: "1.0"
---

# Post-merge fix via cherry-pick

When a fix is committed on a feature branch after its merge into `main`, the fix is not in `main`. Use cherry-pick to port it without rebasing the entire branch.

## When to use

- A branch has been merged into `main`
- A bug is discovered and fixed on the feature branch
- `main` has advanced (new merges, commits) since the initial merge
- The fix only concerns one or two atomic commits

## Workflow

```
Progress:
- [ ] 1. Identify the fix commit(s)
- [ ] 2. Checkout updated main
- [ ] 3. Cherry-pick the commit(s)
- [ ] 4. Resolve conflicts if necessary
- [ ] 5. Test + push
- [ ] 6. Optional: merge the updated feature branch (for alignment)
```

## Step 1 — Identify the fix

```bash
git log --all --oneline --grep="fix("
git log feat/my-branch --oneline -5
```

## Step 2 — Cherry-pick

```bash
git checkout main
git pull origin main
git cherry-pick <commit-hash>

# In case of conflict:
git status                              # view conflicting files
# Resolve conflicts manually
git add <resolved-files>
git cherry-pick --continue
```

## Step 3 — Typical conflicts

See the [merge-conflict-resolver](../merge-conflict-resolver/SKILL.md) skill for resolution patterns.

## Step 4 — Verification

```bash
vendor/bin/pest --parallel
git push origin main
```

## Step 5 — Branch alignment (optional)

```bash
git checkout feat/my-branch
git merge main
git push origin feat/my-branch
```

## Pitfalls (this repo)

- Do not cherry-pick merge commits (`Merge branch …`) — only content commits
- Fix commits are often in the `CHANGELOG` — do not forget to document them
- Verify the commit scope is correct (e.g. `fix(comments):` not `feat(comments):`)
