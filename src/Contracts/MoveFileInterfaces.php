<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 2019-04-18
 * Time: 08:58
 */

namespace DjurovicIgoor\LaraFiles\Contracts;

interface MoveFileInterfaces {
    
    /**
     * @param $file
     *
     * @return mixed
     */
    public function move($file);
    
    /**
     * @param $file
     *
     * @return mixed
     */
    function getFileExtension($file);
    /**
     * @param $file
     *
     * @return mixed
     */
    function getFileOriginalName($file);
    
}
