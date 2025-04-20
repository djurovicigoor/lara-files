<?php

namespace DjurovicIgoor\LaraFiles\Exceptions;

use RuntimeException;

final class VisibilityIsNotValidException extends RuntimeException
{
	/**
	 * @var string
	 */
	private const MESSAGE = 'Invalid visibility provided. Expected either "public" or "private", received %s';
	
	/**
	 * Creates a new Exception instance.
	 */
	public function __construct(string $visibility)
	{
		parent::__construct(sprintf(self::MESSAGE, $visibility));
	}
}