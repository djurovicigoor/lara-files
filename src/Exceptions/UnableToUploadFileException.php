<?php

namespace DjurovicIgoor\LaraFiles\Exceptions;

use RuntimeException;

final class UnableToUploadFileException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Unable to upload file.', 500);
    }
}
