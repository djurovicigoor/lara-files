<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 20.11.17.
 * Time: 19.24
 */

namespace DjurovicIgoor\LaraFiles;

use DjurovicIgoor\LaraFiles\Commands\PreInstallCheck;
use Illuminate\Support\ServiceProvider;

class LaraFilesProvider extends ServiceProvider {
    
    /**
     * Bootstrap the application services.
     * @return void
     */
    public function boot() {
        
        $this->publishes([
            __DIR__ . '/../config/lara-files.php' => config_path('lara-files.php'),
        ], 'config');
        $this->publishes([
            __DIR__ . '/database/migrations/' => base_path('/database/migrations'),
        ], 'migrations');
    }
    
    /**
     * Register the application services.
     * @return void
     */
    public function register() {
        
        $this->commands([
            PreInstallCheck::class,
        ]);
        $this->mergeConfigFrom(__DIR__ . '/../config/lara-files.php', 'lara-files');
    }
    
    /**
     * {@inheritdoc}
     */
    public function provides() {
        
        return [
            PreInstallCheck::class,
        ];
    }
}
