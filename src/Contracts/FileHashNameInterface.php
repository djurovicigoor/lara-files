<?php

namespace DjurovicIgoor\LaraFiles\Contracts;

/**
 * Represents a contract for generating and retrieving a hashed filename.
 */
interface FileHashNameInterface
{
    public function generateHashName(): void;

    public function getHashName(): string;
}
