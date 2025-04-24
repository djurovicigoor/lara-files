<?php

namespace DjurovicIgoor\LaraFiles\Models\Observers;

use Illuminate\Support\Str;
use DjurovicIgoor\LaraFiles\Models\LaraFile;

class LaraFileObserver
{
    public function creating(LaraFile $laraFile): void
    {
        if (empty($laraFile->id)) {
            $laraFile->id = Str::uuid()->toString();
        }
        
        if (is_null($laraFile->order)) {
            $laraFile->setHighestOrderNumber();
        }
    }
}