<?php

namespace Happytodev\Blogr\Services;

use Happytodev\Blogr\Contracts\BlogrExtension;

class ExtensionRegistry
{
    /** @var array<string, BlogrExtension> */
    protected array $extensions = [];

    /**
     * Register an extension.
     */
    public function register(BlogrExtension $extension): void
    {
        $this->extensions[$extension->getId()] = $extension;
    }

    /**
     * Get all registered extensions.
     * @return array<string, BlogrExtension>
     */
    public function getAll(): array
    {
        return $this->extensions;
    }

    /**
     * Get a specific extension by ID.
     */
    public function get(string $id): ?BlogrExtension
    {
        return $this->extensions[$id] ?? null;
    }

    /**
     * Check if an extension is registered.
     */
    public function has(string $id): bool
    {
        return isset($this->extensions[$id]);
    }

    /**
     * Get the total count of registered extensions.
     */
    public function count(): int
    {
        return count($this->extensions);
    }
}
