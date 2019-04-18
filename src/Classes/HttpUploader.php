<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 2019-04-18
 * Time: 09:19
 */

namespace DjurovicIgoor\LaraFiles\Classes;

use DjurovicIgoor\LaraFiles\Contracts\UploaderInterfaces;
use DjurovicIgoor\LaraFiles\Traits\HashNameTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class HttpUploader extends Uploader implements UploaderInterfaces {
    
    use HashNameTrait;
    
    /**
     * HttpFile constructor.
     *
     * @param $disk
     * @param $path
     * @param $type
     * @param $visibility
     * @param $user
     * @param $description
     */
    public function __construct($disk, $path, $type, $additionalParameters) {
        
        parent::__construct($disk, $path, $type, $additionalParameters);
    }
    
    /**
     * @param $file
     *
     * @return mixed|void
     */
    public function move($file) {
        
        $this->getFileOriginalName($file);
        Storage::disk($this->laraFile->disk)->put("{$this->laraFile->path}/{$this->generateHashName()}.{$this->getFileExtension($file)}", File::get($file));
        Storage::disk($this->laraFile->disk)->setVisibility("{$this->laraFile->path}/{$this->laraFile->hash_name}.{$this->laraFile->extension}", $this->laraFile->visibility);
    }
    
    /**
     * @param $file
     *
     * @return mixed
     */
    public function getFileExtension($file) {
        
        return $this->laraFile->extension = $file->getClientOriginalExtension();
    }
    
    /**
     * @param $file
     *
     * @return mixed
     */
    public function getFileOriginalName($file) {
        
        return $this->laraFile->name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);;
    }
}
