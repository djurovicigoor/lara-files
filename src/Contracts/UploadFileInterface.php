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
     * Retrieve a hash name for the file.
     *
     * @return string
     */
    public function getHashName(): string;

    /**
     * Retrieve file binary representation for upload
     *
     * @throws FileNotFoundException
     */
    public function getFileForUpload(): string;
}
