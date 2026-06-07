# Blogr Installation Guide

A step-by-step guide to install Blogr from scratch — no prior setup required.

> **Prerequisites:** PHP 8.3+, [Composer](https://getcomposer.org/), Node.js 18+, npm

---

## 1. Create a Laravel project

```bash
composer create-project laravel/laravel my-blog
cd my-blog
```

Or via the Laravel installer:

```bash
laravel new my-blog
cd my-blog
```

---

## 2. Install FilamentPHP

```bash
composer require filament/filament
php artisan filament:install --panels
```

Create your first admin user:

```bash
php artisan make:filament-user
```

Keep the email/password handy — you'll need them to log in.

---

## 3. Install Blogr

```bash
composer require happytodev/blogr
```

Run the automated installer:

```bash
php artisan blogr:install
```

The installer will ask you a few questions:

- **Enable CMS?** — `Yes` (recommended) to create static pages like About, Contact
- **Set as homepage?** — Choose `blog` to show posts at `/` or `cms` to show a custom page
- **Publish Spatie Permission config?** — `Yes` if you need role customization
- **Install Alpine.js?** — `Yes` (required for theme switcher)
- **Configure Tailwind CSS v4 dark mode?** — `Yes` (required)
- **Install tutorial content?** — `Yes` to get sample posts
- **Install demo CMS pages?** — `Yes` to get sample pages
- **Install npm packages?** — `Yes`
- **Build assets now?** — `Yes`

The installer handles everything automatically:
- Publishes config, migrations, views, and assets
- Runs database migrations
- Creates the storage symlink
- Configures your `User` model with Spatie Permissions & FilamentUser
- Creates roles (Admin, Writer) and permissions
- Adds Alpine.js to `resources/js/app.js`
- Configures Tailwind dark mode in `resources/css/app.css`
- Installs npm dependencies (`alpinejs`, `@tailwindcss/typography`)
- Builds frontend assets
- Installs tutorial content and demo CMS pages (if opted in)
- Configures your `AdminPanelProvider` with `BlogrPlugin`

---

## 4. Verify the Admin Panel

Open your browser and navigate to `http://my-blog.test/admin` (or your local dev URL).

Log in with the credentials from step 2.

You should see the Blogr dashboard with widgets showing post statistics, recent posts, and scheduled content.

---

## 5. Create your first blog post

1. Go to **Blog Posts → New Post** in the admin sidebar
2. Write a title and content (Markdown supported)
3. Add a category and tags
4. Set a featured image (drag & drop)
5. Configure SEO meta fields (optional)
6. Click **Publish**

Visit `http://my-blog.test/blog` (or `/` if blog is set as homepage) to see your post live.

---

## 6. Configure settings

Go to **Blogr Settings** in the admin panel to customize:

- **General** — Blog name, description, posts per page
- **SEO** — Site name, default meta, Open Graph image
- **Navigation** — Menu links, sticky nav, logo
- **Appearance** — Colors, card styles, dark mode
- **Footer** — Social links, copyright text
- **Backup** — Export/import your data

---

## 7. Optional: Enable CMS pages

If you skipped CMS during installation, enable it in `config/blogr.php`:

```php
'cms' => [
    'enabled' => true,
    'prefix' => '', // empty for /about, or 'page' for /page/about
],
```

Then create pages in **CMS → Pages CMS** in the admin panel.

Available templates: Default, Landing, About, Contact, Pricing, FAQ, Custom.

---

## 8. Optional: Enable multilingual

Edit `config/blogr.php`:

```php
'locales' => [
    'enabled' => true,
    'default' => 'en',
    'available' => ['en', 'fr', 'es', 'de', 'pl'],
],
```

With locales enabled, your blog posts and CMS pages get per-language URLs:
- `/en/blog/my-post`
- `/fr/blog/mon-article`
- `/es/blog/mi-articulo`

---

## 9. Next steps

- [Read the full documentation](https://github.com/happytodev/blogr#readme)
- [Report an issue](https://github.com/happytodev/blogr/issues)
- [Star on GitHub](https://github.com/happytodev/blogr)

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Admin panel shows no resources | Run `php artisan blogr:install --force` to re-run setup |
| CMS page shows 404 | Ensure `cms.enabled` is `true` in `config/blogr.php` |
| Theme switcher doesn't work | Check that `@variant dark (.dark &);` is in your `app.css` |
| Blank page on blog | Run `npm run build` to compile frontend assets |
| "No pending migrations" | Run `php artisan migrate --force` manually |
