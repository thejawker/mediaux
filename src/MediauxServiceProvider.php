<?php

namespace Bram Veerman\Mediaux;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Bram Veerman\Mediaux\Commands\MediauxCommand;

class MediauxServiceProvider extends PackageServiceProvider
{
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
            ->hasViews()
            ->hasMigration('create_mediaux_table')
            ->hasCommand(MediauxCommand::class);
    }
}
