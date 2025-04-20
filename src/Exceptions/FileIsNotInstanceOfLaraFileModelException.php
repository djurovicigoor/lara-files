<?php

namespace DjurovicIgoor\LaraFiles\Exceptions;

use RuntimeException;

final class FileIsNotInstanceOfLaraFileModelException extends RuntimeException
{
	public function __construct()
	{
		parent::__construct('File must be instance of DjurovicIgoor\LaraFiles\LaraFile class.', 500);
	}
}