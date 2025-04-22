<?php

namespace DjurovicIgoor\LaraFiles\Traits;

use Illuminate\Support\Str;

trait HashNameTrait
{
    public function getHashName(): string
    {
        return $this->hashName;
    }

    public function generateHashName(): void
    {
        $this->hashName = Str::uuid7()->toString();
    }
}
