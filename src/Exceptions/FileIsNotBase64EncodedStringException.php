<?php

namespace DjurovicIgoor\LaraFiles\Exceptions;

use RuntimeException;

final class FileIsNotBase64EncodedStringException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('File must be base64 encoded string.', 500);
    }
}
