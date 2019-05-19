<?php

namespace DjurovicIgoor\LaraFiles\Exceptions;

use Throwable;

class UnsupportedDiskAdapterException extends \Exception
{
    /**
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $message = 'Disk driver is unsupported!',
        $code = 400,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
