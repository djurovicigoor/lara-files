<?php

namespace DjurovicIgoor\LaraFiles\Console\Commands;

use Storage;
use Illuminate\Console\Command;

class SetupLaraFiles extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lara-files:setup';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Setup lara files folders and permissions.';
	
	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		if (Storage::disk('local')->directoryMissing('lara-files')) {
			Storage::disk('local')->makeDirectory('lara-files');
		}
		if (Storage::disk('public')->directoryMissing('lara-files')) {
			Storage::disk('public')->makeDirectory('lara-files');
		}
	}
}