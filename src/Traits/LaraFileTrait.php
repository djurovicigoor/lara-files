<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 20.11.17.
 * Time: 21.29
 */

namespace DjurovicIgoor\LaraFiles\Traits;

use Illuminate\Http\UploadedFile;
use DjurovicIgoor\LaraFiles\LaraFile;
use DjurovicIgoor\LaraFiles\Classes\HttpUploader;
use DjurovicIgoor\LaraFiles\Classes\Base64Uploader;
use DjurovicIgoor\LaraFiles\Exceptions\UnsupportedDiskAdapterException;

/**
 * @property null laraFileError
 */
trait LaraFileTrait
{
    /**
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if ( ! empty(config('lara-files.types'))) {
            foreach (config('lara-files.types') as $value) {
                if (in_array($method, [$value])) {
                    return $this->morphOne(LaraFile::class, 'larafilesable')->where('type', $value);
                }
            }
            foreach (config('lara-files.types') as $value) {
                if (in_array($method, [str_plural($value)])) {
                    return $this->morphMany(LaraFile::class, 'larafilesable')->where('type', $value);
                }
            }
            foreach (config('lara-files.types') as $value) {
                if (in_array($method, ['get'.ucwords($value)])) {
                    return $this->morphOne(LaraFile::class, 'larafilesable')->where('type', $value)->first();
                }
            }
            foreach (config('lara-files.types') as $value) {
                if (in_array($method, ['get'.str_plural(ucwords($value))])) {
                    return $this->morphMany(LaraFile::class, 'larafilesable')->where('type', $value)->get();
                }
            }
        }

        return parent::__call($method, $arguments);
    }

    /**
     * @param $method
     * @param $arguments
     * @param mixed $key
     *
     * @return mixed
     */
    public function getRelationValue($key)
    {

        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->relations[ $key ];
        }
        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array. || in_array($key, $this->morphs)
        if (in_array($key, config('lara-files.types')) || in_array(str_singular($key), config('lara-files.types'))) {
            return $this->getRelationshipFromMethod($key);
        } else {
            if (method_exists($this, $key)) {
                return $this->getRelationshipFromMethod($key);
            }
        }
    }

    /**
     * @param              $disk
     * @param UploadedFile $file
     * @param              $type
     * @param mixed        $additionalParameters
     *
     * @throws \Throwable
     */
    public function uploadHttpFile($disk, UploadedFile $file, $type, $additionalParameters = [])
    {
        $this->diskIsValid($disk);
        $fileMove = new HttpUploader($disk, $this->getModelPath(), $type, $additionalParameters);
        $fileMove->putFile($file);
        $this->laraFiles()->save($fileMove->laraFile);
    }

    /**
     * @param              $disk
     * @param UploadedFile $file
     * @param              $type
     * @param mixed        $files
     * @param mixed        $additionalParameters
     *
     * @throws \Throwable
     */
    public function uploadHttpFiles($disk, $files, $type, $additionalParameters = [])
    {
        $this->diskIsValid($disk);
        if ( ! is_array($files)) {
            $files = [$files];
        }
        $files = collect($files);
        foreach ($files as $file) {
            $fileMove = new HttpUploader($disk, $this->getModelPath(), $type, $additionalParameters);
            $fileMove->putFile($file);
            $this->laraFiles()->save($fileMove->laraFile);
        }
    }

    /**
     * @param              $disk
     * @param UploadedFile $file
     * @param              $type
     * @param mixed        $base64File
     * @param mixed        $additionalParameters
     *
     * @throws \Throwable
     */
    public function uploadBase64File($disk, $base64File, $type, $additionalParameters = [])
    {
        $this->diskIsValid($disk);
        $fileMove = new Base64Uploader($disk, $this->getModelPath(), $type, $additionalParameters);
        $fileMove->putFile($base64File);
        $this->laraFiles()->save($fileMove->laraFile);
    }

    /**
     * @param              $disk
     * @param UploadedFile $file
     * @param              $type
     * @param mixed        $base64Files
     * @param mixed        $additionalParameters
     *
     * @throws \Throwable
     */
    public function uploadBase64Files($disk, $base64Files, $type, $additionalParameters = [])
    {
        $this->diskIsValid($disk);
        if ( ! is_array($base64Files)) {
            $base64Files = [$base64Files];
        }
        $base64Files = collect($base64Files);
        foreach ($base64Files as $base64File) {
            $fileMove = new Base64Uploader($disk, $this->getModelPath(), $type, $additionalParameters);
            $fileMove->putFile($base64File);
            $this->laraFiles()->save($fileMove->laraFile);
        }
    }

    /**
     * @return string
     */
    public function getModelPath()
    {
        return 'lara-files/'.strtolower(class_basename($this));
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
        throw_unless(array_key_exists($disk, config('filesystems.disks')), new UnsupportedDiskAdapterException("Disk \"{$disk}\" is not supported! Please check your \"config/filesistems.php\" for disk drivers."), null);
    }
}
