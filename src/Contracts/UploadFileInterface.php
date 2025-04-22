<?php

namespace DjurovicIgoor\LaraFiles\Contracts;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

interface UploadFileInterface
{
    public function getFileExtension(): string;

    public function getFileOriginalName(): ?string;

    public function getHashName(): string;

    /**
     * @throws FileNotFoundException
     */
    public function getFileForUpload(): string;
}
