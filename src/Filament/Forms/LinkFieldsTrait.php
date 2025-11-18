<?php

namespace Happytodev\Blogr\Filament\Forms;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CmsPage;

/**
 * Trait for reusable link selection fields across forms
 * Provides consistent UI for selecting external URLs, blog home, categories, or CMS pages
 */
trait LinkFieldsTrait
{
    /**
     * Get link type selector field
     */
    public static function getLinkTypeField(string $fieldName = 'link_type', bool $includeBlogHome = true): Select
    {
        $options = [
            'external' => 'External URL',
        ];

        if ($includeBlogHome) {
            $options['blog'] = 'Blog Home';
        }

        $options['category'] = 'Category';
        $options['cms_page'] = 'CMS Page';

        return Select::make($fieldName)
            ->label('Link Type')
            ->options($options)
            ->default('external')
            ->live()
            ->required()
            ->columnSpan(1);
    }

    /**
     * Get external URL field
     */
    public static function getExternalUrlField(string $fieldName = 'url', string $linkTypeFieldName = 'link_type'): TextInput
    {
        return TextInput::make($fieldName)
            ->label('URL')
            ->url()
            ->nullable()
            ->placeholder('https://example.com/about')
            ->visible(fn(Get $get) => $get($linkTypeFieldName) === 'external')
            ->required(fn(Get $get) => $get($linkTypeFieldName) === 'external')
            ->columnSpan(1);
    }

    /**
     * Get category selector field
     */
    public static function getCategoryField(string $fieldName = 'category_id', string $linkTypeFieldName = 'link_type'): Select
    {
        return Select::make($fieldName)
            ->label('Select Category')
            ->options(function () {
                return Category::with('translations')
                    ->get()
                    ->mapWithKeys(function ($category) {
                        $translation = $category->translations->first();
                        return [$category->id => $translation->name ?? 'Category #' . $category->id];
                    });
            })
            ->searchable()
            ->visible(fn(Get $get) => $get($linkTypeFieldName) === 'category')
            ->required(fn(Get $get) => $get($linkTypeFieldName) === 'category')
            ->columnSpan(1);
    }

    /**
     * Get CMS page selector field
     */
    public static function getCmsPageField(string $fieldName = 'cms_page_id', string $linkTypeFieldName = 'link_type'): Select
    {
        return Select::make($fieldName)
            ->label('Select CMS Page')
            ->options(function () {
                return CmsPage::with('translations')
                    ->get()
                    ->mapWithKeys(function ($page) {
                        $translation = $page->translations->first();
                        return [$page->id => $translation->title ?? 'Page #' . $page->id];
                    });
            })
            ->searchable()
            ->visible(fn(Get $get) => $get($linkTypeFieldName) === 'cms_page')
            ->required(fn(Get $get) => $get($linkTypeFieldName) === 'cms_page')
            ->columnSpan(1);
    }

    /**
     * Get all link fields as schema array for use in forms
     *
     * @param string $linkTypeFieldName Name of the link_type field
     * @param string $urlFieldName Name of the URL field
     * @param string $categoryIdFieldName Name of the category_id field
     * @param string $cmsPageIdFieldName Name of the cms_page_id field
     * @param bool $includeBlogHome Whether to include 'Blog Home' option
     */
    public static function getLinkFieldsSchema(
        string $linkTypeFieldName = 'link_type',
        string $urlFieldName = 'url',
        string $categoryIdFieldName = 'category_id',
        string $cmsPageIdFieldName = 'cms_page_id',
        bool $includeBlogHome = true
    ): array {
        return [
            self::getLinkTypeField($linkTypeFieldName, $includeBlogHome),
            self::getExternalUrlField($urlFieldName, $linkTypeFieldName),
            self::getCategoryField($categoryIdFieldName, $linkTypeFieldName),
            self::getCmsPageField($cmsPageIdFieldName, $linkTypeFieldName),
        ];
    }
}
