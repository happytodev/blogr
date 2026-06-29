<?php

namespace Happytodev\Blogr\Services;

use Closure;

class LinkTypeRegistry
{
    /** @var array<string, array{label: string, resolver: Closure}> */
    protected array $types = [];

    public function register(string $key, string $label, Closure $resolver): void
    {
        $this->types[$key] = compact('label', 'resolver');
    }

    public function getOptions(): array
    {
        return array_map(fn (array $type): string => $type['label'], $this->types);
    }

    public function resolve(string $key): ?string
    {
        if (! isset($this->types[$key])) {
            return null;
        }

        return ($this->types[$key]['resolver'])();
    }

    public function has(string $key): bool
    {
        return isset($this->types[$key]);
    }
}
