<?php

namespace DjurovicIgoor\LaraFiles\Contracts;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

interface UploadFileInterface
{
    /**
     * Retrieves the file extension.
     *
     * @return string
     */
    public function getFileExtension(): string;

    /**
     * Retrieves the original name of the file.
     *
     * @return string|null
     */
    public function getFileOriginalName(): ?string;

    /**
     * Generate and retrieve a hash name for a file or entity.
     *
     * @return string
     */
    public function getHashName(): string;

    /**
     * @throws FileNotFoundException
     */
    public function getFileForUpload(): string;
}
