<?php

namespace Happytodev\Blogr\Contracts;

interface BlogrExtension
{
    /**
     * Unique identifier for the extension (e.g., 'blogr-gdpr').
     */
    public function getId(): string;

    /**
     * Human-readable name (e.g., 'GDPR Compliance').
     */
    public function getName(): string;

    /**
     * Short description of what the extension does.
     */
    public function getDescription(): string;

    /**
     * Current version (e.g., '1.0.0').
     */
    public function getVersion(): string;

    /**
     * Author name or organization.
     */
    public function getAuthor(): string;

    /**
     * Optional URL to the extension's homepage or repository.
     */
    public function getHomepage(): ?string;

    /**
     * Optional array of extension IDs that this extension depends on.
     *
     * @return string[]
     */
    public function getDependencies(): array;
}
