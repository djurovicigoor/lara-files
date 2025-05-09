<?php

declare(strict_types=1);

namespace DjurovicIgoor\LaraFiles;

use DjurovicIgoor\LaraFiles\Models\LaraFile;
use DjurovicIgoor\LaraFiles\Models\Observers\LaraFileObserver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaraFileServiceProvider extends PackageServiceProvider
{
    /**
     * @param  Package  $package
     *
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package->name('lara-files')->hasConfigFile('lara-files')->hasMigration('create_lara_files_table')->hasMigration('update_lara_files_to_v2_table');
    }

    /**
     * @return void
     */
    public function packageBooted(): void
    {
        //        $laraFileClass = config('config_key', LaraFile::class);
        //        $laraFileObserverClass = config('config_key', LaraFileObserver::class);
        //
        //        $laraFileClass::observe(new $laraFileObserverClass);
        LaraFile::observe(LaraFileObserver::class);
    }
}
