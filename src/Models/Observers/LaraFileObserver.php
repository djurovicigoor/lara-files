<?php

namespace DjurovicIgoor\LaraFiles\Models\Observers;

use DjurovicIgoor\LaraFiles\Models\LaraFile;
use Illuminate\Support\Str;

class LaraFileObserver
{
    public function creating(LaraFile $laraFile): void
    {
        $laraFile->id = Str::uuid()->toString();
    }
}
