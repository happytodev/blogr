<?php

namespace Happytodev\Blogr\Commands;

use Illuminate\Console\Command;

class BlogrCommand extends Command
{
    public $signature = 'blogr';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
