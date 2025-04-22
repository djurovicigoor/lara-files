<?php

namespace DjurovicIgoor\LaraFiles\Classes;

use DjurovicIgoor\LaraFiles\Contracts\FileHashNameInterface;
use DjurovicIgoor\LaraFiles\Contracts\UploadFileInterface;
use DjurovicIgoor\LaraFiles\LaraFile;
use Illuminate\Http\UploadedFile;

abstract class AbstractFile implements FileHashNameInterface, UploadFileInterface
{
    protected ?string $hashName = null;

    protected UploadedFile|LaraFile|string $file;
}
