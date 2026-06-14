<?php

// translations for Happytodev/Blogr
return [
    'users' => [
        'model_label' => 'User',
        'navigation_label' => 'Users',
        'plural_model_label' => 'Users',
    ],

    // Blog UI
    'ui' => [
        'about_the_author' => 'About the author',
        'back_to_all_posts' => 'Back to all posts',
        'back_to_blog' => 'Back to blog',
        'check_back_soon' => 'Check back soon for new content!',
        'current' => 'current',
        'featured' => 'Featured',
        'latest_posts' => 'Latest Posts',
        'min_read' => ':minutes min read',
        'no_posts_yet' => 'No posts yet',
        'part_of_series' => 'Part of Series',
        'post_in_series' => ':count post in this series',
        'posts_in_category' => 'Posts in Category',
        'posts_in_series' => ':count posts in this series',
        'posts_with_tag' => 'Posts with Tag',
        'read_more' => 'Read more',
        'read_post' => 'Read post',
        'reading_time' => ':time min read',
        'table_of_contents' => 'Table of contents',
        'tags' => 'Tags',
        'translation_available_in' => 'Available in',
        'translation_unavailable_message' => 'This content is not available in :requested. Showing :showing version instead.',
        'translation_unavailable_title' => 'Translation not available',
        'untitled' => 'Untitled',
        'visit_site' => 'Visit the blog',
        'quick_access' => 'Quick Access',
        'visit_blog' => 'Open in new tab',
        'view_website' => 'View Website',
    ],

    // Series
    'series' => [
        'authors' => 'Authors',
        'current' => 'current',
        'all_posts_in_series' => 'All Posts in This Series',
        'description' => 'Browse all our blog series and learn step by step.',
        'featured' => 'Featured',
        'featured_series' => 'Featured Series',
        'no_series' => 'No series published yet',
        'part_number' => 'Part :number',
        'part_of_series' => 'Part of Series',
        'posts_count' => ':count posts',
        'series' => 'This post is part of the series',
        'show_less_posts' => 'Show less',
        'show_more_posts' => 'See :count more posts in this series',
        'started_on' => 'Started :date',
        'title' => 'Blog Series',
        'view_all_series' => 'View all series',
        'view_serie' => 'View serie',
    ],

    // Dates
    'date' => [
        'draft' => 'Draft',
        'published_on' => 'Published on :date',
        'updated_on' => 'Updated on :date',
    ],

    // Notifications
    'notifications' => [
        'post_saved_subject' => '[Blogr] Post saved by :author',
        'post_saved_line1' => 'User :author has saved a post titled ":title".',
        'post_saved_line2' => 'You receive this notification because you are an administrator.',
        'view_post' => 'View Post',
    ],

    // Settings
    'settings' => [
        'save' => 'Save Settings',
        'navigation_label' => 'Settings',
        'saved_successfully' => 'Settings saved successfully!',
        'run_sync_command' => 'To apply the new admin panel path, run: php artisan blogr:sync-admin-path',
        'env_not_writable' => 'Warning: the .env file is not writable. Mail credentials were not saved.',
    ],

    // Feeds
    'feeds' => [
        'title' => 'Feeds',
        'description' => 'Subscribe to our RSS feeds to stay updated with the latest content.',
        'main_feed' => 'Main Feed',
        'all_posts' => 'All Posts',
        'main_feed_desc' => 'Latest blog posts from all categories',
        'categories' => 'Categories',
        'tags' => 'Tags',
        'posts' => 'posts',
    ],

    // AI Translation
    'translation' => [
        'local_counter' => 'Local counter: characters translated through this instance only. The total may be higher if the same key is used from multiple sites.',
        'view_azure_usage' => 'View actual usage on Azure portal →',
        'azure_metrics_help' => 'Go to <strong>Monitoring &gt; Metrics</strong>, select <em>Text Characters Translated</em> as the metric, and adjust the date range in the top-right of the chart.',
        'chars' => 'chars',
        'remaining' => 'remaining',
        'select_provider' => 'Select a provider to see usage statistics.',
        'no_data' => 'No usage data for this month.',
    ],

    // Profile / Bio
    'profile' => [
        'bio_heading' => 'Biography',
        'bio_subheading' => 'Write your author biography in markdown. Supports multiple languages.',
        'bio_label' => 'Biography (:locale)',
        'bio_help' => 'Supports Markdown formatting (bold, italic, links, lists, code blocks).',
        'bio_submit' => 'Save Biography',
        'bio_updated' => 'Biography saved successfully!',
        'translate_bio' => 'Translate with AI',
        'source_locale' => 'Source language',
        'target_locale' => 'Target language',
        'select_source' => 'Select source language',
        'select_target' => 'Select target language',
        'cancel' => 'Cancel',
        'translate_button' => 'Translate',
        'no_source_bio' => 'No biography content found for :locale.',
        'bio_translated' => 'Biography translated to :locale.',
        'translation_error' => 'Translation error',
    ],
];
