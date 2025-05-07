<?php

namespace DjurovicIgoor\LaraFiles\Contracts;

/**
 * Represents a contract for generating and retrieving a hashed filename.
 */
interface FileHashNameInterface
{
    /**
     * Generate UUID Hash for file ame
     *
     * @return void
     */
    public function generateHashName(): void;

    /**
     * Retrieve a hash name for the file
     *
     * @return string
     */
    public function getHashName(): string;
}
