<?php

namespace Happytodev\Blogr\Contracts;

interface ExportableExtension extends BlogrExtension
{
    public function getExportKey(): string;

    /** @return array<string, mixed> */
    public function getExportData(): array;

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     * @return array{imported: int, skipped: int}
     */
    public function importData(array $data, array $options): array;

    /** @return array<int, string> */
    public function getExportMediaPaths(): array;
}
