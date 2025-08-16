# This is my package blogr

[![Latest Version on Packagist](https://img.shields.io/packagist/v/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/happytodev/blogr/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/happytodev/blogr/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/happytodev/blogr/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/happytodev/blogr/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)



# Blogr – FilamentPHP Plugin

Blogr is a FilamentPHP plugin that adds a powerful blog system to your Laravel application.

## Installation

1. **Install the package via Composer**

```bash
composer require happytodev/blogr
```

2. **Publish configuration and migration files**

```bash
php artisan vendor:publish --provider="Happytodev\Blogr\BlogrServiceProvider"
```

3. **Run the migrations**

```bash
php artisan migrate
```

4. **Add the plugin in AdminPanelProvider class**

Add this line in your file `app\Providers\Filament\AdminPanelProvider.php`

```php
            ->plugin(BlogrPlugin::make())
```

5. **Install typography plugin**

Run `npm install -D @tailwindcss/typography`

6. **Add typography plugin in `resources\css\app.css`**

In `resources\css\app.css`, change : 

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
...
```

by 

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
@plugin "@tailwindcss/typography";
...
```

7. **Access the blog in Filament**

The plugin adds a Filament resource for managing blog posts.  
Log in to your Filament admin panel and go to the “Blog Posts” section.

## Configuration

You can customize the table prefix in the published config file:  
`config/blogr.php`

## Features

- Create, edit, and delete blog posts
- Image upload and editing
- SEO fields (meta title, description, keywords)
- Scheduled publishing
- Automatic author assignment

## Support

For questions or bug reports, open an issue on GitHub or contact [happytodev](mailto:happytodev@ik.me).

## Sponsor

If you like this project, you can support me via [GitHub Sponsors](https://github.com/sponsors/happytodev).

---

**License**  
MIT

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Frédéric Blanc](https://github.com/happytodev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
