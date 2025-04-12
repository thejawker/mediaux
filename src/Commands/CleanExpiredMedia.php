<?php

namespace TheJawker\Mediaux\Commands;

use Illuminate\Console\Command;
use TheJawker\Mediaux\Models\MediaItem;

class CleanExpiredMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-expired-media';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans media that has expired. This can be because the media was never used.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $expired = MediaItem::expired();
        $this->info("Cleaning up {$expired->count()} expired media items.");
        $this->withProgressBar($expired->get(), function (MediaItem $mediaItem) {
            $mediaItem->deleteWithDependencies();
        });
    }
}
