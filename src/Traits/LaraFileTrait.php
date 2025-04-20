<?php

namespace DjurovicIgoor\LaraFiles\Traits;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use DjurovicIgoor\LaraFiles\LaraFile;
use DjurovicIgoor\LaraFiles\Classes\LaraFileUploader;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
	
//	/**
//	 * @param       $method
//	 * @param       $arguments
//	 * @param mixed $key
//	 *
//	 * @return mixed
//	 */
//	public function getRelationValue($key)
//	{
//		if ($this->relationLoaded($key)) {
//			return $this->relations[$key];
//		}
//
//		if (!$this->isRelation($key) && !(in_array($key, config('lara-files.types')) || in_array(str_singular($key), config('lara-files.types')))) {
//			return;
//		}
//
//		if ($this->preventsLazyLoading) {
//			$this->handleLazyLoadingViolation($key);
//		}
//
//		// If the "attribute" exists as a method on the model, we will just assume
//		// it is a relationship and will load and return results from the query
//		// and hydrate the relationship's value on the "relationships" array.
//		return $this->getRelationshipFromMethod($key);
//	}
	
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
	 * @return MorphMany
	 */
	public function laraFiles(): MorphMany
	{
		return $this->morphMany(LaraFile::class, 'larafilesable');
	}
	
	/**
	 * Copy file from another model
	 *
	 * @param LaraFile $laraFile
	 * @param          $disk
	 * @param          $type
	 * @param null     $visibility
	 * @param null     $description
	 * @param null     $authorId
	 * @param null     $name
	 *
	 * @return LaraFile
	 * @throws FileNotFoundException
	 * @throws Throwable
	 */
	public function copyFromAnotherLaraFile(LaraFile $laraFile, $disk = NULL, $type = NULL, $visibility = NULL, $description = NULL, $authorId = NULL, $name = NULL): LaraFile
	{
		$laraFileUploader = (new LaraFileUploader(uploadedFile: $laraFile, fileUploaderType: 'lara_file'))->setDisk(disk: $disk ?? $laraFile->disk)
			->setType(type: $type ?? $laraFile->type)
			->setModel(model: $this);
		
		$laraFileUploader->setVisibility(visibility: $visibility ?? $laraFile->visibility);
		
		$description = $description ?? $laraFile->description;
		if ($description !== NULL) {
			$laraFileUploader->setDescription(description: $description);
		}
		
		$authorId = $authorId ?? $laraFile->author_id;
		if ($authorId !== NULL) {
			$laraFileUploader->setAuthorId(authorId: $authorId);
		}
		
		$name = $name ?? $laraFile->name;
		if ($name !== NULL) {
			$laraFileUploader->setName(name: $name);
		}
		
		return $laraFileUploader->upload();
	}
}