<?php

namespace Happytodev\Blogr\Tests;

use Illuminate\Foundation\Application;

class CmsWithPrefixTestCase extends CmsTestCase
{
    /**
     * Get environment setup.
     *
     * @param  Application  $app
     */
    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        // Set CMS prefix BEFORE routes are registered
        $app['config']->set('blogr.cms.prefix', 'page');
    }
}
