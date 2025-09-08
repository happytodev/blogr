<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Browser Testing Configuration
|--------------------------------------------------------------------------
|
| Configure PestPHP browser plugin for browser testing
|
*/

uses()->group('browser')->in('Browser');
