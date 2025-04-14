<?php

declare(strict_types=1);

namespace DjurovicIgoor\LaraFiles;

use Illuminate\Support\ServiceProvider;
use DjurovicIgoor\LaraFiles\Console\Commands\SetupLaraFiles;

class LaraFilesProvider extends ServiceProvider
{
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->publishes([
			__DIR__ . '/../config/lara-files.php' => config_path('lara-files.php'),
		], 'config');
		
		$this->publishes([
			__DIR__ . '/database/migrations/' => base_path('/database/migrations'),
		], 'migrations');
	}
	
	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->commands([
			SetupLaraFiles::class,
		]);
		
		$this->mergeConfigFrom(__DIR__ . '/../config/lara-files.php', 'lara-files');
	}
}