<?php

namespace DjurovicIgoor\LaraFiles\Exceptions;

use RuntimeException;

final class UnsupportedDiskAdapterException extends RuntimeException
{
    /**
     * @var string
     */
    private const MESSAGE = 'Disk %s is not supported! Please check your "config/filesystems.php" for available disk drivers.';

    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $disk)
    {
        parent::__construct(sprintf(self::MESSAGE, $disk));
    }
}
