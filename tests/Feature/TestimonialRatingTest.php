<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\User;

beforeEach(function () {
    // Prepare roles
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);
    
    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('testimonial template handles ratings from 0 to 5', function () {
    $data = [
        'title' => 'Client Testimonials',
        'items' => [
            ['name' => 'Five Stars', 'quote' => 'Excellent!', 'rating' => '5'],
            ['name' => 'Four Stars', 'quote' => 'Very good', 'rating' => '4'],
            ['name' => 'Three Stars', 'quote' => 'Good', 'rating' => '3'],
            ['name' => 'Two Stars', 'quote' => 'OK', 'rating' => '2'],
            ['name' => 'One Star', 'quote' => 'Not great', 'rating' => '1'],
            ['name' => 'Zero Stars', 'quote' => 'Bad', 'rating' => '0'],
        ],
    ];
    
    $html = view('blogr::components.blocks.testimonials', ['data' => $data])->render();
    
    // Should contain all testimonials
    expect($html)->toContain('Five Stars')
        ->and($html)->toContain('Four Stars')
        ->and($html)->toContain('Three Stars')
        ->and($html)->toContain('Two Stars')
        ->and($html)->toContain('One Star')
        ->and($html)->toContain('Zero Stars');
    
    // Should contain star icons (text-yellow-400 is the class for stars)
    expect($html)->toContain('text-yellow-400');
});

it('testimonial template hides rating section when rating is 0 or empty', function () {
    $data = [
        'items' => [
            ['name' => 'No Rating Zero', 'quote' => 'Quote', 'rating' => '0'],
            ['name' => 'No Rating Empty', 'quote' => 'Quote', 'rating' => ''],
            ['name' => 'No Rating Null', 'quote' => 'Quote'],
        ],
    ];
    
    $html = view('blogr::components.blocks.testimonials', ['data' => $data])->render();
    
    // Should contain testimonials
    expect($html)->toContain('No Rating Zero')
        ->and($html)->toContain('No Rating Empty')
        ->and($html)->toContain('No Rating Null');
    
    // Count star SVG elements - should be 0
    $starCount = substr_count($html, 'text-yellow-400');
    expect($starCount)->toBe(0);
});

it('testimonial template displays correct number of stars', function () {
    $data = [
        'items' => [
            ['name' => 'Test 5', 'quote' => 'Quote', 'rating' => '5'],
            ['name' => 'Test 3', 'quote' => 'Quote', 'rating' => '3'],
            ['name' => 'Test 1', 'quote' => 'Quote', 'rating' => '1'],
        ],
    ];
    
    $html = view('blogr::components.blocks.testimonials', ['data' => $data])->render();
    
    // Should display stars (5 + 3 + 1 = 9 stars total)
    $starCount = substr_count($html, '<path d="M9.049 2.927c.3-.921');
    expect($starCount)->toBe(9);
});


it('rating field configuration includes all options from 0 to 5', function () {
    // Read the CmsBlockBuilder source code
    $filePath = __DIR__ . '/../../src/Filament/Resources/CmsPages/CmsBlockBuilder.php';
    $source = file_get_contents($filePath);
    
    // Check that all rating options are present in the code
    expect($source)->toContain("'0' =>")
        ->and($source)->toContain("'1' =>")
        ->and($source)->toContain("'2' =>")
        ->and($source)->toContain("'3' =>")
        ->and($source)->toContain("'4' =>")
        ->and($source)->toContain("'5' =>");
});
