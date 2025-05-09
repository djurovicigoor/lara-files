<?php

namespace DjurovicIgoor\LaraFiles\Classes;

use DjurovicIgoor\LaraFiles\Exceptions\FileIsNotInstanceOfLaraFileModelException;
use DjurovicIgoor\LaraFiles\Models\LaraFile;
use DjurovicIgoor\LaraFiles\Traits\HashNameTrait;
use Throwable;

class AnotherLaraFile extends AbstractFile
{
    use HashNameTrait;

    /**
     * @param  LaraFile  $file
     *
     * @throws FileIsNotInstanceOfLaraFileModelException|Throwable
     */
    public function __construct($file)
    {
        \throw_if(! $file instanceof LaraFile, new FileIsNotInstanceOfLaraFileModelException());

        $this->generateHashName();

        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return $this->file->extension;
    }

    /**
     * @return string
     */
    public function getFileOriginalName(): string
    {
        return $this->file->name;
    }

    /**
     * @return string
     */
    public function getFileForUpload(): string
    {
        return $this->file->getContents();
    }
}
