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

it('testimonial template supports full_width option', function () {
    $data = [
        'title' => 'Featured Testimonial',
        'full_width' => true,
        'items' => [
            ['name' => 'John Doe', 'quote' => 'Amazing service!', 'rating' => '5'],
        ],
    ];
    
    $html = view('blogr::components.blocks.testimonials', ['data' => $data])->render();
    
    // Should contain testimonial
    expect($html)->toContain('John Doe')
        ->and($html)->toContain('Amazing service!');
    
    // Should use single column layout when full_width is true
    expect($html)->toContain('grid-cols-1')
        ->and($html)->not->toContain('md:grid-cols-2')
        ->and($html)->not->toContain('lg:grid-cols-3');
});

it('testimonial template uses grid layout when full_width is false', function () {
    $data = [
        'full_width' => false,
        'items' => [
            ['name' => 'Jane Smith', 'quote' => 'Great!', 'rating' => '4'],
            ['name' => 'Bob Wilson', 'quote' => 'Good!', 'rating' => '3'],
        ],
    ];
    
    $html = view('blogr::components.blocks.testimonials', ['data' => $data])->render();
    
    // Should use multi-column grid layout
    expect($html)->toContain('md:grid-cols-2')
        ->and($html)->toContain('lg:grid-cols-3');
});

it('testimonial template defaults to grid layout when full_width is not set', function () {
    $data = [
        'items' => [
            ['name' => 'Test User', 'quote' => 'Test quote', 'rating' => '5'],
        ],
    ];
    
    $html = view('blogr::components.blocks.testimonials', ['data' => $data])->render();
    
    // Should default to grid layout
    expect($html)->toContain('md:grid-cols-2')
        ->and($html)->toContain('lg:grid-cols-3');
});

it('full_width testimonials are styled differently for single quotes', function () {
    $data = [
        'full_width' => true,
        'items' => [
            ['name' => 'VIP Client', 'quote' => 'This is a featured testimonial that deserves full attention.', 'role' => 'CEO', 'rating' => '5'],
        ],
    ];
    
    $html = view('blogr::components.blocks.testimonials', ['data' => $data])->render();
    
    // Should contain the testimonial
    expect($html)->toContain('VIP Client')
        ->and($html)->toContain('This is a featured testimonial');
    
    // Should use larger text for full width (text-xl or larger)
    expect($html)->toMatch('/text-xl|text-2xl/');
});

it('full_width field is available in CmsBlockBuilder', function () {
    // Read the CmsBlockBuilder source code
    $filePath = __DIR__ . '/../../src/Filament/Resources/CmsPages/CmsBlockBuilder.php';
    $source = file_get_contents($filePath);
    
    // Check that full_width toggle exists
    expect($source)->toContain('full_width')
        ->and($source)->toContain('Toggle::make');
});
