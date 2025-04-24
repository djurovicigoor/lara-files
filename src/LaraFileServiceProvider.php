<?php

declare(strict_types=1);

namespace DjurovicIgoor\LaraFiles;

use DjurovicIgoor\LaraFiles\Models\LaraFile;
use DjurovicIgoor\LaraFiles\Models\Observers\LaraFileObserver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaraFileServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('lara-files')->hasConfigFile('lara-files')->hasMigration('create_lara_files_table');
    }

    public function packageBooted(): void
    {
        //        $laraFileClass = config('config_key', LaraFile::class);
        //        $laraFileObserverClass = config('config_key', LaraFileObserver::class);
        //
        //        $laraFileClass::observe(new $laraFileObserverClass);
        LaraFile::observe(LaraFileObserver::class);
    }
}
