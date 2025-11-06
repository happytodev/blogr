<?php

namespace Happytodev\Blogr\Enums;

use Filament\Support\Contracts\HasLabel;

enum CmsBlockType: string implements HasLabel
{
    case HERO = 'hero';
    case FEATURES = 'features';
    case TESTIMONIALS = 'testimonials';
    case CTA = 'cta';
    case GALLERY = 'gallery';
    case FAQ = 'faq';
    case TEAM = 'team';
    case PRICING = 'pricing';
    case CONTENT = 'content';
    case BLOG_POSTS = 'blog_posts';
    case STATS = 'stats';
    case TIMELINE = 'timeline';
    case VIDEO = 'video';
    case NEWSLETTER = 'newsletter';
    case MAP = 'map';

    public function getLabel(): string
    {
        return match ($this) {
            self::HERO => __('Hero Banner'),
            self::FEATURES => __('Features Grid'),
            self::TESTIMONIALS => __('Testimonials'),
            self::CTA => __('Call to Action'),
            self::GALLERY => __('Image Gallery'),
            self::FAQ => __('FAQ Accordion'),
            self::TEAM => __('Team Members'),
            self::PRICING => __('Pricing Plans'),
            self::CONTENT => __('Rich Content'),
            self::BLOG_POSTS => __('Blog Posts'),
            self::STATS => __('Stats & Metrics'),
            self::TIMELINE => __('Timeline'),
            self::VIDEO => __('Video'),
            self::NEWSLETTER => __('Newsletter'),
            self::MAP => __('Map'),
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::HERO => __('Large banner with image, title, subtitle and call-to-action button'),
            self::FEATURES => __('Grid of features with icons, titles and descriptions'),
            self::TESTIMONIALS => __('Customer testimonials with photos, names and quotes'),
            self::CTA => __('Simple call-to-action with heading and button'),
            self::GALLERY => __('Responsive image gallery with lightbox support'),
            self::FAQ => __('Frequently asked questions with collapsible answers'),
            self::TEAM => __('Team members with photos, names, roles and bios'),
            self::PRICING => __('Pricing plans comparison with features list'),
            self::CONTENT => __('Rich text content with Markdown support'),
            self::BLOG_POSTS => __('Display recent blog posts from your blog'),
            self::STATS => __('Animated counters with metrics and statistics'),
            self::TIMELINE => __('Chronological timeline of events'),
            self::VIDEO => __('Embed YouTube or Vimeo videos'),
            self::NEWSLETTER => __('Newsletter subscription form'),
            self::MAP => __('Interactive map with location marker'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HERO => 'heroicon-o-photo',
            self::FEATURES => 'heroicon-o-squares-2x2',
            self::TESTIMONIALS => 'heroicon-o-chat-bubble-left-right',
            self::CTA => 'heroicon-o-cursor-arrow-rays',
            self::GALLERY => 'heroicon-o-rectangle-stack',
            self::FAQ => 'heroicon-o-question-mark-circle',
            self::TEAM => 'heroicon-o-user-group',
            self::PRICING => 'heroicon-o-currency-dollar',
            self::CONTENT => 'heroicon-o-document-text',
            self::BLOG_POSTS => 'heroicon-o-newspaper',
            self::STATS => 'heroicon-o-chart-bar',
            self::TIMELINE => 'heroicon-o-clock',
            self::VIDEO => 'heroicon-o-play-circle',
            self::NEWSLETTER => 'heroicon-o-envelope',
            self::MAP => 'heroicon-o-map-pin',
        };
    }
}
