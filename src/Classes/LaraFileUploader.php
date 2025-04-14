<?php

namespace DjurovicIgoor\LaraFiles\Classes;

use Throwable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use DjurovicIgoor\LaraFiles\Contracts\UploadFileInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use DjurovicIgoor\LaraFiles\Exceptions\UnsupportedDiskAdapterException;

class LaraFileUploader
{
	private UploadFileInterface $uploadedFile;
	private ?string             $disk  = NULL;
	private ?string             $type  = NULL;
	private ?Model              $model = NULL;
	
	/**
	 * @throws Throwable
	 */
	public function __construct($uploadedFile, $fileUploaderType = 'http_file')
	{
		if ($fileUploaderType === 'http_file') {
			$this->uploadedFile = new HttpFile($uploadedFile);
		}
		if ($fileUploaderType === 'base64_file') {
			$this->uploadedFile = new Base64File($uploadedFile);
		}
	}
	
	/**
	 * @throws Throwable
	 */
	public function setDisk(string $disk): static
	{
		throw_if(!array_key_exists($disk, config('filesystems.disks')), new UnsupportedDiskAdapterException($disk), NULL);
		
		$this->disk = $disk;
		
		return $this;
	}
	
	/**
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setType(string $type): static
	{
		$this->type = $type;
		
		return $this;
	}
	
	/**
	 * @param Model $model
	 *
	 * @return $this
	 */
	public function setModel(Model $model): static
	{
		$this->model = $model;
		
		return $this;
	}
	
	/**
	 * @throws FileNotFoundException|Throwable
	 */
	public function upload()
	{
		$fileExtension = $this->uploadedFile->getFileExtension();
		$hashName      = $this->uploadedFile->getHashName();
		$path          = 'lara-files/';
		
		if ($this->model instanceof Model) {
			$path .= strtolower(class_basename($this->model)).'/';
		} else {
			$path .= 'tmp/';
		}
		
		$fullPath = "$path$hashName.$fileExtension";
		
		if (Storage::disk($this->disk)->put($fullPath, $this->uploadedFile->getFileForUpload())) {
			Storage::disk($this->disk)->setVisibility($fullPath, $this->disk === 'local' ? 'private' : 'public');
		}
	}
}