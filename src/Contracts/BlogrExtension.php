<?php

namespace Happytodev\Blogr\Contracts;

use Happytodev\Blogr\Services\ExtensionRegistry;
use Happytodev\Blogr\Services\LinkTypeRegistry;

interface BlogrExtension
{
    public function getId(): string;

    public function getName(): string;

    public function getDescription(): string;

    public function getVersion(): string;

    public function getAuthor(): string;

    public function getHomepage(): ?string;

    public function getDependencies(): array;

    public function getSettingsUrl(): ?string;

    public function registerExtension(ExtensionRegistry $registry): void;

    public function registerLinkTypes(LinkTypeRegistry $registry): void;
}
