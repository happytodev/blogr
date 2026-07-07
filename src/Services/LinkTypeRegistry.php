<?php

namespace Happytodev\Blogr\Services;

use Closure;

class LinkTypeRegistry
{
    /** @var array<string, array{label: string, resolver: Closure, fieldFactory: ?Closure}> */
    protected array $types = [];

    public function register(string $key, string $label, Closure $resolver, ?Closure $fieldFactory = null): void
    {
        $this->types[$key] = compact('label', 'resolver', 'fieldFactory');
    }

    public function getOptions(): array
    {
        return array_map(fn (array $type): string => $type['label'], $this->types);
    }

    public function resolve(string $key, mixed $context = null): ?string
    {
        if (! isset($this->types[$key])) {
            return null;
        }

        return ($this->types[$key]['resolver'])($context);
    }

    public function has(string $key): bool
    {
        return isset($this->types[$key]);
    }

    /** @return array<string, Closure> */
    public function getFieldFactories(): array
    {
        return array_reduce(
            array_keys($this->types),
            function (array $carry, string $key): array {
                if ($this->types[$key]['fieldFactory'] !== null) {
                    $carry[$key] = $this->types[$key]['fieldFactory'];
                }

                return $carry;
            },
            []
        );
    }
}
