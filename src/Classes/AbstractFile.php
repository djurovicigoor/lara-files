<?php

namespace DjurovicIgoor\LaraFiles\Classes;

use Illuminate\Http\UploadedFile;
use DjurovicIgoor\LaraFiles\Contracts\UploadFileInterface;
use DjurovicIgoor\LaraFiles\Contracts\FileHashNameInterface;

abstract class AbstractFile implements UploadFileInterface, FileHashNameInterface
{
	protected ?string             $hashName          = NULL;
	protected ?string             $originalName      = NULL;
	protected ?string             $originalExtension = NULL;
	protected UploadedFile|string $file;
}