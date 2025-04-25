<?php

namespace DjurovicIgoor\LaraFiles\Traits;

use DjurovicIgoor\LaraFiles\Classes\LaraFileUploader;
use DjurovicIgoor\LaraFiles\Exceptions\FileTypeIsNotPresentedException;
use DjurovicIgoor\LaraFiles\Exceptions\UnableToUploadFileException;
use DjurovicIgoor\LaraFiles\Exceptions\UnsupportedDiskAdapterException;
use DjurovicIgoor\LaraFiles\Models\LaraFile;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

trait LaraFileTrait
{
    public static function bootLaraFileTrait(): void
    {
        static::deleting(function ($model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (! $model->forceDeleting) {
                    return;
                }
            }
            $model->laraFiles()->cursor()->each(fn (LaraFile $laraFile) => $laraFile->delete());
        });
    }

    public function __call($method, $arguments)
    {
        if (! empty(config('lara-files.types'))) {
            foreach (config('lara-files.types') as $value) {
                if ($method == $value) {
                    return $this->morphOne(LaraFile::class, 'larafilesable')->where('type', $value);
                }
                if ($method == Str::plural($value)) {
                    return $this->morphMany(LaraFile::class, 'larafilesable')->where('type', $value);
                }
                if ($method == 'get'.ucwords($value)) {
                    return $this->morphOne(LaraFile::class, 'larafilesable')->where('type', $value)->first();
                }
                if ($method == 'get'.Str::plural(ucwords($value))) {
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
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function uploadHttpFile(UploadedFile $uploadedFile, string $type, ?string $disk = null, $visibility = null, $name = null, array $customProperties = []): LaraFile
    {
        $laraFileUploader = (new LaraFileUploader(uploadedFile: $uploadedFile, fileUploaderType: 'http_file'))->setType(type: $type)->setModel(model: $this);
        if ($disk) {
            $laraFileUploader->setDisk(disk: $disk);
        }
        if ($visibility) {
            $laraFileUploader->setVisibility(visibility: $visibility);
        }
        if ($name) {
            $laraFileUploader->setName(name: $name);
        }
        if (\count($customProperties)) {
            $laraFileUploader->setCustomProperties(customProperties: $customProperties);
        }

        return $laraFileUploader->upload();
    }

    /**
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function uploadHttpFiles(array $uploadedFiles, string $disk, string $type, $visibility = null, $name = null, array $customProperties = []): Collection
    {
        if (\count($uploadedFiles) == 0) {
            return \collect();
        }

        $uploadedFilesCollection = \collect();

        foreach ($uploadedFiles as $uploadedFile) {
            $uploadedFilesCollection->push($this->uploadHttpFile(uploadedFile: $uploadedFile, type: $type, disk: $disk, visibility: $visibility, name: $name,
                customProperties: $customProperties));
        }

        return $uploadedFilesCollection;
    }

    /**
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function uploadBase64File(
        string $uploadedFile,
        string $type,
        ?string $disk = null,
        ?string $visibility = null,
        ?string $name = null,
        array $customProperties = []
    ): LaraFile {
        $laraFileUploader = (new LaraFileUploader(uploadedFile: $uploadedFile, fileUploaderType: 'base64_file'))->setType(type: $type)->setModel(model: $this);
        if ($disk) {
            $laraFileUploader->setDisk(disk: $disk);
        }
        if ($visibility) {
            $laraFileUploader->setVisibility(visibility: $visibility);
        }
        if ($name) {
            $laraFileUploader->setName(name: $name);
        }
        if (\count($customProperties)) {
            $laraFileUploader->setCustomProperties(customProperties: $customProperties);
        }

        return $laraFileUploader->upload();
    }

    /**
     * @throws Throwable
     * @throws FileNotFoundException
     */
    public function uploadBase64Files(array $uploadedFiles, string $type, ?string $disk = null, ?string $visibility = null, $name = null, array $customProperties = []): Collection
    {
        if (\count($uploadedFiles) == 0) {
            return \collect();
        }

        $uploadedFilesCollection = \collect();

        foreach ($uploadedFiles as $uploadedFile) {
            $uploadedFilesCollection->push($this->uploadBase64File(uploadedFile: $uploadedFile, type: $type, disk: $disk, visibility: $visibility, name: $name,
                customProperties: $customProperties));
        }

        return $uploadedFilesCollection;
    }

    /**
     * @return string
     */
    public function getModelPath()
    {
        return 'lara-files/'.strtolower(class_basename($this));
    }

    public function laraFiles(): MorphMany
    {
        return $this->morphMany(LaraFile::class, 'larafilesable');
    }

    /**
     * Copy file from another model
     *
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function copyFromAnotherLaraFile(LaraFile $laraFile, ?string $disk = null, ?string $type = null, ?string $visibility = null, ?string $name = null): LaraFile
    {
        $laraFileUploader = (new LaraFileUploader(uploadedFile: $laraFile, fileUploaderType: 'lara_file'))->setDisk(disk: $disk ?? $laraFile->disk)
            ->setType(type: $type ?? $laraFile->type)
            ->setModel(model: $this);

        $laraFileUploader->setVisibility(visibility: $visibility ?? $laraFile->visibility);

        $description = $description ?? $laraFile->description;
        if ($description !== null) {
            $laraFileUploader->setDescription(description: $description);
        }

        $authorId = $authorId ?? $laraFile->author_id;
        if ($authorId !== null) {
            $laraFileUploader->setAuthorId(authorId: $authorId);
        }

        $name = $name ?? $laraFile->name;
        if ($name !== null) {
            $laraFileUploader->setName(name: $name);
        }

        if (\count($laraFile->custom_properties)) {
            $laraFileUploader->setCustomProperties(customProperties: $laraFile->custom_properties);
        }

        return $laraFileUploader->upload();
    }

    /**
     * @throws Throwable
     */
    public function addHttpFile(UploadedFile $uploadedFile, string $type): LaraFileUploader
    {
        return (new LaraFileUploader(uploadedFile: $uploadedFile, fileUploaderType: 'http_file'))->setType(type: $type)->setModel(model: $this);
    }

    /**
     * @throws Throwable
     * @throws UnsupportedDiskAdapterException
     * @throws FileTypeIsNotPresentedException
     * @throws FileNotFoundException
     * @throws UnableToUploadFileException
     */
    public function addBase64File(string $uploadedFile, string $type): LaraFileUploader
    {
        return (new LaraFileUploader(uploadedFile: $uploadedFile, fileUploaderType: 'base64_file'))->setType(type: $type)->setModel(model: $this);
    }
}
