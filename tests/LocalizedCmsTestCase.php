<?php

namespace Happytodev\Blogr\Tests;

use Illuminate\Foundation\Application;

/**
 * Test case for CMS tests with locales enabled
 *
 * This extends CmsTestCase and adds locale configuration.
 * Used to test CMS behavior with multi-language support.
 */
abstract class LocalizedCmsTestCase extends CmsTestCase
{
    /**
     * Get environment setup.
     *
     * @param  Application  $app
     */
    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        // Enable locales for these tests
        $app['config']->set('blogr.locales.enabled', true);
        $app['config']->set('blogr.locales.default', 'en');
        $app['config']->set('blogr.locales.available', ['en', 'fr']);
    }
}
