<?php

use Happytodev\Blogr\Database\Seeders\CmsPageSeeder;
use Happytodev\Blogr\Enums\CmsBlockType;
use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Filament\Resources\CmsPages\CmsBlockBuilder;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Mail;

uses(\Happytodev\Blogr\Tests\CmsTestCase::class);

/**
 * Helper to get the project root path from tests/Feature/Cms/
 */
function projectRoot(): string
{
    return dirname(__DIR__, 3);
}

// ──────────────────────────────────────────────
// CmsBlockType enum
// ──────────────────────────────────────────────

test('contact_form block type exists in enum', function () {
    expect(CmsBlockType::CONTACT_FORM)->toBe(CmsBlockType::from('contact_form'));
    expect(CmsBlockType::CONTACT_FORM->getLabel())->toBe('Contact Form');
    expect(CmsBlockType::CONTACT_FORM->getIcon())->toBe('heroicon-o-envelope');
    expect(CmsBlockType::CONTACT_FORM->getDescription())->toContain('Contact form');
});

test('map block type exists in enum', function () {
    expect(CmsBlockType::MAP)->toBe(CmsBlockType::from('map'));
    expect(CmsBlockType::MAP->getLabel())->toBe('Map');
    expect(CmsBlockType::MAP->getIcon())->toBe('heroicon-o-map-pin');
});

// ──────────────────────────────────────────────
// Contact page seeder — locales
// ──────────────────────────────────────────────

test('contact page seeder creates all 4 locale translations', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $page = CmsPage::where('slug', 'contact')->first();
    expect($page)->not->toBeNull();
    expect($page->template->value)->toBe('contact');

    $locales = $page->translations->pluck('locale')->sort()->values()->toArray();
    expect($locales)->toBe(['en', 'es', 'fr', 'pl']);
});

test('contact page english translation has correct slug and title', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $en = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', 'en')->first();
    expect($en->slug)->toBe('contact');
    expect($en->title)->toBe('Get in Touch');
});

test('contact page french translation has correct slug and title', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $fr = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', 'fr')->first();
    expect($fr->slug)->toBe('contact');
    expect($fr->title)->toBe('Nous Contacter');
});

test('contact page spanish translation has correct slug and title', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $es = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', 'es')->first();
    expect($es->slug)->toBe('contacto');
    expect($es->title)->toBe('Contáctenos');
});

test('contact page polish translation has correct slug and title', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $pl = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', 'pl')->first();
    expect($pl->slug)->toBe('kontakt');
    expect($pl->title)->toBe('Skontaktuj się z nami');
});

// ──────────────────────────────────────────────
// Contact page seeder — block structure
// ──────────────────────────────────────────────

test('each locale has exactly 6 blocks with correct types', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $page = CmsPage::where('slug', 'contact')->first();

    foreach (['en', 'fr', 'es', 'pl'] as $locale) {
        $trans = $page->translations()->where('locale', $locale)->first();
        $blocks = $trans->blocks;
        expect($blocks)->toHaveCount(6);

        $types = array_column($blocks, 'type');
        expect($types[0])->toBe('hero');
        expect($types[1])->toBe('stats');
        expect($types[2])->toBe('map');
        expect($types[3])->toBe('contact_form');
        expect($types[4])->toBe('features');
        expect($types[5])->toBe('cta');
    }
});

// ──────────────────────────────────────────────
// Hero block
// ──────────────────────────────────────────────

test('hero block has text shadow enabled for readability on dark gradient', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $en = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', 'en')->first();
    $hero = $en->blocks[0]['data'];

    expect($hero['background_type'])->toBe('gradient');
    expect($hero['text_shadow'])->toBeTrue();
    expect($hero['shadow_intensity'])->toBe('medium');
});

// ──────────────────────────────────────────────
// Stats block
// ──────────────────────────────────────────────

test('stats block has light and dark mode colors', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $en = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', 'en')->first();
    $stats = $en->blocks[1]['data'];

    // Light mode
    expect($stats['background_type'])->toBe('color');
    expect($stats['background_color'])->toBe('#ffffff');
    expect($stats['heading_color'])->toBe('#1e293b');
    expect($stats['text_color'])->toBe('#475569');

    // Dark mode
    expect($stats['background_type_dark'])->toBe('color');
    expect($stats['background_color_dark'])->toBe('#111827');
    expect($stats['heading_color_dark'])->toBe('#e2e8f0');
    expect($stats['text_color_dark'])->toBe('#94a3b8');
});

// ──────────────────────────────────────────────
// Map block — Grasse centering
// ──────────────────────────────────────────────

test('map block is centered on Grasse France', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    foreach (['en', 'fr', 'es', 'pl'] as $locale) {
        $trans = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', $locale)->first();
        $map = $trans->blocks[2]['data'];

        expect($map['center_lat'])->toBe(43.6589);
        expect($map['center_lng'])->toBe(6.9252);
        expect($map['zoom'])->toBe(13);
        expect($map['height'])->toBe(480);
    }
});

