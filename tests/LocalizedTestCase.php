<?php

namespace Happytodev\Blogr\Tests;

class LocalizedTestCase extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Configure locales BEFORE ServiceProvider boots to ensure routes are registered correctly
        $app['config']->set('blogr.locales.enabled', true);
        $app['config']->set('blogr.locales.available', ['en', 'fr', 'de']);
        $app['config']->set('blogr.locales.default', 'en');
    }
}
