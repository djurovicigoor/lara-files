<?php

namespace DjurovicIgoor\LaraFiles\Classes;

use DjurovicIgoor\LaraFiles\Exceptions\FileIsNotInstanceOfUploadedFileClassException;
use DjurovicIgoor\LaraFiles\Traits\HashNameTrait;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Throwable;

class HttpFile extends AbstractFile
{
    use HashNameTrait;

    /**
     * @throws FileIsNotInstanceOfUploadedFileClassException|Throwable
     */
    public function __construct($file)
    {
        \throw_if(! $file instanceof UploadedFile, new FileIsNotInstanceOfUploadedFileClassException());

        $this->generateHashName();

        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return $this->file->extension();
    }

    /**
     * @return string
     */
    public function getFileOriginalName(): string
    {
        return pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getFileForUpload(): string
    {
        return File::get($this->file);
    }
}
