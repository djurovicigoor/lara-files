<?php

namespace DjurovicIgoor\LaraFiles\Models\Observers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use DjurovicIgoor\LaraFiles\Models\LaraFile;

class LaraFileObserver
{
    /**
     * @param  LaraFile  $laraFile
     * @return void
     */
    public function creating(LaraFile $laraFile): void
    {
        if (empty($laraFile->id)) {
            $laraFile->id = Str::uuid()->toString();
        }

        if (is_null($laraFile->order)) {
            $laraFile->setHighestOrderNumber();
        }
    }

    /**
     * @param  LaraFile  $laraFile
     * @return void
     */
    public function deleting(LaraFile $laraFile): void
    {
        if (Storage::disk($laraFile->disk)->exists($laraFile->fullPath)) {
            Storage::disk($laraFile->disk)->delete($laraFile->fullPath);
        }
    }
}
