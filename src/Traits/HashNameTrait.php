<?php

namespace DjurovicIgoor\LaraFiles\Traits;

use Illuminate\Support\Str;

trait HashNameTrait
{
	/**
	 * @return string
	 */
	public function getHashName(): string
	{
		return $this->hashName;
	}
	
	/**
	 * @return void
	 */
	public function generateHashName(): void
	{
		$this->hashName = Str::uuid7()->toString();
	}
}