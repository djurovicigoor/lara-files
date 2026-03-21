<?php

namespace DjurovicIgoor\LaraFiles\Classes;

use Illuminate\Http\UploadedFile;
use DjurovicIgoor\LaraFiles\Models\LaraFile;
use DjurovicIgoor\LaraFiles\Contracts\UploadFileInterface;
use DjurovicIgoor\LaraFiles\Contracts\FileHashNameInterface;

abstract class AbstractFile implements FileHashNameInterface, UploadFileInterface
{
    protected ?string $hashName = null;

    protected UploadedFile|LaraFile|string $file;
}
