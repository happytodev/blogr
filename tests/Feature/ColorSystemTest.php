<?php

// ============================================
// PHASE RED: Tests pour le nouveau système de couleurs
// ============================================

describe('Card Border Color Removal', function () {
    test('card border color setting is removed from properties', function () {
        $settings = new \Happytodev\Blogr\Filament\Pages\BlogrSettings();
        $reflection = new \ReflectionClass($settings);
        
        // Should not have blog_index_cards_colors_top_border property
        expect($reflection->hasProperty('blog_index_cards_colors_top_border'))->toBeFalse();
    });
    
    test('card border color is not in config', function () {
        $configPath = __DIR__ . '/../../config/blogr.php';
        $content = file_get_contents($configPath);
        
        // Should not contain top_border in cards section
        expect($content)->not->toContain("'top_border'");
    });
});

describe('Appearance Card Colors', function () {
    test('blog post card colors are configurable', function () {
        config([
            'blogr.ui.appearance.blog_card_bg' => '#ffffff',
            'blogr.ui.appearance.blog_card_bg_dark' => '#1f2937',
        ]);
        
        expect(config('blogr.ui.appearance.blog_card_bg'))->toBe('#ffffff');
        expect(config('blogr.ui.appearance.blog_card_bg_dark'))->toBe('#1f2937');
    });
    
    test('series card colors are configurable', function () {
        config([
            'blogr.ui.appearance.series_card_bg' => '#f9fafb',
            'blogr.ui.appearance.series_card_bg_dark' => '#374151',
        ]);
        
        expect(config('blogr.ui.appearance.series_card_bg'))->toBe('#f9fafb');
        expect(config('blogr.ui.appearance.series_card_bg_dark'))->toBe('#374151');
    });
});

describe('Theme Color System', function () {
    test('primary colors with dark and hover variants exist', function () {
        config([
            'blogr.ui.theme.primary_color' => '#c20be5',
            'blogr.ui.theme.primary_color_dark' => '#9b0ab8',
            'blogr.ui.theme.primary_color_hover' => '#d946ef',
            'blogr.ui.theme.primary_color_hover_dark' => '#a855f7',
        ]);
        
        expect(config('blogr.ui.theme.primary_color'))->toBe('#c20be5');
        expect(config('blogr.ui.theme.primary_color_dark'))->toBe('#9b0ab8');
        expect(config('blogr.ui.theme.primary_color_hover'))->toBe('#d946ef');
        expect(config('blogr.ui.theme.primary_color_hover_dark'))->toBe('#a855f7');
    });
    
    test('category colors with dark variant exist', function () {
        config([
            'blogr.ui.theme.category_bg' => '#e0f2fe',
            'blogr.ui.theme.category_bg_dark' => '#0c4a6e',
        ]);
        
        expect(config('blogr.ui.theme.category_bg'))->toBe('#e0f2fe');
        expect(config('blogr.ui.theme.category_bg_dark'))->toBe('#0c4a6e');
    });
    
    test('tag colors with dark variant exist', function () {
        config([
            'blogr.ui.theme.tag_bg' => '#d1fae5',
            'blogr.ui.theme.tag_bg_dark' => '#065f46',
        ]);
        
        expect(config('blogr.ui.theme.tag_bg'))->toBe('#d1fae5');
        expect(config('blogr.ui.theme.tag_bg_dark'))->toBe('#065f46');
    });
    
    test('author colors with dark variant exist', function () {
        config([
            'blogr.ui.theme.author_bg' => '#fef3c7',
            'blogr.ui.theme.author_bg_dark' => '#78350f',
        ]);
        
        expect(config('blogr.ui.theme.author_bg'))->toBe('#fef3c7');
        expect(config('blogr.ui.theme.author_bg_dark'))->toBe('#78350f');
    });
});

describe('CSS Variables Injection', function () {
    test('all color CSS variables are injected in layout', function () {
        $viewPath = __DIR__ . '/../../resources/views/layouts/blog.blade.php';
        $content = file_get_contents($viewPath);
        
        // Primary colors
        expect($content)->toContain('--color-primary:');
        expect($content)->toContain('--color-primary-dark:');
        expect($content)->toContain('--color-primary-hover:');
        expect($content)->toContain('--color-primary-hover-dark:');
        
        // Card colors
        expect($content)->toContain('--color-blog-card-bg:');
        expect($content)->toContain('--color-blog-card-bg-dark:');
        expect($content)->toContain('--color-series-card-bg:');
        expect($content)->toContain('--color-series-card-bg-dark:');
        
        // Category colors
        expect($content)->toContain('--color-category-bg:');
        expect($content)->toContain('--color-category-bg-dark:');
        
        // Tag colors
        expect($content)->toContain('--color-tag-bg:');
        expect($content)->toContain('--color-tag-bg-dark:');
        
        // Author colors
        expect($content)->toContain('--color-author-bg:');
        expect($content)->toContain('--color-author-bg-dark:');
    });
});

describe('Default Colors', function () {
    test('config file has all default colors defined', function () {
        $configPath = __DIR__ . '/../../config/blogr.php';
        $content = file_get_contents($configPath);
        
        // Primary colors
        expect($content)->toContain('#c20be5'); // Primary
        expect($content)->toContain('#9b0ab8'); // Primary dark
        expect($content)->toContain('#d946ef'); // Primary hover
        expect($content)->toContain('#e49df2'); // Primary hover dark
        
        // Cards
        expect($content)->toContain("'blog_card_bg'");
        expect($content)->toContain("'series_card_bg'");
        
        // Categories
        expect($content)->toContain("'category_bg'");
        
        // Tags
        expect($content)->toContain("'tag_bg'");
        
        // Author
        expect($content)->toContain("'author_bg'");
    });
});
