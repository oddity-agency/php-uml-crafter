<?php

namespace WongDoody\PhpUmlCrafter;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use WongDoody\PhpUmlCrafter\Commands\PhpUmlCrafterScanner;
use WongDoody\PhpUmlCrafter\Commands\PhpUmlCrafterGenerator;

class PhpUmlCrafterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('php-uml-crafter')
            ->hasConfigFile()
            ->hasCommand(PhpUmlCrafterScanner::class)
            ->hasCommand(PhpUmlCrafterGenerator::class);
    }
}