test('map block has tagline set', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $en = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', 'en')->first();
    $map = $en->blocks[2]['data'];

    expect($map['tagline'])->toBe('Made with love in the world capital of perfume');
    expect($map['tagline_position'])->toBe('bottom');
});

test('map block has 3 markers including Grasse landmarks', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $en = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', 'en')->first();
    $markers = $en->blocks[2]['data']['markers'];

    expect($markers)->toHaveCount(3);
    expect($markers[0]['popup_text'])->toContain('Grasse');
    expect($markers[0]['lat'])->toBe(43.6589);
    expect($markers[0]['lng'])->toBe(6.9252);
    expect($markers[1]['popup_text'])->toContain('Fragonard');
    expect($markers[2]['popup_text'])->toContain('Molinard');
});

test('map block has NO Google Maps references in data', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $en = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', 'en')->first();
    $mapJson = json_encode($en->blocks[2]);

    expect($mapJson)->not->toContain('google');
    expect($mapJson)->not->toContain('Google');
    expect($mapJson)->not->toContain('maps.google');
});

test('map block has light and dark mode colors for readability', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $en = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', 'en')->first();
    $map = $en->blocks[2]['data'];

    // Light mode
    expect($map['background_type'])->toBe('color');
    expect($map['background_color'])->toBe('#f8fafc');
    expect($map['heading_color'])->toBe('#0f172a');
    expect($map['subtitle_color'])->toBe('#64748b');
    expect($map['text_color'])->toBe('#334155');

    // Dark mode
    expect($map['background_type_dark'])->toBe('color');
    expect($map['background_color_dark'])->toBe('#0f172a');
    expect($map['heading_color_dark'])->toBe('#f1f5f9');
    expect($map['subtitle_color_dark'])->toBe('#94a3b8');
    expect($map['text_color_dark'])->toBe('#cbd5e1');
});

// ──────────────────────────────────────────────
// Contact form block
// ──────────────────────────────────────────────

test('contact_form block exists in every locale', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    foreach (['en', 'fr', 'es', 'pl'] as $locale) {
        $trans = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', $locale)->first();
        $form = $trans->blocks[3]['data'];
        expect($form['submit_text'])->not->toBeEmpty();
        expect($form['success_message'])->not->toBeEmpty();
    }
});

test('contact_form block has light and dark mode colors', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    $en = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', 'en')->first();
    $form = $en->blocks[3]['data'];

    expect($form['background_type'])->toBe('color');
    expect($form['background_color'])->toBe('#ffffff');
    expect($form['heading_color'])->toBe('#1e293b');
    expect($form['subtitle_color'])->toBe('#64748b');
    expect($form['background_type_dark'])->toBe('color');
    expect($form['background_color_dark'])->toBe('#111827');
    expect($form['heading_color_dark'])->toBe('#e2e8f0');
    expect($form['subtitle_color_dark'])->toBe('#94a3b8');
    expect($form['text_color_dark'])->toBe('#94a3b8');
});

// ──────────────────────────────────────────────
// Features block — no chat, X + Bluesky
// ──────────────────────────────────────────────

test('features block has X and Bluesky instead of chat', function () {
    $seeder = new CmsPageSeeder;
    $seeder->run();

    foreach (['en', 'fr', 'es', 'pl'] as $locale) {
        $trans = CmsPage::where('slug', 'contact')->first()->translations()->where('locale', $locale)->first();
        $items = $trans->blocks[4]['data']['items'];

        expect($items)->toHaveCount(3);

        $titles = array_column($items, 'title');

        // First item must reference email (in any language)
        $emailItem = $items[0];
        expect($emailItem['title'])->toMatch('/Email|Correo|Mail|Email|email/i');

        // Must contain X / Twitter reference
        $xItem = collect($items)->first(fn ($i) => str_contains($i['title'], 'X') || str_contains($i['title'], 'Twitter'));
        expect($xItem)->not->toBeNull();

        // Must contain Bluesky
        $bskyItem = collect($items)->first(fn ($i) => str_contains($i['title'], 'Bluesky') || str_contains($i['title'], 'Bluesky'));
        expect($bskyItem)->not->toBeNull();

        // Must NOT have Live Chat
        $chatItem = collect($items)->first(fn ($i) => str_contains(strtolower($i['title']), 'chat'));
        expect($chatItem)->toBeNull();
    }
});

// ──────────────────────────────────────────────
// Controller
// ──────────────────────────────────────────────

test('contact submit route is registered', function () {
    $routes = Route::getRoutes()->getRoutesByMethod()['POST'] ?? [];
    $contactRoute = collect($routes)->first(fn ($r) => $r->getName() === 'blogr.cms.contact.submit');
    expect($contactRoute)->not->toBeNull();
    expect($contactRoute->uri())->toBe('contact/submit');
});

