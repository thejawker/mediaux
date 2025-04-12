<?php

namespace TheJawker\Mediaux;

use ProtoneMedia\LaravelFFMpeg\Support\ServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TheJawker\Mediaux\Commands\CleanExpiredMedia;

class MediauxServiceProvider extends PackageServiceProvider
{
    public function packageRegistered()
    {
        $this->app->register(ServiceProvider::class);
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
            ->hasRoute('media')
            ->hasMigration('create_mediaux_table')
            ->hasCommands(CleanExpiredMedia::class);
    }
}
