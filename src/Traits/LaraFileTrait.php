<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 20.11.17.
 * Time: 21.29
 */

namespace DjurovicIgoor\LaraFiles\Traits;

use DjurovicIgoor\LaraFiles\Exceptions\UnsupportedDiskAdapterException;
use DjurovicIgoor\LaraFiles\Helpers\LaraFilesHandler;
use DjurovicIgoor\LaraFiles\LaraFile;

/**
 * @property null laraFileError
 */
trait LaraFileTrait {
    
    public $laraFileError;
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
    
    public function addFile($disk) {
        
        $this->diskIsValid($disk);
        $this->getModelPath();
    }
    
    private function diskIsValid($disk) {
        
        throw_unless(array_key_exists($disk, config('filesystems.disks')), new UnsupportedDiskAdapterException("Disk \"{$disk}\" is not supported! Please check your \"config/filesistems.php\" for disk drivers."), NULL);
    }
    
    /**
     * @return string
     */
    public function getModelPath() {
        
        return config('lara-files.default_folder') . '/' . strtolower(class_basename($this));
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
     * @param      $file
     * @param null $storage
     * @param null $description
     * @param null $user
     * @param int  $type
     *
     * @return LaraFileTrait|void
     * @throws \Exception
     */
    public function addFileOld($file, $storage = NULL, $type = NULL, $description = NULL, $user = NULL) {
        
        $laraFilesHandler = new LaraFilesHandler($this->getModelPath(), $this->useStorage($storage), $type, $description, $user);
        if ($laraFilesHandler->addFile($file)) {
            $this->laraFileError = $laraFilesHandler;
        } else {
            $this->laraFileError = $laraFilesHandler;
            $this->laraFiles()->save($this->storeFile($laraFilesHandler->toArray(get_class(), $this->id)));
        }
    }
    
    /**
     * @param      $files
     * @param null $storage
     * @param null $description
     * @param null $user
     * @param int  $type
     *
     * @return void
     * @throws \Exception
     */
    public function addFiles($files, $storage = NULL, $type = NULL, $description = NULL, $user = NULL) {
        
        if (!is_array($files)) {
            $files = [$files];
        }
        $files = collect($files);
        $files->each(function($file) use ($storage, $description, $user, $type) {
            
            $laraFilesHandler = new LaraFilesHandler($this->getModelPath(), $this->useStorage($storage), $type, $description, $user);
            if ($laraFilesHandler->addFile($file)) {
                $this->laraFileError = $laraFilesHandler;
            } else {
                $this->laraFileError = $laraFilesHandler;
                $this->laraFiles()->save($this->storeFile($laraFilesHandler->toArray(get_class(), $this->id)));
            }
        });
    }
    
    
    public function getFullSavePath() {
        
        LaraFilesHandler::setPath($this->useStorage(), $this->getModelPath());
    }
    
    /**
     * @param $data
     *
     * @return \DjurovicIgoor\LaraFiles\LaraFile
     */
    public function storeFile($data) {
        
        return new LaraFile($data);
    }
    
    /**
     * @return bool
     */
    public function useNameHashing() {
        
        return (isset($this->laraFilesNameHashing)) ? $this->laraFilesNameHashing : config('lara-files.name_hashing');
    }
    
    /**
     * @param $storage
     *
     * @return bool
     */
    public function useStorage($storage) {
        
        return isset($storage) ? $storage : ((isset($this->laraFilesStorage)) ? ($this->laraFilesStorage) : (config('lara-files.storage')));
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function laraFiles() {
        
        return $this->morphMany(LaraFile::class, 'larafilesable');
    }
    
}
