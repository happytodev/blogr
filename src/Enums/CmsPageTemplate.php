<?php

namespace Happytodev\Blogr\Enums;

enum CmsPageTemplate: string
{
    case DEFAULT = 'default';
    case LANDING = 'landing';
    case CONTACT = 'contact';
    case ABOUT = 'about';
    case PRICING = 'pricing';
    case FAQ = 'faq';
    case CUSTOM = 'custom';

    /**
     * Get human-readable label for the template
     */
    public function label(): string
    {
        return match($this) {
            self::DEFAULT => 'Simple Page',
            self::LANDING => 'Landing Page',
            self::CONTACT => 'Contact Page',
            self::ABOUT => 'About Page',
            self::PRICING => 'Pricing Page',
            self::FAQ => 'FAQ Page',
            self::CUSTOM => 'Custom Page',
        };
    }

    /**
     * Get description for the template
     */
    public function description(): string
    {
        return match($this) {
            self::DEFAULT => 'Simple page with markdown content',
            self::LANDING => 'Landing page with block builder (hero, features, CTA, etc.)',
            self::CONTACT => 'Contact form with map integration',
            self::ABOUT => 'About page with team and timeline blocks',
            self::PRICING => 'Pricing tables with plans comparison',
            self::FAQ => 'Frequently Asked Questions with accordion',
            self::CUSTOM => 'Full custom page with all available blocks',
        };
    }

    /**
     * Get available blocks for this template
     */
    public function availableBlocks(): array
    {
        return match($this) {
            self::DEFAULT => ['markdown'],
            self::LANDING => ['hero', 'features', 'cta', 'testimonials', 'pricing', 'statistics'],
            self::CONTACT => ['hero', 'contact_form', 'map', 'markdown'],
            self::ABOUT => ['hero', 'markdown', 'team', 'timeline', 'statistics'],
            self::PRICING => ['hero', 'pricing', 'faq', 'testimonials', 'cta'],
            self::FAQ => ['hero', 'faq', 'contact_form', 'markdown'],
            self::CUSTOM => ['hero', 'features', 'cta', 'testimonials', 'faq', 'contact_form', 'markdown', 'team', 'gallery', 'pricing', 'statistics', 'timeline', 'map', 'html'],
        };
    }

    /**
     * Check if this template supports block builder
     */
    public function supportsBlockBuilder(): bool
    {
        return $this !== self::DEFAULT;
    }

    /**
     * Get Blade view path for this template
     */
    public function viewPath(): string
    {
        return "blogr::cms.templates.{$this->value}";
    }

    /**
     * Get all templates as options array for Filament Select
     */
    public static function toSelectOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}
