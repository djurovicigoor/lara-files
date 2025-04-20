<?php

namespace DjurovicIgoor\LaraFiles\Classes;

use Throwable;
use DjurovicIgoor\LaraFiles\LaraFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use DjurovicIgoor\LaraFiles\Contracts\UploadFileInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use DjurovicIgoor\LaraFiles\Exceptions\UnableToUploadFileException;
use DjurovicIgoor\LaraFiles\Exceptions\VisibilityIsNotValidException;
use DjurovicIgoor\LaraFiles\Exceptions\FileTypeIsNotPresentedException;
use DjurovicIgoor\LaraFiles\Exceptions\UnsupportedDiskAdapterException;

class LaraFileUploader
{
	/**
	 * @var UploadFileInterface
	 */
	private UploadFileInterface $uploadedFile;
	
	/**
	 * @var string|null
	 */
	private ?string $disk = NULL;
	
	/**
	 * @var string|null
	 */
	private ?string $type = NULL;
	
	/**
	 * @var string|null
	 */
	private ?string $visibility = NULL;
	
	/**
	 * @var string|null
	 */
	private ?string $description = NULL;
	/**
	 * @var string|null
	 */
	private ?string $authorId = NULL;
	/**
	 * @var string|null
	 */
	private ?string $name = NULL;
	
	/**
	 * @var Model|null
	 */
	private ?Model $model = NULL;
	
	/**
	 * @param        $uploadedFile
	 * @param string $fileUploaderType
	 *
	 * @throws Throwable
	 */
	public function __construct($uploadedFile, string $fileUploaderType = 'http_file')
	{
		if ($fileUploaderType === 'http_file') {
			$this->uploadedFile = new HttpFile($uploadedFile);
		}
		if ($fileUploaderType === 'base64_file') {
			$this->uploadedFile = new Base64File($uploadedFile);
		}
		if ($fileUploaderType === 'lara_file') {
			$this->uploadedFile = new AnotherLaraFile($uploadedFile);
		}
	}
	
	/**
	 * @param string $disk
	 *
	 * @return LaraFileUploader
	 * @throws Throwable|UnsupportedDiskAdapterException
	 */
	public function setDisk(string $disk): static
	{
		throw_if(!array_key_exists($disk, config('filesystems.disks')), new UnsupportedDiskAdapterException($disk));
		
		$this->disk = $disk;
		
		return $this;
	}
	
	/**
	 * @param string $type
	 *
	 * @return LaraFileUploader
	 * @throws Throwable|FileTypeIsNotPresentedException
	 */
	public function setType(string $type): static
	{
		\throw_if(!$type || $type == '', new FileTypeIsNotPresentedException());
		
		$this->type = $type;
		
		return $this;
	}
	
	/**
	 * @param string $description
	 *
	 * @return LaraFileUploader
	 */
	public function setDescription(string $description): static
	{
		$this->description = $description;
		
		return $this;
	}
	
	/**
	 * @param Model $model
	 *
	 * @return LaraFileUploader
	 */
	public function setModel(Model $model): static
	{
		$this->model = $model;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getVisibility(): string
	{
		if (!$this->visibility) {
			return \config('lara-files.visibility');
		}
		return $this->visibility;
	}
	
	/**
	 * @param string $visibility
	 *
	 * @return LaraFileUploader
	 * @throws Throwable|VisibilityIsNotValidException
	 */
	public function setVisibility(string $visibility): static
	{
		\throw_if(!in_array($visibility, ['public', 'private']), new VisibilityIsNotValidException($visibility));
		
		$this->visibility = $visibility;
		
		return $this;
	}
	
	/**
	 * @param string|int $authorId
	 *
	 * @return LaraFileUploader
	 */
	public function setAuthorId(string|int $authorId): static
	{
		$this->authorId = $authorId;
		return $this;
	}
	
	/**
	 * @param string $name
	 *
	 * @return LaraFileUploader
	 */
	public function setName(string $name): static
	{
		$this->name = $name;
		
		return $this;
	}
	
	/**
	 * @throws UnsupportedDiskAdapterException|FileTypeIsNotPresentedException|FileNotFoundException|UnableToUploadFileException|Throwable
	 */
	public function upload(): LaraFile
	{
		\throw_if(!$this->disk, new UnsupportedDiskAdapterException($this->disk));
		
		\throw_if(!$this->type, new FileTypeIsNotPresentedException());
		
		$fileExtension    = $this->uploadedFile->getFileExtension();
		$fileOriginalName = $this->name ?? $this->uploadedFile->getFileOriginalName();
		$fileHashName     = $this->uploadedFile->getHashName();
		
		if ($this->model instanceof Model) {
			$path = 'lara-files/' . strtolower(class_basename($this->model));
		} else {
			$path = 'lara-files/tmp';
		}
		
		$fullPath = "$path/$fileHashName.$fileExtension";
		
		$isSuccessfullyUploaded = Storage::disk($this->disk)->put($fullPath, $this->uploadedFile->getFileForUpload(), [
			'visibility' => $this->getVisibility(),
		]);
		
		\throw_if(!$isSuccessfullyUploaded, new UnableToUploadFileException);
		
		$laraFile = new LaraFile([
			'disk'        => $this->disk,
			'path'        => $path,
			'hash_name'   => $fileHashName,
			'extension'   => $fileExtension,
			'name'        => $fileOriginalName,
			'type'        => $this->type,
			'visibility'  => $this->getVisibility(),
			'description' => $this->description,
			'author_id'   => $this->authorId,
		]);
		
		if ($this->model instanceof Model) {
			$laraFile->larafilesable()->associate($this->model);
		}
		
		if (!$laraFile->save()) {
			if (Storage::disk($this->disk)->exists($fullPath)) {
				Storage::disk($this->disk)->delete($fullPath);
			}
			throw new UnableToUploadFileException();
		}
		
		return $laraFile;
	}
}