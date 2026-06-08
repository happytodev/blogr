<?php

namespace Happytodev\Blogr\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AnalyticsScriptRendered
{
    use Dispatchable;

    public function __construct(
        public string $provider,
        public bool $hasConsent,
    ) {}
}
