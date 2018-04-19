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
        ];
    }

    /**
     * Creates a directory
     *
     */
    public function createFolder() {

        try {
            try {
                File::makeDirectory($this->path, 0777, TRUE);
            } catch (\Exception $exception) {
                throw new \Exception("Couldn't create $this->path");
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

        if (!File::isWritable($this->path)) {
            File::chmod($this->path, 0777);
        }
    }

    /**
     * @return bool
     */
    public function ifFolderExist() {

        if (File::exists($this->path)) {
            $this->setPermission();
        } else {
            $this->createFolder();
        }

        return $this->hasError();
    }

    /**
     * Handles files upload
     *
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return LaraFilesHandler
     */
    public function addFile(UploadedFile $file = NULL) {

        if (isset($file)) {
            if (!$this->ifFolderExist()) {
                $this->setHashName();
                $this->setName($file);
                $this->setExtension($file);
                if (!$this->hasError()) {
                    try {
                        $file->move($this->path, $this->hash_name . '.' . $file->getClientOriginalExtension());
                    } catch (\Exception $exception) {
                        $this->setExceptionError($exception);
                    }
                }

                return $this;
            }
        } else {
            $this->setError("File not provided!");
        }
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
     * @param Exception $exception
     */
    public function setExceptionError(Exception $exception) {

        $this->error = $exception->getMessage();
    }

    /**
     * @param null $message
     */
    public function setError($message = NULL) {

        $this->error = $message;
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
    /**
     * Sets a new upload path
     *
     * @param $path
     *
     * @return $this
     */
    public static function uploadPath($path) {

        $obj       = new static();
        $obj->path = $path;
        if ($obj->path) {
            File::makeDirectory($obj->path, 0777, TRUE, TRUE);
        }

        return $obj;
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