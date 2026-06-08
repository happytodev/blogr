<?php

namespace Happytodev\Blogr\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ContactFormSubmitted
{
    use Dispatchable;

    public function __construct(
        public string $name,
        public string $email,
        public string $subject,
        public string $message,
    ) {}
}
