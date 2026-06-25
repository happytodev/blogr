# Dependency Update Policy

This document defines the rules applied by the `safe-dependency-update` skill.

---

## 1. 72-Hour Rule

### Principle

Any package update to a version published **less than 72 hours ago** is **excluded by default**, unless that version fixes a known CVE.

### Justification

Prevention of **supply-chain** attacks: an attacker can publish a malicious package that will be quickly removed. Waiting 72 hours allows the community to detect and report the danger.

### Exception

A version that fixes a **confirmed CVE** (detected by `composer audit`) is **prioritized** even if < 72h. Security takes precedence over the waiting period.

---

## 2. Update Typology

| Type | Definition | Default Action | Risk |
|------|-----------|----------------|------|
| **Patch** (x.y.**Z**) | Bug fix only, 100% backward-compatible API | Automatically proposed | Near zero |
| **Minor** (x.**Y**.0) | New feature, backward-compatible API | Proposed with changelog analysis | Low |
| **Major** (**X**.0.0) | Breaking change, modified API | Proposed with breaking changes analysis + manual test recommended | High |

### Decision by Type

| Type | Proposed alone? | Human validation required? | Additional condition |
|------|---------------|---------------------------|---------------------|
| Patch | Yes | No (proposed, applied if global check OK) | Age ≥ 72h or CVE |
| Minor | Yes | Yes | Age ≥ 72h or CVE |
| Major | Yes | **Yes, explicit** | Breaking changes analysis provided |

---

## 3. CVE / Vulnerability Management

### Detection

`composer audit` in step 1 of the workflow.

### Priority

- A known CVE = **absolute priority**, overrides all other criteria
- Even if < 72h → update proposed and recommended
- If multiple CVEs → sort by severity (Critical > High > Medium > Low)

### Behavior

- Packages with CVEs are moved to the top of the table
- The Risk column becomes **critical** or **high**
- The user is informed: "This security fix is recommended without delay"

---

## 4. Rollback Rule

### Trigger

Failure of `vendor/bin/pest --parallel` after applying updates.

### Procedure

```bash
git checkout -- composer.lock package-lock.json composer.json package.json
```

### Report

Inform the user of the failure and rollback. Offer to analyze the conflict and re-apply a compatible version.

### Edge Cases

- If only one package fails and the others pass: propose to isolate the problematic update and keep the others
- If the tests were already failing before the update: do not roll back (the problem pre-exists)

---

## 5. Limits and Edge Cases

| Situation | Rule |
|-----------|------|
| `--with-all-dependencies` | Do not use unless necessary (it updates too many things) |
| `npm audit` alert | Convert to a priority proposal like `composer audit` |
| Abandoned package | Report to the user and propose a replacement |
| Incompatible PHP constraint | Do not propose the version (check `require.php` in `composer.json`) |
| `platform-check` | Let `composer` handle it, do not disable |
| Lock already modified | Ask what to do before continuing |
| Dev dependencies (`require-dev`) | Included in the scan but marked as such |

---

## 6. Notwithstanding

These rules are **default safeguards**, not absolute prohibitions. The user can **always** request a specific update that violates a rule (e.g., "I want tailwindcss v4 now") → the skill executes without filtering, but notes the exception in the report.
