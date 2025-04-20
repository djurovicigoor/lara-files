<?php

namespace DjurovicIgoor\LaraFiles\Classes;

use Illuminate\Http\UploadedFile;
use DjurovicIgoor\LaraFiles\LaraFile;
use DjurovicIgoor\LaraFiles\Contracts\UploadFileInterface;
use DjurovicIgoor\LaraFiles\Contracts\FileHashNameInterface;

abstract class AbstractFile implements UploadFileInterface, FileHashNameInterface
{
	protected ?string                      $hashName = NULL;
	protected UploadedFile|LaraFile|string $file;
}