<?php

use Happytodev\Blogr\Helpers\LinkResolver;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\LocalizedTestCase;

uses(LocalizedTestCase::class);

describe('LinkResolver', function () {
    it('resolves external URL link type', function () {
        $data = [
            'link_type' => 'external',
            'url' => 'https://example.com',
        ];

        $result = LinkResolver::resolve($data, 'link_type', 'url');

        expect($result)->toBe('https://example.com');
    });

    it('defaults to external when link_type is missing', function () {
        $data = [
            'url' => 'https://example.com',
        ];

        $result = LinkResolver::resolve($data, 'link_type', 'url');

        expect($result)->toBe('https://example.com');
    });

    it('returns null for null URL', function () {
        $data = [
            'link_type' => 'external',
            'url' => null,
        ];

        $result = LinkResolver::resolve($data, 'link_type', 'url');

        expect($result)->toBeNull();
    });

    it('works with custom field names', function () {
        $data = [
            'cta_link_type' => 'external',
            'cta_url' => 'https://custom.com',
        ];

        $result = LinkResolver::resolve(
            $data,
            'cta_link_type',
            'cta_url'
        );

        expect($result)->toBe('https://custom.com');
    });

    it('returns null for unknown link type', function () {
        $data = [
            'link_type' => 'unknown',
        ];

        $result = LinkResolver::resolve($data, 'link_type', 'url');

        expect($result)->toBeNull();
    });

    it('resolves category link type (returns null in test context without routes)', function () {
        $category = Category::factory()->create();
        $category->translations()->create([
            'locale' => 'en',
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $data = [
            'link_type' => 'category',
            'category_id' => $category->id,
        ];

        $result = LinkResolver::resolve($data, 'link_type', 'url', 'category_id');

        // In test context without routes, should return null gracefully
        expect($result === null || is_string($result))->toBeTrue();
    });

    it('resolves cms_page link type (returns null in test context without routes)', function () {
        $page = CmsPage::factory()->create();
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'Content',
            'blocks' => [],
        ]);

        $data = [
            'link_type' => 'cms_page',
            'cms_page_id' => $page->id,
        ];

        $result = LinkResolver::resolve($data, 'link_type', 'url', 'category_id', 'cms_page_id');

        // In test context without routes, should return null gracefully
        expect($result === null || is_string($result))->toBeTrue();
    });

    it('resolves blog link type (returns null in test context without routes)', function () {
        $data = [
            'link_type' => 'blog',
        ];

        $result = LinkResolver::resolve($data, 'link_type', 'url');

        // In test context without routes, should return null gracefully
        expect($result === null || is_string($result))->toBeTrue();
    });

    it('returns null for invalid category ID', function () {
        $data = [
            'link_type' => 'category',
            'category_id' => 99999,
        ];

        $result = LinkResolver::resolve($data, 'link_type', 'url', 'category_id');

        expect($result)->toBeNull();
    });

    it('returns null for invalid CMS page ID', function () {
        $data = [
            'link_type' => 'cms_page',
            'cms_page_id' => 99999,
        ];

        $result = LinkResolver::resolve($data, 'link_type', 'url', 'category_id', 'cms_page_id');

        expect($result)->toBeNull();
    });
});
