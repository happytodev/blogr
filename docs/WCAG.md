# WCAG Accessibility Implementation

**Target level:** WCAG 2.2 Level AA (Level A achieved — partial)

## Conformance status

| Level | Status | Details |
|-------|--------|---------|
| A | ✅ Complete | All Level A criteria satisfied |
| AA | ✅ Partial (AA target reached, user-configurable colors may affect runtime contrast) | All implementable criteria addressed |
| AAA | ❌ Not targeted | Not required by regulations |

## Implemented (Sprint 0 — completed)

| WCAG SC | Description | Component | Issue |
|---------|-------------|-----------|-------|
| 2.4.1 (A) | Skip-to-content link — bypass navigation blocks | `layouts/blog.blade.php` | #285 |
| 3.3.2 (A) | Form label on newsletter email input | `newsletter.blade.php` | #285 |
| 2.1.1 + 4.1.2 (A) | Keyboard-accessible gallery images (`tabindex`, `role`, keydown) | `gallery.blade.php` | #285 |
| 2.1.1 + 2.2.2 (A) | Keyboard-navigable carousel with focus-based autoplay pause | `carousel.blade.php` | #285 |

## Completed (all sprints)

| Sprint | Issues | Criteria |
|--------|--------|----------|
| 0 — Critical A fixes | #285 | 2.4.1, 3.3.2, 2.1.1, 4.1.2 |
| 1 — Quick wins | #291, #295, #287 | 2.4.4, 4.1.2, 1.3.5 |
| 2 — Keyboard navigation | #294, #292 | 2.1.1 |
| 3 — Dynamic content | #290, #293 | 4.1.3, 2.2.2 |
| 4 — Visual / design | #288, #289 | 2.4.11, 2.5.8 |
| 5 — Color system | #286 | 1.4.3 |
| 6 — AA completion | #299 | 2.5.3, 1.4.1, 2.4.7, 1.4.11, 1.4.13 |

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
