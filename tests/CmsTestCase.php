<?php

namespace Happytodev\Blogr\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class CmsTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Get environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        
        // Enable CMS by default for all CMS tests
        $app['config']->set('blogr.cms.enabled', true);
        
        // Set CMS as homepage by default for CMS tests
        $app['config']->set('blogr.homepage.type', 'cms');
        
        // Configure CMS routes
        $app['config']->set('blogr.cms.prefix', '');
        
        // Load reserved slugs configuration for tests
        $app['config']->set('blogr.cms.reserved_slugs', [
            // Blog routes
            'blog',
            'feed',
            'author',
            'category',
            'tag',
            'series',
            'rss',
            
            // Authentication routes
            'admin',
            'login',
            'logout',
            'register',
            'password',
            'dashboard',
            
            // Common system routes
            'api',
            'storage',
            'livewire',
        ]);
    }
}
