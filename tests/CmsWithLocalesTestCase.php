<?php

namespace Happytodev\Blogr\Tests;

class CmsWithLocalesTestCase extends CmsTestCase
{
    /**
     * Get environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        
        // Enable locales BEFORE routes are registered
        $app['config']->set('blogr.locales.enabled', true);
        $app['config']->set('blogr.locales.available', ['en', 'fr']);
        $app['config']->set('blogr.locales.default', 'en');
        $app['config']->set('blogr.cms.route.prefix', '');
        
        // Set CMS as homepage (new unified config)
        $app['config']->set('blogr.homepage.type', 'cms');
        // Legacy support: Disable blog as homepage
        $app['config']->set('blogr.route.homepage', false);
    }
}

