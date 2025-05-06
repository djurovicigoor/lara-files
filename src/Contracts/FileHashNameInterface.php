<?php

namespace DjurovicIgoor\LaraFiles\Contracts;

/**
 * Represents a contract for generating and retrieving a hashed filename.
 */
interface FileHashNameInterface
{
    /**
     * @return void
     */
    public function generateHashName(): void;

    /**
     * @return string
     */
    public function getHashName(): string;
}