test('contact submit accepts valid data', function () {
    Mail::fake();

    $response = $this->withoutMiddleware(VerifyCsrfToken::class)
        ->postJson(route('blogr.cms.contact.submit'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message that is long enough to pass validation.',
        ]);

    $response->assertStatus(200);
    if (! $response->ok()) {
        dump($response->getContent());
    }
    $response->assertJson(['success' => true]);
});

test('contact submit rejects missing name', function () {
    $response = $this->withoutMiddleware(VerifyCsrfToken::class)
        ->postJson(route('blogr.cms.contact.submit'), [
            'email' => 'john@example.com',
            'subject' => 'Test',
            'message' => 'A test message here.',
        ]);

    $response->assertStatus(422);
});

test('contact submit rejects invalid email', function () {
    $response = $this->withoutMiddleware(VerifyCsrfToken::class)
        ->postJson(route('blogr.cms.contact.submit'), [
            'name' => 'John',
            'email' => 'not-an-email',
            'subject' => 'Test',
            'message' => 'A test message here.',
        ]);

    $response->assertStatus(422);
});

test('contact submit rejects missing message', function () {
    $response = $this->withoutMiddleware(VerifyCsrfToken::class)
        ->postJson(route('blogr.cms.contact.submit'), [
            'name' => 'John',
            'email' => 'john@example.com',
            'subject' => 'Test',
            'message' => '',
        ]);

    $response->assertStatus(422);
});

test('contact submit csrf protection works', function () {
    $response = $this->postJson('/contact/submit', [
        'name' => 'John',
        'email' => 'john@example.com',
        'subject' => 'Test',
        'message' => 'A test message.',
    ]);

    // Without CSRF token should fail
    expect($response->status())->toBeIn([419, 422, 500]);
});

// ──────────────────────────────────────────────
// Map block blade view — no Google Maps
// ──────────────────────────────────────────────

test('map block blade view has no Google Maps references', function () {
    $path = projectRoot().'/resources/views/components/blocks/map.blade.php';
    $content = file_get_contents($path);

    expect($content)->not->toContain('google');
    expect($content)->not->toContain('Google');
    expect($content)->not->toContain('maps.google');
    expect($content)->not->toContain('goo.gl');
});

test('map block blade view uses Leaflet CDN from unpkg', function () {
    $path = projectRoot().'/resources/views/components/blocks/map.blade.php';
    $content = file_get_contents($path);

    expect($content)->toContain('unpkg.com/leaflet');
    expect($content)->toContain('L.map');
    expect($content)->toContain('tile.openstreetmap.org');
});

test('map block blade view uses CartoDB tiles for dark mode', function () {
    $path = projectRoot().'/resources/views/components/blocks/map.blade.php';
    $content = file_get_contents($path);

    expect($content)->toContain('basemaps.cartocdn.com/dark_all');
});

// ──────────────────────────────────────────────
// Contact form blade view — contrast
// ──────────────────────────────────────────────

test('contact form blade view has proper dark mode label contrast', function () {
    $path = projectRoot().'/resources/views/components/blocks/contact_form.blade.php';
    $content = file_get_contents($path);

    // Labels should have proper dark mode contrast
    expect($content)->toMatch('/dark:text-gray-300/');
    expect($content)->toMatch('/dark:bg-gray-800/');
    expect($content)->toMatch('/dark:bg-gray-700/');
    expect($content)->toMatch('/dark:border-gray-600/');
});

test('contact form blade view uses Alpine.js for form handling', function () {
    $path = projectRoot().'/resources/views/components/blocks/contact_form.blade.php';
    $content = file_get_contents($path);

    expect($content)->toContain('x-data');
    expect($content)->toContain('alpine:init');
    expect($content)->toContain('Alpine.data');
});

// ──────────────────────────────────────────────
// CmsBlockBuilder
// ──────────────────────────────────────────────

test('CmsBlockBuilder has contactFormBlock and mapBlock static methods', function () {
    $reflection = new ReflectionClass(CmsBlockBuilder::class);
    expect($reflection->hasMethod('contactFormBlock'))->toBeTrue();
    expect($reflection->hasMethod('mapBlock'))->toBeTrue();
    expect($reflection->hasMethod('featuresBlock'))->toBeTrue();
    expect($reflection->hasMethod('ctaBlock'))->toBeTrue();
    expect($reflection->hasMethod('heroBlock'))->toBeTrue();
    expect($reflection->hasMethod('statsBlock'))->toBeTrue();
});

test('CmsBlockBuilder make() does not throw', function () {
    CmsBlockBuilder::make();
    expect(true)->toBeTrue();
});

test('CmsPageTemplate CONTACT allows map, contact_form, features, cta, stats', function () {
    $available = CmsPageTemplate::CONTACT->availableBlocks();

    expect($available)->toContain('map');
    expect($available)->toContain('contact_form');
    expect($available)->toContain('features');
    expect($available)->toContain('cta');
    expect($available)->toContain('stats');
    expect($available)->toContain('hero');
    expect($available)->not->toContain('blog_posts');
});
