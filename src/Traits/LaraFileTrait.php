<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 20.11.17.
 * Time: 21.29
 */

namespace DjurovicIgoor\LaraFiles\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
                if (in_array($method, [Str::plural($value)])) {
                    return $this->morphMany(LaraFile::class, 'larafilesable')->where('type', $value);
                }
            }
            foreach (config('lara-files.types') as $value) {
                if (in_array($method, ['get'.ucwords($value)])) {
                    return $this->morphOne(LaraFile::class, 'larafilesable')->where('type', $value)->first();
                }
            }
            foreach (config('lara-files.types') as $value) {
                if (in_array($method, ['get'.Str::plural(ucwords($value))])) {
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
        if ($this->relationLoaded($key)) {
            return $this->relations[ $key ];
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
        throw_unless(array_key_exists($disk, config('filesystems.disks')), new UnsupportedDiskAdapterException("Disk \"{$disk}\" is not supported! Please check your \"config/filesystems.php\" for disk drivers."), null);
    }
    
    /**
     * Copy file from another model
     * 
     * @param  LaraFile  $laraFile
     */
    public function copyFromAnotherLaraFile(LaraFile $laraFile)
    {
        $hashName = md5(microtime());
        $copedFile = Storage::disk($laraFile->disk)->copy("{$laraFile->path}/{$laraFile->hash_name}.{$laraFile->extension}", $this->getModelPath()."/$hashName.{$laraFile->extension}");
        if ($copedFile) {
            $newLaraFile = new LaraFile([
                'disk'        => $laraFile->disk,
                'path'        => $this->getModelPath(),
                'type'        => $laraFile->type,
                'hash_name'   => $hashName,
                'name'        => $laraFile->name,
                'extension'   => $laraFile->extension,
                'visibility'  => $laraFile->visibility,
                'description' => self::class.' attachment',
                'author_id'   => $laraFile->author_id,,
            ]);
            $this->laraFiles()->save($newLaraFile);
        }
    }
}
