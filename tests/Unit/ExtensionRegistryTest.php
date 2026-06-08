<?php

use Happytodev\Blogr\Contracts\BlogrExtension;
use Happytodev\Blogr\Services\ExtensionRegistry;

// ─── BlogrExtension INTERFACE ─────────────────────────

test('BlogrExtension interface exists', function () {
    expect(interface_exists(BlogrExtension::class))->toBeTrue();
});

test('BlogrExtension interface has required methods', function () {
    $methods = (new ReflectionClass(BlogrExtension::class))->getMethods();

    $methodNames = array_map(fn ($m) => $m->getName(), $methods);

    expect($methodNames)->toContain('getId');
    expect($methodNames)->toContain('getName');
    expect($methodNames)->toContain('getDescription');
    expect($methodNames)->toContain('getVersion');
    expect($methodNames)->toContain('getAuthor');
    expect($methodNames)->toContain('getHomepage');
    expect($methodNames)->toContain('getDependencies');
});

// ─── EXTENSION REGISTRY ───────────────────────────────

test('ExtensionRegistry can register and retrieve extensions', function () {
    $registry = new ExtensionRegistry();

    $extension = new class implements BlogrExtension {
        public function getId(): string { return 'test-plugin'; }
        public function getName(): string { return 'Test Plugin'; }
        public function getDescription(): string { return 'A test plugin.'; }
        public function getVersion(): string { return '1.0.0'; }
        public function getAuthor(): string { return 'Tester'; }
        public function getHomepage(): ?string { return 'https://example.com'; }
        public function getDependencies(): array { return []; }
    };

    $registry->register($extension);

    expect($registry->has('test-plugin'))->toBeTrue();
    expect($registry->get('test-plugin'))->toBe($extension);
    expect($registry->count())->toBe(1);
});

test('ExtensionRegistry returns null for unknown extensions', function () {
    $registry = new ExtensionRegistry();

    expect($registry->get('non-existent'))->toBeNull();
    expect($registry->has('non-existent'))->toBeFalse();
});

test('ExtensionRegistry can list all extensions', function () {
    $registry = new ExtensionRegistry();

    $ext1 = new class implements BlogrExtension {
        public function getId(): string { return 'ext-one'; }
        public function getName(): string { return 'Extension One'; }
        public function getDescription(): string { return 'First extension.'; }
        public function getVersion(): string { return '1.0.0'; }
        public function getAuthor(): string { return 'Author'; }
        public function getHomepage(): ?string { return null; }
        public function getDependencies(): array { return []; }
    };

    $ext2 = new class implements BlogrExtension {
        public function getId(): string { return 'ext-two'; }
        public function getName(): string { return 'Extension Two'; }
        public function getDescription(): string { return 'Second extension.'; }
        public function getVersion(): string { return '2.0.0'; }
        public function getAuthor(): string { return 'Author'; }
        public function getHomepage(): ?string { return null; }
        public function getDependencies(): array { return ['ext-one']; }
    };

    $registry->register($ext1);
    $registry->register($ext2);

    $all = $registry->getAll();

    expect($all)->toHaveCount(2);
    expect($all)->toHaveKey('ext-one');
    expect($all)->toHaveKey('ext-two');
});

test('ExtensionRegistry can count extensions', function () {
    $registry = new ExtensionRegistry();

    expect($registry->count())->toBe(0);

    $ext = new class implements BlogrExtension {
        public function getId(): string { return 'only-one'; }
        public function getName(): string { return 'Only One'; }
        public function getDescription(): string { return ''; }
        public function getVersion(): string { return '0.1.0'; }
        public function getAuthor(): string { return 'Dev'; }
        public function getHomepage(): ?string { return null; }
        public function getDependencies(): array { return []; }
    };

    $registry->register($ext);
    expect($registry->count())->toBe(1);
});

test('registering extension with same id overwrites previous', function () {
    $registry = new ExtensionRegistry();

    $ext1 = new class implements BlogrExtension {
        public function getId(): string { return 'same-id'; }
        public function getName(): string { return 'Original'; }
        public function getDescription(): string { return ''; }
        public function getVersion(): string { return '1.0.0'; }
        public function getAuthor(): string { return 'A'; }
        public function getHomepage(): ?string { return null; }
        public function getDependencies(): array { return []; }
    };

    $ext2 = new class implements BlogrExtension {
        public function getId(): string { return 'same-id'; }
        public function getName(): string { return 'Overwritten'; }
        public function getDescription(): string { return ''; }
        public function getVersion(): string { return '2.0.0'; }
        public function getAuthor(): string { return 'B'; }
        public function getHomepage(): ?string { return null; }
        public function getDependencies(): array { return []; }
    };

    $registry->register($ext1);
    $registry->register($ext2);

    expect($registry->count())->toBe(1);
    expect($registry->get('same-id')->getName())->toBe('Overwritten');
});

test('extension can have dependencies', function () {
    $ext = new class implements BlogrExtension {
        public function getId(): string { return 'dependent'; }
        public function getName(): string { return 'Dependent'; }
        public function getDescription(): string { return ''; }
        public function getVersion(): string { return '1.0.0'; }
        public function getAuthor(): string { return ''; }
        public function getHomepage(): ?string { return null; }
        public function getDependencies(): array { return ['blogr-core', 'blogr-gdpr']; }
    };

    expect($ext->getDependencies())->toBe(['blogr-core', 'blogr-gdpr']);
});

test('extension can have null homepage', function () {
    $ext = new class implements BlogrExtension {
        public function getId(): string { return 'no-homepage'; }
        public function getName(): string { return 'No Homepage'; }
        public function getDescription(): string { return ''; }
        public function getVersion(): string { return '1.0.0'; }
        public function getAuthor(): string { return ''; }
        public function getHomepage(): ?string { return null; }
        public function getDependencies(): array { return []; }
    };

    expect($ext->getHomepage())->toBeNull();
});

// ─── CORE EXTENSION REGISTRATION ──────────────────────

test('blogr core is registered as an extension', function () {
    $registry = app(ExtensionRegistry::class);

    expect($registry->has('blogr-core'))->toBeTrue();
});

test('blogr core extension has correct id', function () {
    $registry = app(ExtensionRegistry::class);
    $core = $registry->get('blogr-core');

    expect($core->getId())->toBe('blogr-core');
});

test('blogr core extension has a version', function () {
    $registry = app(ExtensionRegistry::class);
    $core = $registry->get('blogr-core');

    expect($core->getVersion())->not->toBeEmpty();
});

test('blogr core extension has no dependencies', function () {
    $registry = app(ExtensionRegistry::class);
    $core = $registry->get('blogr-core');

    expect($core->getDependencies())->toBe([]);
});

test('blogr core extension has a name', function () {
    $registry = app(ExtensionRegistry::class);
    $core = $registry->get('blogr-core');

    expect($core->getName())->toBe('Blogr Core');
});

test('blogr core extension has an author', function () {
    $registry = app(ExtensionRegistry::class);
    $core = $registry->get('blogr-core');

    expect($core->getAuthor())->not->toBeEmpty();
});
