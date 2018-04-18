<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 20.11.17.
 * Time: 21.29
 */

namespace DjurovicIgoor\LaraFiles\Traits;

use function config;
use function dd;
use DjurovicIgoor\LaraFiles\Helpers\LaraFilesHandler;
use DjurovicIgoor\LaraFiles\LaraFile;

/**
 * @property null laraFileError
 */
trait LaraFileTrait {

    public $modelPath;
    public $laraFIleError;

    /**
     * LaraFileTrait constructor.
     *
     * @param array $attributes
     */
    public function __construct($attributes = []) {

        parent::__construct($attributes); // Calls Default Constructor
        $this->modelPath = config('lara-files.default_folder') . '/' . strtolower(class_basename($this));
    }

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
    //
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
    public function addFile($file, $storage = NULL, $type = NULL, $description = NULL, $user = NULL) {

        //        dd($this->useNameHashing(), $this->useStorage($storage));
        if (!isset($file)) {
            $this->laraFileError = LaraFilesHandler::setError();

            return;
        }
        //        $laraFilesHandler = LaraFilesHandler::uploadPath($this->setPath())->addFile($file);
        $laraFilesHandler = new LaraFilesHandler($this->setPath($this->useStorage($storage)), $this->useStorage($storage), $type, $description);
        $laraFilesHandler->addFile($file);

        dd($laraFilesHandler);
        if ($laraFilesHandler->hasError()) {

            $this->laraFileError = $laraFilesHandler;
        } else {
            $this->laraFiles()->save($this->storeFile($laraFilesHandler, $description, $user));
        }
        if ($this->laraFileError) {
            /** @var TYPE_NAME $this */
            return $this;
        } else {
            return NULL;
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
    public function addFiles($files, $storage = NULL, $description = NULL, $user = NULL, $type = 1) {

        if (!is_null($storage)) {
            $this->storage = $storage;
        }
        if (!isset($files)) {
            $this->laraErrors->push(LaraFilesHandler::setError());

            return;
        }
        if (!is_array($files)) {
            $files = [$files];
        }
        $files = collect($files);
        $files->each(function ($file) use ($description, $user) {

            $laraFilesHandler = LaraFilesHandler::uploadPath($this->setPath())->addFile($file);
            if ($laraFilesHandler->hasError()) {

                $this->laraErrors->push($laraFilesHandler);
            } else {
                $this->laraFiles()->save($this->storeFile($laraFilesHandler, $description, $user));
            }
        });
        if ($this->laraErrors->isNotEmpty()) {
            return $this;
        } else {
            return NULL;
        }
    }

    /**
     * @param $storage
     *
     * @return string
     */
    public function setPath($storage) {

        if ($storage) {
            $path = storage_path($this->modelPath);
        } else {
            $path = public_path($this->modelPath);
        }

        return $path;
    }

    /**
     * @param \DjurovicIgoor\LaraFiles\helpers\LaraFilesHandler $LaraFilesHandler
     * @param null                                              $description
     * @param null                                              $user
     *
     * @return \DjurovicIgoor\LaraFiles\LaraFile
     */
    public function storeFile(LaraFilesHandler $LaraFilesHandler, $description = NULL, $user = NULL) {

        return new LaraFile([
            'path'               => $this->modelPath . '/' . $this->attributes['id'] . '/',
            'hash_name'          => $LaraFilesHandler->hash_name,
            'name'               => $LaraFilesHandler->name,
            'extension'          => $LaraFilesHandler->extension,
            'type'               => 1,
            'larafilesable_type' => get_class(),
            'larafilesable_id'   => $this->id,
            'description'        => $description,
            #'author_id'          => !is_null( $user ) ?: $user->id,
        ]);
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

        return !isset($storage) ? ((isset($this->laraFilesStorage)) ? $this->laraFilesStorage : config('lara-files.storage')) : $storage;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function laraFiles() {

        return $this->morphMany(LaraFile::class, 'larafilesable');
    }

}