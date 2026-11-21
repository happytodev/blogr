<?php

namespace Happytodev\Blogr\Tests;

/**
 * Same as LocalizedTestCase, but with the blog served as the homepage:
 * the routes lose the /blog prefix and live directly under the locale
 * (/{locale}/..., /{locale}/series/...).
 */
class LocalizedHomepageTestCase extends LocalizedTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('blogr.homepage.type', 'blog');
        $app['config']->set('blogr.route.homepage', true);
        $app['config']->set('blogr.route.prefix', '');
        $app['config']->set('blogr.cms.enabled', false);
    }
}
