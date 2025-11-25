<?php

namespace Happytodev\Blogr\Tests;

class CmsWithPrefixTestCase extends CmsTestCase
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
        
        // Set CMS prefix BEFORE routes are registered
        $app['config']->set('blogr.cms.prefix', 'page');
    }
}
