<?php

namespace DjurovicIgoor\LaraFiles\Contracts;

interface FileHashNameInterface
{
    public function generateHashName(): void;

    public function getHashName(): string;
}
