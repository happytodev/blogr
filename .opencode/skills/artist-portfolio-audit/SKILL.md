# artist-portfolio-audit

Analyse des écarts entre Blogr et un site portfolio d'illustrateur (cf. Milanote + screenshots).

## Référence visuelle

Le site cible est un portfolio d'illustrateur avec :
- **Home** : carousel hero + bio artiste + liens sociaux + couleurs personnalisées (#273338, #2B5748, #618764, #9CB080)
- **Portfolio** : grille d'images horizontales, hover B/N, click plein écran, filtres par catégorie
- **Commissions** : carrousels par type, hover avec prix/infos
- **Contact** : formulaire avec illustration

## Codebase Blogr — ce qui existe

| Entité | Tables | Champs clés |
|--------|--------|-------------|
| Blog posts | `blog_posts` + `blog_post_translations` | title, slug, content, photo, category_id |
| Categories | `categories` + `category_translations` | name, slug |
| Tags | `tags` + `tag_translations` | name, slug |
| CMS pages | `cms_pages` + `cms_page_translations` | blocks (JSON), template, is_homepage |
| Users | `users` + `user_translations` | name, avatar, bio (JSON) |
| Blog series | `blog_series` + `blog_series_translations` | title, description, photo, is_featured |

### Blocks CMS existants

`hero`, `features`, `testimonials`, `cta`, **`gallery`** (grid/masonry/bento, lightbox, hover zoom), `faq`, `team`, `pricing` (plans statiques), `content`, `blog_posts`, `stats`, `timeline`, `video`, `newsletter`, `map`, **`contact_form`**, `wave-separator`, `blog-title`

### Ce qui existe déjà

- ✔ **Contact form** : block + template + contrôleur + tests + mail
- ✔ **Gallery avec lightbox** : block grid/masonry/bento
- ✔ **Traductions** : architecture translation-first complète
- ✔ **Thème** : couleurs, dark mode, presets, logo, navigation
- ✔ **SEO / sitemap / RSS / locales**

### Ce qui manque

| Fonctionnalité | Détail |
|----------------|--------|
| **Carousel / slider** | Aucun block carousel n'existe |
| **Gallery avancée** | Pas de filtres catégorie, pas de layout horizontal, pas d'effet B/N hover |
| **Pricing commissions** | Block `pricing` statique — pas de prix par type, pas de statut |
| **Liens sociaux réutilisables** | Uniquement dans le footer actuellement |
| **Block bio artiste** | `author-bio` existe mais lié aux articles uniquement |

## Architecture technique

- **Moteur de thème** : config `blogr.php > ui.theme`, variables CSS injectées dans `layouts/blog.blade.php`
- **Blocks** : enum `CmsBlockType` → builder `CmsBlockBuilder.php` → `blocks-renderer.blade.php`
- **Traductions** : tables de traduction séparées, pivot via translations
- **Tests** : Pest PHP 4, Feature tests dans `tests/Feature/`, base classes `TestCase` / `LocalizedTestCase` / `CmsTestCase`
- **Configuration** : `config/blogr.php` (attention aux doublons de clés)

## Plan d'implémentation

1. Composant `<x-blogr::social-links>` réutilisable
2. Block Carousel Hero (slides avec image + titre opt + sous-titre opt + CTA opt)
3. Block Gallery amélioré (display_mode: grid/masonry/bento/horizontal/filtered, hover B/N, catégories)
4. Block PricingCommissions (image + titre + prix + statut)
5. Block ArtistBio (avatar + bio + liens sociaux)

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `src/Enums/CmsBlockType.php` | Enum des types de blocks |
| `src/Filament/Resources/CmsPages/CmsBlockBuilder.php` | Builder admin des blocks |
| `resources/views/components/blocks/{type}.blade.php` | Vue Blade de chaque block |
| `resources/views/components/blocks-renderer.blade.php` | Renderer qui boucle les blocks |
| `resources/views/components/background-wrapper.blade.php` | Wrapper fond/couleur |
