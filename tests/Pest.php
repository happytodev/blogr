<?php

use Happytodev\Blogr\Tests\TestCase;
use Happytodev\Blogr\Tests\LocalizedTestCase;

// LocalizedTestCase for tests that need locales enabled BEFORE boot
uses(LocalizedTestCase::class)->in('Localized');

// TestCase for standard tests (Unit, Arch, Browser)
uses(TestCase::class)->in('Unit');
uses(TestCase::class)->in('Arch');
uses(TestCase::class)->in('Browser');

// Feature tests: must declare uses() individually due to mix of TestCase and CmsTestCase
// Pest does not support conditional/excluded patterns, so we cannot use in('Feature')
// Each Feature test file declares its own uses() at the top

/*
|--------------------------------------------------------------------------
| Browser Testing Configuration
|--------------------------------------------------------------------------
|
| Configure PestPHP browser plugin for browser testing
|
*/

uses()->group('browser')->in('Browser');
