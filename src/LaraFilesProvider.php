<?php

declare(strict_types=1);

namespace DjurovicIgoor\LaraFiles;

use Illuminate\Support\ServiceProvider;

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
		$this->commands([]);
		
		$this->mergeConfigFrom(__DIR__ . '/../config/lara-files.php', 'lara-files');
	}
}