<?php

use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

it('loads back-to-top settings from config', function () {
    config(['blogr.ui.back_to_top.enabled' => true]);
    config(['blogr.ui.back_to_top.shape' => 'square']);
    config(['blogr.ui.back_to_top.color' => '#ff0000']);
    
    $settings = new BlogrSettings();
    $settings->mount();
    
    expect($settings->back_to_top_enabled)->toBeTrue()
        ->and($settings->back_to_top_shape)->toBe('square')
        ->and($settings->back_to_top_color)->toBe('#ff0000');
});

it('uses default values when back-to-top config is missing', function () {
    config(['blogr.ui.back_to_top' => null]);
    
    $settings = new BlogrSettings();
    $settings->mount();
    
    expect($settings->back_to_top_enabled)->toBeTrue()
        ->and($settings->back_to_top_shape)->toBe('circle')
        ->and($settings->back_to_top_color)->toBeNull();
});

it('saves back-to-top settings to config', function () {
    $settings = new BlogrSettings();
    $settings->mount();
    
    $settings->back_to_top_enabled = false;
    $settings->back_to_top_shape = 'square';
    $settings->back_to_top_color = '#00ff00';
    
    $settings->save();
    
    // Reload config from file
    $configPath = config_path('blogr.php');
    $freshConfig = include $configPath;
    
    expect($freshConfig['ui']['back_to_top']['enabled'])->toBeFalse()
        ->and($freshConfig['ui']['back_to_top']['shape'])->toBe('square')
        ->and($freshConfig['ui']['back_to_top']['color'])->toBe('#00ff00');
});

it('back-to-top component respects enabled setting', function () {
    config(['blogr.ui.back_to_top.enabled' => false]);
    
    $html = view('blogr::components.back-to-top')->render();
    
    expect($html)->not->toContain('blogr-back-to-top');
});

it('back-to-top component uses circle shape by default', function () {
    config(['blogr.ui.back_to_top.enabled' => true]);
    config(['blogr.ui.back_to_top.shape' => 'circle']);
    
    $html = view('blogr::components.back-to-top')->render();
    
    expect($html)->toContain('rounded-full');
});

it('back-to-top component uses square shape when configured', function () {
    config(['blogr.ui.back_to_top.enabled' => true]);
    config(['blogr.ui.back_to_top.shape' => 'square']);
    
    $html = view('blogr::components.back-to-top')->render();
    
    expect($html)->toContain('rounded-lg');
});

it('back-to-top component uses primary color when no custom color is set', function () {
    config(['blogr.ui.back_to_top.enabled' => true]);
    config(['blogr.ui.back_to_top.color' => null]);
    
    $html = view('blogr::components.back-to-top')->render();
    
    expect($html)->toContain('var(--color-primary)');
});

it('back-to-top component uses custom color when configured', function () {
    config(['blogr.ui.back_to_top.enabled' => true]);
    config(['blogr.ui.back_to_top.color' => '#ff5500']);
    
    $html = view('blogr::components.back-to-top')->render();
    
    expect($html)->toContain('#ff5500');
});
