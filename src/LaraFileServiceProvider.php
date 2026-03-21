<?php

declare(strict_types=1);

namespace DjurovicIgoor\LaraFiles;

use Spatie\LaravelPackageTools\Package;
use DjurovicIgoor\LaraFiles\Models\LaraFile;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use DjurovicIgoor\LaraFiles\Models\Observers\LaraFileObserver;

class LaraFileServiceProvider extends PackageServiceProvider
{
    /**
     * @param  Package  $package
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
        LaraFile::observe(LaraFileObserver::class);
    }
}
