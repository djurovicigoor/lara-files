<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 2019-04-18
 * Time: 14:53
 */

namespace DjurovicIgoor\LaraFiles\Classes;

use DjurovicIgoor\LaraFiles\LaraFile;

abstract class UploadFile {
    
    public $laraFile;
    
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
    public function __construct($disk, $path, $type, $visibility, $user, $description) {
        
        $this->laraFile = new LaraFile([
            'disk'        => $disk,
            'path'        => $path,
            'type'        => $type,
            'visibility'  => $visibility,
            'description' => $description,
            'author_id'   => !is_null($user) ?: $user->id,
        ]);
    }
}
