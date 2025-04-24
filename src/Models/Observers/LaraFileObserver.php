<?php

namespace DjurovicIgoor\LaraFiles\Models\Observers;

use DjurovicIgoor\LaraFiles\Models\LaraFile;
use Illuminate\Support\Str;

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
