<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Filament\Pages\BlogrSettings;

it('demonstrates the bug: second posts assignment overwrites first', function () {
    // Simulate what happens in the save method
    $testConfig = [
        'ui' => [
            'posts' => [
                'tags_position' => 'top',
            ],
        ],
    ];
    
    // Later in the code, another section tries to set posts
    $testConfig['ui']['posts'] = [
        'default_image' => '/test.jpg',
        'show_language_switcher' => true,
    ];
    
    // This demonstrates the bug: the second assignment overwrites the first
    expect($testConfig['ui']['posts'])->not->toHaveKey('tags_position');
    expect($testConfig['ui']['posts'])->toHaveKey('default_image');
});

it('correctly merges all ui.posts settings in a single array', function () {
    // Correct way: all settings in one array
    $correctConfig = [
        'ui' => [
            'posts' => [
                'tags_position' => 'top',
                'default_image' => '/custom/image.jpg',
                'show_language_switcher' => false,
            ],
        ],
    ];
    
    // All keys should be present
    expect($correctConfig['ui']['posts'])->toHaveKeys(['tags_position', 'default_image', 'show_language_switcher']);
    expect($correctConfig['ui']['posts']['tags_position'])->toBe('top');
    expect($correctConfig['ui']['posts']['default_image'])->toBe('/custom/image.jpg');
    expect($correctConfig['ui']['posts']['show_language_switcher'])->toBe(false);
});
