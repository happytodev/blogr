<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Happytodev\Blogr\Concerns\RegistersLinkTypes;
use Happytodev\Blogr\Contracts\BlogrExtension;
use Happytodev\Blogr\Contracts\ExportableExtension;
use Happytodev\Blogr\Services\BlogrExportService;
use Happytodev\Blogr\Services\BlogrImportService;
use Happytodev\Blogr\Services\ExtensionRegistry;
use Happytodev\Blogr\Services\LinkTypeRegistry;

it('export includes extension_states section', function () {
    $registry = app(ExtensionRegistry::class);
    $ext = new class implements BlogrExtension
    {
        use RegistersLinkTypes;

        public function getId(): string
        {
            return 'test-ext';
        }

        public function getName(): string
        {
            return 'Test Ext';
        }

        public function getDescription(): string
        {
            return '';
        }

        public function getVersion(): string
        {
            return '1.0.0';
        }

        public function getAuthor(): string
        {
            return 'Test';
        }

        public function getHomepage(): ?string
        {
            return null;
        }

        public function getDependencies(): array
        {
            return [];
        }
    };
    $registry->register($ext);
    $registry->disable('test-ext');
    $data = app(BlogrExportService::class)->export();
    expect($data['extension_states'])->toHaveKey('test-ext');
    expect($data['extension_states']['test-ext']['disabled_at'])->not->toBeNull();
});
it('export includes extensions section with exportable data', function () {
    $registry = app(ExtensionRegistry::class);
    $ext = new class implements ExportableExtension
    {
        public function getId(): string
        {
            return 'export-plugin';
        }

        public function getName(): string
        {
            return 'Export Plugin';
        }

        public function getDescription(): string
        {
            return '';
        }

        public function getVersion(): string
        {
            return '2.0.0';
        }

        public function getAuthor(): string
        {
            return 'Dev';
        }

        public function getHomepage(): ?string
        {
            return null;
        }

        public function getDependencies(): array
        {
            return [];
        }

        public function getSettingsUrl(): ?string
        {
            return null;
        }

        public function registerExtension(ExtensionRegistry $registry): void {}

        public function registerLinkTypes(LinkTypeRegistry $registry): void {}

        public function getExportKey(): string
        {
            return 'export-plugin';
        }

        public function getExportData(): array
        {
            return ['settings' => ['theme' => 'dark'], 'items' => [1, 2, 3]];
        }

        public function importData(array $data, array $options): array
        {
            return ['imported' => 0, 'skipped' => 0];
        }

        public function getExportMediaPaths(): array
        {
            return [];
        }
    };
    $registry->register($ext);
    $data = app(BlogrExportService::class)->export();
    expect($data['extensions']['export-plugin']['data']['settings']['theme'])->toBe('dark');
});
it('import restores extension states from backup', function () {
    $registry = app(ExtensionRegistry::class);
    $ext = new class implements BlogrExtension
    {
        use RegistersLinkTypes;

        public function getId(): string
        {
            return 'state-test';
        }

        public function getName(): string
        {
            return 'State Test';
        }

        public function getDescription(): string
        {
            return '';
        }

        public function getVersion(): string
        {
            return '1.0.0';
        }

        public function getAuthor(): string
        {
            return 'Dev';
        }

        public function getHomepage(): ?string
        {
            return null;
        }

        public function getDependencies(): array
        {
            return [];
        }
    };
    $registry->register($ext);
    app(BlogrImportService::class)->import([
        'version' => '1.0.0', 'exported_at' => now()->toIso8601String(),
        'posts' => [], 'series' => [], 'categories' => [], 'tags' => [],
        'extension_states' => ['state-test' => ['disabled_at' => '2026-07-01T12:00:00+00:00']],
    ]);
    expect($registry->isEnabled('state-test'))->toBeFalse();
});
it('import calls importData on registered exportable extensions', function () {
    $registry = app(ExtensionRegistry::class);
    $tracker = new stdClass;
    $tracker->called = false;
    $ext = new class($tracker) implements ExportableExtension
    {
        public function __construct(private stdClass $tracker) {}

        public function getId(): string
        {
            return 'import-call-test';
        }

        public function getName(): string
        {
            return 'Import Call Test';
        }

        public function getDescription(): string
        {
            return '';
        }

        public function getVersion(): string
        {
            return '1.0.0';
        }

        public function getAuthor(): string
        {
            return 'Dev';
        }

        public function getHomepage(): ?string
        {
            return null;
        }

        public function getDependencies(): array
        {
            return [];
        }

        public function getSettingsUrl(): ?string
        {
            return null;
        }

        public function registerExtension(ExtensionRegistry $registry): void {}

        public function registerLinkTypes(LinkTypeRegistry $registry): void {}

        public function getExportKey(): string
        {
            return 'import-call-test';
        }

        public function getExportData(): array
        {
            return [];
        }

        public function importData(array $data, array $options): array
        {
            $this->tracker->called = true;

            return ['imported' => 2, 'skipped' => 0];
        }

        public function getExportMediaPaths(): array
        {
            return [];
        }
    };
    $registry->register($ext);
    $result = app(BlogrImportService::class)->import([
        'version' => '1.0.0', 'exported_at' => now()->toIso8601String(),
        'posts' => [], 'series' => [], 'categories' => [], 'tags' => [],
        'extensions' => ['import-call-test' => ['version' => '1.0.0', 'data' => ['foo' => 'bar'], 'media_files' => []]],
    ]);
    expect($result['success'])->toBeTrue();
    expect($tracker->called)->toBeTrue();
});
it('export includes format_version field', function () {
    expect(app(BlogrExportService::class)->export())->toHaveKey('format_version');
});
it('import works without extension_states section (backward compat)', function () {
    expect(app(BlogrImportService::class)->import([
        'version' => '1.0.0', 'exported_at' => now()->toIso8601String(),
        'posts' => [], 'series' => [], 'categories' => [], 'tags' => [],
    ])['success'])->toBeTrue();
});
