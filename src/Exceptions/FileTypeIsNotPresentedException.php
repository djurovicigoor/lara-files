<?php

namespace DjurovicIgoor\LaraFiles\Exceptions;

use RuntimeException;

final class FileTypeIsNotPresentedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('File type is not presented.', 500);
    }
}
