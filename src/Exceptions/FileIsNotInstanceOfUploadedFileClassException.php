<?php

namespace DjurovicIgoor\LaraFiles\Exceptions;

use RuntimeException;

final class FileIsNotInstanceOfUploadedFileClassException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('File must be instance of Illuminate\Http\UploadedFile class.', 500);
    }
}
