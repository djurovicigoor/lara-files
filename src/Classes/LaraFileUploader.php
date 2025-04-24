<?php

namespace DjurovicIgoor\LaraFiles\Classes;

use DjurovicIgoor\LaraFiles\Contracts\UploadFileInterface;
use DjurovicIgoor\LaraFiles\Exceptions\FileIsNotBase64EncodedStringException;
use DjurovicIgoor\LaraFiles\Exceptions\FileIsNotInstanceOfLaraFileModelException;
use DjurovicIgoor\LaraFiles\Exceptions\FileIsNotInstanceOfUploadedFileClassException;
use DjurovicIgoor\LaraFiles\Exceptions\FileTypeIsNotPresentedException;
use DjurovicIgoor\LaraFiles\Exceptions\UnableToUploadFileException;
use DjurovicIgoor\LaraFiles\Exceptions\UnsupportedDiskAdapterException;
use DjurovicIgoor\LaraFiles\Exceptions\VisibilityIsNotValidException;
use DjurovicIgoor\LaraFiles\Models\LaraFile;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class LaraFileUploader
 *
 * Handles the uploading of files with configurable options such as disk, type, visibility, description, author ID, and name.
 * Provides methods to define file-related behaviors and supports associating uploaded files with a model instance.
 * Implements file storage and supports throwing relevant exceptions during file operations or validation failures.
 */
class LaraFileUploader
{
    private UploadFileInterface $uploadedFile;

    private ?string $disk = null;

    private ?string $type = null;

    private ?string $visibility = null;

    private ?string $description = null;

    private ?string $authorId = null;

    private ?string $name = null;

    private ?Model $model = null;

    /**
     * Constructor to initialize the uploaded file based on the type of file uploader provided.
     *
     * @param  mixed  $uploadedFile  The file being uploaded.
     * @param  string  $fileUploaderType  The type of file uploader. Can be 'http_file', 'base64_file', or 'lara_file'.
     *
     * @throws FileIsNotInstanceOfUploadedFileClassException|FileIsNotBase64EncodedStringException|FileIsNotInstanceOfLaraFileModelException|Throwable
     */
    public function __construct(mixed $uploadedFile, string $fileUploaderType = 'http_file')
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
     * @return LaraFileUploader
     *
     * @throws UnsupportedDiskAdapterException|Throwable
     */
    public function setDisk(string $disk): static
    {
        throw_if(! array_key_exists($disk, config('filesystems.disks')), new UnsupportedDiskAdapterException($disk));

        $this->disk = $disk;

        return $this;
    }

    /**
     * @return LaraFileUploader
     *
     * @throws Throwable|FileTypeIsNotPresentedException
     */
    public function setType(string $type): static
    {
        \throw_if(! $type || $type == '', new FileTypeIsNotPresentedException);

        $this->type = $type;

        return $this;
    }

    /**
     * @return LaraFileUploader
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return LaraFileUploader
     */
    public function setModel(Model $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getVisibility(): string
    {
        if (! $this->visibility) {
            return \config('lara-files.visibility');
        }

        return $this->visibility;
    }

    /**
     * @return LaraFileUploader
     *
     * @throws Throwable|VisibilityIsNotValidException
     */
    public function setVisibility(string $visibility): static
    {
        \throw_if(! in_array($visibility, ['public', 'private']), new VisibilityIsNotValidException($visibility));

        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return LaraFileUploader
     */
    public function setAuthorId(string|int $authorId): static
    {
        $this->authorId = $authorId;

        return $this;
    }

    /**
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
        \throw_if(! $this->disk, new UnsupportedDiskAdapterException($this->disk));

        \throw_if(! $this->type, new FileTypeIsNotPresentedException);

        $fileExtension = $this->uploadedFile->getFileExtension();
        $fileOriginalName = $this->name ?? $this->uploadedFile->getFileOriginalName();
        $fileHashName = $this->uploadedFile->getHashName();

        if ($this->model instanceof Model) {
            $path = 'lara-files/'.strtolower(class_basename($this->model));
        } else {
            $path = 'lara-files/tmp';
        }

        $fullPath = "$path/$fileHashName.$fileExtension";

        $isSuccessfullyUploaded = Storage::disk($this->disk)->put($fullPath, $this->uploadedFile->getFileForUpload(), [
            'visibility' => $this->getVisibility(),
        ]);

        \throw_if(! $isSuccessfullyUploaded, new UnableToUploadFileException);

        $laraFile = new LaraFile([
            'disk' => $this->disk,
            'path' => $path,
            'hash_name' => $fileHashName,
            'extension' => $fileExtension,
            'name' => $fileOriginalName,
            'type' => $this->type,
            'visibility' => $this->getVisibility(),
            'description' => $this->description,
            'author_id' => $this->authorId,
        ]);

        if ($this->model instanceof Model) {
            $laraFile->larafilesable()->associate($this->model);
        }

        if (! $laraFile->save()) {
            if (Storage::disk($this->disk)->exists($fullPath)) {
                Storage::disk($this->disk)->delete($fullPath);
            }
            throw new UnableToUploadFileException;
        }

        return $laraFile;
    }

    /**
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public static function uploadForOptimizationAndManipulation($uploadedFile, $fileUploaderType, $disk, $type, $visibility = null, $description = null, $authorId = null, $name = null): LaraFile
    {
        $laraFileUploader = (new LaraFileUploader(uploadedFile: $uploadedFile, fileUploaderType: $fileUploaderType))->setDisk(disk: $disk)->setType(type: $type);

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
}
