<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 20.11.17.
 * Time: 21.29
 */

namespace DjurovicIgoor\LaraFiles\Traits;

use DjurovicIgoor\LaraFiles\Classes\HttpUploader;
use DjurovicIgoor\LaraFiles\Exceptions\UnsupportedDiskAdapterException;
use DjurovicIgoor\LaraFiles\Helpers\LaraFilesHandler;
use DjurovicIgoor\LaraFiles\LaraFile;
use Illuminate\Http\UploadedFile;

/**
 * @property null laraFileError
 */
trait LaraFileTrait {
    
    //
    //    /**
    //     * LaraFileTrait constructor.
    //     *
    //     * @param array $attributes
    //     */
    //    public function __construct($attributes = []) {
    //
    //        parent::__construct($attributes); // Calls Default Constructor
    //    }
    /**
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments) {
        
        if (!empty(config('lara-files.types'))) {
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
                
                if (in_array($method, ['get' . ucwords($value)])) {
                    return $this->morphOne(LaraFile::class, 'larafilesable')->where('type', $value)->first();
                }
            }
            foreach (config('lara-files.types') as $value) {
                
                if (in_array($method, ['get' . str_plural(ucwords($value))])) {
                    return $this->morphMany(LaraFile::class, 'larafilesable')->where('type', $value)->get();
                }
            }
        }
        
        return parent::__call($method, $arguments);
    }
    
    /**
     * @param              $disk
     * @param UploadedFile $file
     * @param              $type
     *
     * @throws \Throwable
     */
    public function uploadHttpFile($disk, UploadedFile $file, $type, $additionalParameters = []) {
        
        $this->diskIsValid($disk);
        $fileMove = new HttpUploader($disk, $this->getModelPath(), $type, $additionalParameters);
        $fileMove->move($file);
        $this->laraFiles()->save($fileMove->laraFile);
    }
    
    /**
     * @param              $disk
     * @param UploadedFile $file
     * @param              $type
     *
     * @throws \Throwable
     */
    public function uploadHttpFiles($disk, $files, $type, $additionalParameters = []) {
        
        if (!is_array($files)) {
            $files = [$files];
        }
        $files = collect($files);
        foreach ($files as $file) {
            
            $this->diskIsValid($disk);
            $fileMove = new HttpUploader($disk, $this->getModelPath(), $type, $additionalParameters);
            $fileMove->move($file);
            $this->laraFiles()->save($fileMove->laraFile);
        }
    }
    
    /**
     * @param $disk
     *
     * @throws \Throwable
     */
    private function diskIsValid($disk) {
        
        throw_unless(array_key_exists($disk, config('filesystems.disks')), new UnsupportedDiskAdapterException("Disk \"{$disk}\" is not supported! Please check your \"config/filesistems.php\" for disk drivers."), NULL);
    }
    
    /**
     * @return string
     */
    public function getModelPath() {
        
        return 'lara-files/' . strtolower(class_basename($this));
    }
    
    //    public function getRelationValue($key)
    //    {
    //        // If the key already exists in the relationships array, it just means the
    //        // relationship has already been loaded, so we'll just return it out of
    //        // here because there is no need to query within the relations twice.
    //        if ($this->relationLoaded($key)) {
    //            return $this->relations[$key];
    //        }
    //
    //        // If the "attribute" exists as a method on the model, we will just assume
    //        // it is a relationship and will load and return results from the query
    //        // and hydrate the relationship's value on the "relationships" array. || in_array($key, $this->morphs)
    //        if (method_exists($this, $key) ) {
    //            return $this->getRelationshipFromMethod($key);
    //        }
    //    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function laraFiles() {
        
        return $this->morphMany(LaraFile::class, 'larafilesable');
    }
    
}
