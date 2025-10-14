<?php

use Happytodev\Blogr\Tests\TestCase;
use Happytodev\Blogr\Tests\LocalizedTestCase;

// LocalizedTestCase for tests that need locales enabled BEFORE boot
uses(LocalizedTestCase::class)->in('Localized');

// TestCase for all other tests
uses(TestCase::class)->in('Feature');
uses(TestCase::class)->in('Unit');
uses(TestCase::class)->in('Arch');
uses(TestCase::class)->in('Browser');

/*
|--------------------------------------------------------------------------
| Browser Testing Configuration
|--------------------------------------------------------------------------
|
| Configure PestPHP browser plugin for browser testing
|
*/

uses()->group('browser')->in('Browser');
