<?php

namespace TheJawker\Mediaux\Commands;

use Illuminate\Console\Command;

class MediauxCommand extends Command
{
    public $signature = 'mediaux';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
