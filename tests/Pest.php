<?php

use Happytodev\Blogr\Tests\LocalizedTestCase;
use Happytodev\Blogr\Tests\TestCase;

uses(LocalizedTestCase::class)->in('Localized');
uses(TestCase::class)->in('Unit');
uses(TestCase::class)->in('Arch');
uses(TestCase::class)->in('Browser');

uses()->group('browser')->in('Browser');
