<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 20.11.17.
 * Time: 21.51
 */

namespace DjurovicIgoor\LaraFiles\Helpers;

use function dd;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class LaraFilesHandler {

    public $path;
    public $hash_name;
    public $name;
    public $extension;
    public $type;
    public $description;
    public $author;
    public $storage;
    public $error;

    /**
     * Default constructor
     *
     * @param $path
     * @param $storage
     * @param $type
     * @param $description
     * @param $user
     */
    public function __construct($path = NULL, $storage = NULL, $type = NULL, $description = NULL, $user = NULL) {

        $this->path        = $path;
        $this->hash_name   = NULL;
        $this->name        = NULL;
        $this->extension   = NULL;
        $this->type        = $type;
        $this->description = $description;
        $this->author      = $user;
        $this->storage     = $storage;
        $this->error       = NULL;
    }

    /**
     * @param $type
     * @param $id
     *
     * @return array
     */
    public function toArray($type, $id) {

        return [
            'path'               => $this->path,
            'hash_name'          => $this->hash_name,
            'name'               => $this->name,
            'extension'          => $this->extension,
            'type'               => $this->type,
            'larafilesable_type' => $type,
            'larafilesable_id'   => $id,
            'description'        => $this->description,
            'author_id'          => !is_null($this->author) ?: $this->author->id,
            'storage'            => $this->storage,
        ];
    }

    /**
     * Create a directory
     */
    public function createDirectory() {

        $fullPath = LaraFilesHandler::setPath($this->storage, $this->path, TRUE);
        try {
            try {
                File::makeDirectory($fullPath, 0755, TRUE);
            } catch (\Exception $exception) {
                throw new \Exception("Couldn't create $fullPath");
                $this->setExceptionError($exception);
            }
        } catch (\Exception $exception) {
            $this->setExceptionError($exception);
        }
    }

    /**
     *
     * @return null
     */
    public function setPermission() {

        if (!File::isWritable(LaraFilesHandler::setPath($this->storage, $this->path, TRUE))) {
            if (!File::chmod(LaraFilesHandler::setPath($this->storage, $this->path, TRUE), 0755)) {
                $this->setError("Couldn't change folder permission!");
            }
        }
    }

    /**
     * @return bool
     */
    public function ifFolderExist() {

        if (File::exists(LaraFilesHandler::setPath($this->storage, $this->path, TRUE))) {
            $this->setPermission();
        } else {
            $this->createDirectory();
        }

        return $this->hasError();
    }

    /**
     * Handles files upload
     *
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return bool|LaraFilesHandler
     */
    public function addFile(UploadedFile $file = NULL) {

        if (isset($file)) {
            if (!$this->ifFolderExist()) {
                $this->setHashName();
                $this->setName($file);
                $this->setExtension($file);
                if (!$this->hasError()) {
                    try {
                        $file->move(LaraFilesHandler::setPath($this->storage, $this->path, TRUE), $this->hash_name . '.' . $file->getClientOriginalExtension());
                    } catch (\Exception $exception) {
                        $this->setExceptionError($exception);
                    }
                }

            }
        } else {
            $this->setError("File not provided!");
        }

        return $this->hasError();
    }

    /**
     */
    public function setHashName() {

        $this->hash_name = md5(microtime());
        try {
            if (!isset($this->hash_name)) {
                throw new \Exception("\DjurovicIgoor\LaraFiles\Helpers\LaraFilesHandler::166 file hash name not set!", 404);
            }
        } catch (\Exception $exception) {
            $this->setExceptionError($exception);
        }
    }

    /**
     * @param UploadedFile $file
     *
     */
    public function setName(UploadedFile $file) {

        $this->name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        try {
            if (!isset($this->name)) {
                throw new \Exception("\DjurovicIgoor\LaraFiles\Helpers\LaraFilesHandler::177 file name not set!", 404);
            }
        } catch (\Exception $exception) {
            $this->setExceptionError($exception);
        }
    }

    /**
     * @param UploadedFile $file
     *
     */
    public function setExtension(UploadedFile $file) {

        $this->extension = $file->getClientOriginalExtension();
        try {
            if (!isset($this->extension)) {
                throw new \Exception("\DjurovicIgoor\LaraFiles\Helpers\LaraFilesHandler::177 file name not set!", 404);
            }
        } catch (\Exception $exception) {
            $this->setExceptionError($exception);
        }
    }

    /**
     * @param null $message
     */
    public function setError($message = NULL) {

        $this->error = $message;
    }

    /**
     * @param Exception $exception
     */
    public function setExceptionError(Exception $exception) {

        $this->error = $exception->getMessage();
    }

    /**
     * @param bool   $storage
     * @param string $path
     * @param bool   $fullPath
     *
     * @return string
     */
    public static function setPath($storage, $path, $fullPath = FALSE) {

        if ($fullPath) {
            if ($storage) {
                return storage_path($path);
            } else {
                return public_path($path);
            }
        } else {
            return $path;
        }
    }

    /**
     * has error
     *
     * @return bool
     */
    public function hasError() {

        if ($this->error) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * get error
     *
     * @return bool
     */
    public function getError() {

        return $this->error;
    }
    /**
     * Handles removal of file
     *
     * @param $source
     *
     * @return $this
     */
    //    public function removeFile($source = NULL) {
    //
    //        try {
    //            $this->path = $source;
    //            if ($this->path && File::exists($this->path)) {
    //                File::delete($this->path);
    //            }
    //        } catch (\Exception $e) {
    //            $this->error = $e;
    //        }
    //
    //        return $this->isSuccess();
    //    }
    //    /**
    //     * set error
    //     *
    //     * @return \DjurovicIgoor\LaraFiles\Helpers\LaraFilesHandler
    //     * @throws \Exception
    //     */
    //    public static function setError() {
    //
    //        $obj = new static();
    //        try {
    //            throw new \Exception('File not provided!', 400);
    //        } catch (\Exception $e) {
    //            $obj->error = $e;
    //        }
    //
    //        return $obj;
    //    }
}