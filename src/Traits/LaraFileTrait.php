<?php

namespace DjurovicIgoor\LaraFiles\Traits;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use DjurovicIgoor\LaraFiles\LaraFile;
use Illuminate\Support\Facades\Storage;
use DjurovicIgoor\LaraFiles\Classes\LaraFileUploader;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use DjurovicIgoor\LaraFiles\Exceptions\UnableToUploadFileException;
use DjurovicIgoor\LaraFiles\Exceptions\UnsupportedDiskAdapterException;
use DjurovicIgoor\LaraFiles\Exceptions\FileTypeIsNotPresentedException;

trait LaraFileTrait
{
	public function __call($method, $arguments)
	{
		if (!empty(config('lara-files.types'))) {
			foreach (config('lara-files.types') as $value) {
				if ($method == $value) {
					return $this->morphOne(LaraFile::class, 'larafilesable')->where('type', $value);
				}
				if ($method == Str::plural($value)) {
					return $this->morphMany(LaraFile::class, 'larafilesable')->where('type', $value);
				}
				if ($method == 'get' . ucwords($value)) {
					return $this->morphOne(LaraFile::class, 'larafilesable')->where('type', $value)->first();
				}
				if ($method == 'get' . Str::plural(ucwords($value))) {
					return $this->morphMany(LaraFile::class, 'larafilesable')->where('type', $value)->get();
				}
			}
		}
		
		return parent::__call($method, $arguments);
	}
	
	/**
	 * @param       $method
	 * @param       $arguments
	 * @param mixed $key
	 *
	 * @return mixed
	 */
	public function getRelationValue($key)
	{
		if ($this->relationLoaded($key)) {
			return $this->relations[$key];
		}
		
		if (!$this->isRelation($key) && !(in_array($key, config('lara-files.types')) || in_array(str_singular($key), config('lara-files.types')))) {
			return;
		}
		
		if ($this->preventsLazyLoading) {
			$this->handleLazyLoadingViolation($key);
		}
		
		// If the "attribute" exists as a method on the model, we will just assume
		// it is a relationship and will load and return results from the query
		// and hydrate the relationship's value on the "relationships" array.
		return $this->getRelationshipFromMethod($key);
	}
	
	/**
	 * @param      $uploadedFile
	 * @param      $disk
	 * @param      $type
	 * @param null $visibility
	 * @param null $description
	 * @param null $authorId
	 * @param null $name
	 *
	 * @return LaraFile
	 * @throws FileNotFoundException
	 * @throws Throwable
	 */
	public function uploadHttpFile($uploadedFile, $disk, $type, $visibility = NULL, $description = NULL, $authorId = NULL, $name = NULL): LaraFile
	{
		$laraFileUploader = (new LaraFileUploader(uploadedFile: $uploadedFile, fileUploaderType: 'http_file'))->setDisk(disk: $disk)->setType(type: $type)->setModel(model: $this);
		if ($visibility) {
			$laraFileUploader->setVisibility(visibility: $visibility);
		}
		if ($description) {
			$laraFileUploader->setDescription(description: $description);
		}
		if ($authorId) {
			$laraFileUploader->setAuthorId(authorId: $authorId);
		}
		if ($name) {
			$laraFileUploader->setName(name: $name);
		}
		
		return $laraFileUploader->upload();
	}
	
	/**
	 * @param array $uploadedFiles
	 * @param       $disk
	 * @param       $type
	 * @param null  $visibility
	 * @param null  $description
	 * @param null  $authorId
	 * @param null  $name
	 *
	 * @return Collection
	 * @throws FileNotFoundException *
	 * @throws Throwable *
	 */
	public function uploadHttpFiles(array $uploadedFiles, $disk, $type, $visibility = NULL, $description = NULL, $authorId = NULL, $name = NULL): Collection
	{
		if (\count($uploadedFiles) == 0) {
			return \collect();
		}
		
		$uploadedFilesCollection = \collect();
		
		foreach ($uploadedFiles as $uploadedFile) {
			$uploadedFilesCollection->push($this->uploadHttpFile($uploadedFile, $disk, $type, $visibility, $description, $authorId, $name));
		}
		
		return $uploadedFilesCollection;
	}
	
	/**
	 * @throws Throwable
	 * @throws UnsupportedDiskAdapterException
	 * @throws FileTypeIsNotPresentedException
	 * @throws FileNotFoundException
	 * @throws UnableToUploadFileException
	 */
	public function uploadBase64File($uploadedFile, $disk, $type, $visibility = NULL, $description = NULL, $authorId = NULL, $name = NULL): LaraFile
	{
		$laraFileUploader = (new LaraFileUploader(uploadedFile: $uploadedFile, fileUploaderType: 'base64_file'))->setDisk(disk: $disk)
			->setType(type: $type)
			->setModel(model: $this);
		if ($visibility) {
			$laraFileUploader->setVisibility(visibility: $visibility);
		}
		if ($description) {
			$laraFileUploader->setDescription(description: $description);
		}
		if ($authorId) {
			$laraFileUploader->setAuthorId(authorId: $authorId);
		}
		if ($name) {
			$laraFileUploader->setName(name: $name);
		}
		
		return $laraFileUploader->upload();
	}
	
	/**
	 * @throws Throwable
	 * @throws FileNotFoundException
	 */
	public function uploadBase64Files(array $uploadedFiles, $disk, $type, $visibility = NULL, $description = NULL, $authorId = NULL, $name = NULL): Collection
	{
		if (\count($uploadedFiles) == 0) {
			return \collect();
		}
		
		$uploadedFilesCollection = \collect();
		
		foreach ($uploadedFiles as $uploadedFile) {
			$uploadedFilesCollection->push($this->uploadBase64File($uploadedFile, $disk, $type, $visibility, $description, $authorId, $name));
		}
		
		return $uploadedFilesCollection;
	}
	
	/**
	 * @return string
	 */
	public function getModelPath()
	{
		return 'lara-files/' . strtolower(class_basename($this));
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function laraFiles()
	{
		return $this->morphMany(LaraFile::class, 'larafilesable');
	}
	
	/**
	 * @param $disk
	 *
	 * @throws \Throwable
	 */
	private function diskIsValid($disk)
	{
		throw_unless(array_key_exists($disk, config('filesystems.disks')), new UnsupportedDiskAdapterException("Disk \"{$disk}\" is not supported! Please check your \"config/filesystems.php\" for disk drivers."), NULL);
	}
	
	/**
	 * Copy file from another model
	 *
	 * @param LaraFile $laraFile
	 */
	public function copyFromAnotherLaraFile(LaraFile $laraFile)
	{
		$hashName  = md5(microtime());
		$copedFile = Storage::disk($laraFile->disk)
			->copy("{$laraFile->path}/{$laraFile->hash_name}.{$laraFile->extension}", $this->getModelPath() . "/$hashName.{$laraFile->extension}");
		if ($copedFile) {
			$newLaraFile = new LaraFile([
				'disk'        => $laraFile->disk,
				'path'        => $this->getModelPath(),
				'type'        => $laraFile->type,
				'hash_name'   => $hashName,
				'name'        => $laraFile->name,
				'extension'   => $laraFile->extension,
				'visibility'  => $laraFile->visibility,
				'description' => self::class . ' attachment',
				'author_id'   => $laraFile->author_id,
			]);
			$this->laraFiles()->save($newLaraFile);
		}
	}
}