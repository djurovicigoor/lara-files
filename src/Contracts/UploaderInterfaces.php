<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 2019-04-18
 * Time: 08:58
 */

namespace DjurovicIgoor\LaraFiles\Contracts;

interface UploaderInterfaces
{
    /**
     * @param $file
     */
    public function putFile($file);

    /**
     * @param $file
     *
     * @return string
     */
    public function getFileExtension($file);

    /**
     * @param $file
     *
     * @return string
     */
    public function getFileOriginalName($file);
}
