<?php

namespace DjurovicIgoor\LaraFiles\Contracts;

interface FileHashNameInterface
{
	/**
	 * @return void
	 */
	public function generateHashName(): void;
	
	/**
	 * @return string
	 */
	public function getHashName(): string;
	
}