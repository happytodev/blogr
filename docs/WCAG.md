# WCAG Accessibility Implementation

**Target level:** WCAG 2.2 Level AA (Level A achieved — partial)

## Conformance status

| Level | Status | Details |
|-------|--------|---------|
| A | ✅ Partial (5/10 criteria) | Critical barriers resolved |
| AA | 🔄 In progress | 6 criteria remaining |
| AAA | ❌ Not targeted | Not required by regulations |

## Implemented (Sprint 0 — completed)

| WCAG SC | Description | Component | Issue |
|---------|-------------|-----------|-------|
| 2.4.1 (A) | Skip-to-content link — bypass navigation blocks | `layouts/blog.blade.php` | #285 |
| 3.3.2 (A) | Form label on newsletter email input | `newsletter.blade.php` | #285 |
| 2.1.1 + 4.1.2 (A) | Keyboard-accessible gallery images (`tabindex`, `role`, keydown) | `gallery.blade.php` | #285 |
| 2.1.1 + 2.2.2 (A) | Keyboard-navigable carousel with focus-based autoplay pause | `carousel.blade.php` | #285 |

## In progress (Sprints 1–5)

### Sprint 1 — Quick wins

| # | WCAG SC | Description | Component |
|---|---------|-------------|-----------|
| 291 | 2.4.4 (A) | `aria-label` on "Read More" links | `blog-post-card.blade.php` |
| 295 | 4.1.2 (A) | `aria-pressed` on theme switcher buttons | `navigation.blade.php` |
| 287 | 1.3.5 (AA) | `autocomplete` attributes on forms | `contact_form.blade.php`, `newsletter.blade.php` |

### Sprint 2 — Keyboard navigation (A)

| # | WCAG SC | Description | Component |
|---|---------|-------------|-----------|
| 294 | 2.1.1 (A) | Escape key + focus management on language switcher | `language-switcher.blade.php` |
| 292 | 2.1.1 (A) | Full keyboard support + ARIA on mega menu | `navigation.blade.php` |

### Sprint 3 — Dynamic content (AA)

| # | WCAG SC | Description | Component |
|---|---------|-------------|-----------|
| 290 | 4.1.3 (AA) | `role="status"` / `aria-live` on dynamic messages | `contact_form.blade.php`, `newsletter.blade.php`, code copy script |
| 293 | 2.2.2 (A) | Pause/Play button + `prefers-reduced-motion` on carousel | `carousel.blade.php` |

### Sprint 4 — Visual / design (AA)

| # | WCAG SC | Description | Component |
|---|---------|-------------|-----------|
| 288 | 2.4.11 (AA) | `scroll-margin-top` to prevent sticky nav obscuring focus | `layouts/blog.blade.php` |
| 289 | 2.5.8 (AA) | Touch targets ≥ 24×24px for icons and tags | `social-links.blade.php` |

### Sprint 5 — Color system (AA)

| # | WCAG SC | Description | Component |
|---|---------|-------------|-----------|
| 286 | 1.4.3 (AA) | Contrast ratio validation (4.5:1) for user-configurable colors | `ConfigHelper`, `BlogrSettings` |

## Architecture notes

### Skip link
- First focusable element after `<body>`
- Visually hidden via `sr-only`, appears on `:focus` via `focus:not-sr-only`
- Targets `<main id="main-content">`

### Gallery keyboard access
- All 5 layout modes (grid, horizontal, filtered, masonry, bento) have clickable `<div>` elements with `tabindex="0"`, `role="button"`, `@keydown.enter`, `@keydown.space.prevent`
- Lightbox has Escape, arrow keys, and aria-labels on prev/next/close buttons

### Carousel
- Arrow keys (`@keydown.arrow-right/.arrow-left`) navigate slides
- Focus-based autoplay pause/resume (`@focusin`/`@focusout`)
- All navigation buttons have `aria-label`
- Dot indicators have `aria-label="Go to slide N"`

### Newsletter form
- Email input has `<label for="newsletter-email" class="sr-only">`
- Input has `id="newsletter-email"` matching the `for` attribute

## Testing

Accessibility tests live in `tests/Feature/Accessibility/`:

```bash
vendor/bin/pest --filter "feature_skip_to_content_link|feature_newsletter_form|feature_gallery.*keyboard|feature_carousel"
```

Each fix follows TDD: RED (test fails before fix) → GREEN (test passes after) → anti-false-positive gate (test fails when implementation is removed).

## References

- [WCAG 2.2 Specification](https://www.w3.org/TR/WCAG22/)
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- GitHub issues: [#285](https://github.com/happytodev/blogr/issues/285) (critical A fixes), [#286–#295](https://github.com/happytodev/blogr/issues?q=is%3Aissue+is%3Aopen+label%3Aaccessibility) (remaining)
