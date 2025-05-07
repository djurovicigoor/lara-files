<?php

namespace DjurovicIgoor\LaraFiles\Classes;

use DjurovicIgoor\LaraFiles\Contracts\FileHashNameInterface;
use DjurovicIgoor\LaraFiles\Contracts\UploadFileInterface;
use DjurovicIgoor\LaraFiles\Models\LaraFile;
use Illuminate\Http\UploadedFile;

/**
 * Abstract class representing a file and its related functionality.
 * Implements interfaces for hashing file names and file upload management.
 */
abstract class AbstractFile implements FileHashNameInterface, UploadFileInterface
{
    protected ?string $hashName = null;

    protected UploadedFile|LaraFile|string $file;
}
