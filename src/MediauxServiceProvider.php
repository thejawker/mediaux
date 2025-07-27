<?php

namespace TheJawker\Mediaux;

use ProtoneMedia\LaravelFFMpeg\Support\ServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TheJawker\Mediaux\Commands\CleanExpiredMedia;

class MediauxServiceProvider extends PackageServiceProvider
{
    public function packageRegistered(): void
    {
        $this->app->register(ServiceProvider::class, true);

        if (!config('mediaux.disable_routes')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/media.php');
        }
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('mediaux')
            ->hasConfigFile()
            ->hasMigrations([
                'create_media_conversions_table',
                'create_media_items_table',
                'create_mediables_table',
            ])
            ->hasCommands(CleanExpiredMedia::class);
    }
}
