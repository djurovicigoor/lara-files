<?php

namespace DjurovicIgoor\LaraFiles\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PreInstallCheck extends Command {
    
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'lara-files:pre-install';
    
    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Lara-files pre install sc';
    
    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct() {
        
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle() {
        $driver = 'DOSpaces';
        if (!Storage::disk($driver)->exists('lara-files')) {
            Storage::disk($driver)->makeDirectory('lara-files');
            if (Storage::disk($driver)->exists('lara-files')) {
                $this->info('Package folder successfully created.');
            } else {
                $this->error('Package folder not created!');
            }
        } else{
            $this->info('Package folder already exist!¬');
    
        }
    }
}
