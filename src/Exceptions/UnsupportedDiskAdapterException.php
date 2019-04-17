<?php

namespace DjurovicIgoor\LaraFiles\Exceptions;

class UnsupportedDiskAdapterException extends \Exception {
    
    /**
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $message = 'Disk driver is unspporeted!',
        $code = 400,
        Throwable $previous = NULL
    ) {
        
        parent::__construct($message, $code, $previous);
    }
}
