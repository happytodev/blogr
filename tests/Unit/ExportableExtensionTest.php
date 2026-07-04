<?php

use Happytodev\Blogr\Concerns\RegistersLinkTypes;
use Happytodev\Blogr\Contracts\BlogrExtension;
use Happytodev\Blogr\Contracts\ExportableExtension;
use Happytodev\Blogr\Services\ExtensionRegistry;
use Happytodev\Blogr\Services\LinkTypeRegistry;

test('ExportableExtension interface exists', function () {
    expect(interface_exists(ExportableExtension::class))->toBeTrue();
});
test('ExportableExtension extends BlogrExtension', function () {
    $reflection = new ReflectionClass(ExportableExtension::class);
    expect($reflection->getInterfaceNames())->toContain(BlogrExtension::class);
});
test('ExportableExtension has required methods', function () {
    $methods = array_map(fn ($m) => $m->getName(), (new ReflectionClass(ExportableExtension::class))->getMethods());
    expect($methods)->toContain('getExportKey', 'getExportData', 'importData', 'getExportMediaPaths');
});
test('extension implementing ExportableExtension can be registered', function () {
    $registry = new ExtensionRegistry;
    $ext = new class implements ExportableExtension
    {
        public function getId(): string
        {
            return 'exportable-test';
        }

        public function getName(): string
        {
            return 'Exportable Test';
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

        public function getSettingsUrl(): ?string
        {
            return null;
        }

        public function registerExtension(ExtensionRegistry $registry): void {}

        public function registerLinkTypes(LinkTypeRegistry $registry): void {}

        public function getExportKey(): string
        {
            return 'exportable-test';
        }

        public function getExportData(): array
        {
            return ['foo' => 'bar'];
        }

        public function importData(array $data, array $options): array
        {
            return ['imported' => 1, 'skipped' => 0];
        }

        public function getExportMediaPaths(): array
        {
            return ['images/test.jpg'];
        }
    };
    $registry->register($ext);
    expect($registry->has('exportable-test'))->toBeTrue();
});
test('ExtensionRegistry can list exportable extensions', function () {
    $registry = new ExtensionRegistry;
    $exportable = new class implements ExportableExtension
    {
        public function getId(): string
        {
            return 'exportable-one';
        }

        public function getName(): string
        {
            return 'Exportable One';
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

        public function getSettingsUrl(): ?string
        {
            return null;
        }

        public function registerExtension(ExtensionRegistry $registry): void {}

        public function registerLinkTypes(LinkTypeRegistry $registry): void {}

        public function getExportKey(): string
        {
            return 'exportable-one';
        }

        public function getExportData(): array
        {
            return [];
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
    $nonExportable = new class implements BlogrExtension
    {
        use RegistersLinkTypes;

        public function getId(): string
        {
            return 'non-exportable';
        }

        public function getName(): string
        {
            return 'Non Exportable';
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
    $registry->register($exportable);
    $registry->register($nonExportable);
    expect($registry->getExportableExtensions())->toHaveCount(1)->toHaveKey('exportable-one');
});
