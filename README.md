# Blogr – FilamentPHP Plugin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/happytodev/blogr/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/happytodev/blogr/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/happytodev/blogr/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/happytodev/blogr/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)

![alt text](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr.webp)

Blogr is a FilamentPHP plugin that adds a powerful blog system to your Laravel application.

## Features

- [x] Create, edit, and delete blog posts
- [x] Edit post in markdown
- [x] Table of contents is automatically generated 
- [x] A post can have a TL;DR
- [x] Support code (currently very simple)
- [x] A blog post can have a category
- [x] A blog post can have tags
- [x] A blog post can be published or not
- [x] Schedule posts for future publication with automatic publishing
- [x] Publication status indicator (draft/scheduled/published) with color coding
- [x] The slug of blog post is automatically generated but can be customized
- [x] Posts per category page
- [x] Posts per tags page
- [x] Image upload and editing
- [x] Automatic author assignment
- [x] Backend color customizable

## Screenshots

### Blog post view

![Blog post view](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-1.png)


### Backend - List of posts

![Backend - List of posts](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-2.png)

### Backend - Edit post

![Backend - Edit post](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-3.png)

## Roadmap

### Beta 2

- [x] SEO fields (meta title, description, keywords) ✅ **Completed**
- [x] Scheduled publishing ✅ **Completed**
- [x] In the admin in the list of posts, display the toggle for is_published to quickly publish or unpublish ✅ **Completed**
- [x] Add a table of content for blog post ✅ **Completed**
- [x] When no post is published, display a message to user ✅ **Completed**
- [ ] TOC could be deactivate for a post
- [ ] User could define if TOC is activated by default or not for every post
- [ ] Add a reading time information for blog post
- [ ] Integrate meta fields
- [ ] Add a RSS feed for the blog posts
- [ ] Create widgets to display on dashboard
- [ ] Add a settings page to easily manage settings set in config/blogr.php



## Requirements

- **Laravel 12.x**
- **FilamentPHP v4.x**

You have to start with a fresh install of Laravel and Filament v4 or add this package on existing app with these requirements.

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

Don't forget to import the class : 

```php
use Happytodev\Blogr\BlogrPlugin;
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


## Support

For questions or bug reports, open an issue on GitHub or contact [happytodev](mailto:happytodev@ik.me).

## Sponsor

If you like this project, you can support me via [GitHub Sponsors](https://github.com/sponsors/happytodev).


## License
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
