<?php

namespace DjurovicIgoor\LaraFiles\Contracts;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

interface UploadFileInterface
{
	/**
	 * @return string
	 */
	public function getFileExtension(): string;
	
	/**
	 * @return string|null
	 */
	public function getFileOriginalName(): ?string;
	
	/**
	 * @return string
	 */
	public function getHashName(): string;
	
	/**
	 * @return string
	 * @throws FileNotFoundException
	 */
	public function getFileForUpload(): string;
}