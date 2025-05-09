<?php

namespace DjurovicIgoor\LaraFiles\Models\Observers;

use DjurovicIgoor\LaraFiles\Models\LaraFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LaraFileObserver
{
    /**
     * @param  LaraFile  $laraFile
     *
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
     *
     * @return void
     */
    public function deleting(LaraFile $laraFile): void
    {
        if (Storage::disk($laraFile->disk)->exists($laraFile->fullPath)) {
            Storage::disk($laraFile->disk)->delete($laraFile->fullPath);
        }
    }
}
