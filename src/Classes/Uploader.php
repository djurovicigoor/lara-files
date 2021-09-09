<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 2019-04-18
 * Time: 14:53
 */

namespace DjurovicIgoor\LaraFiles\Classes;

use DjurovicIgoor\LaraFiles\LaraFile;

abstract class Uploader
{
    public $laraFile;

    /**
     * HttpFile constructor.
     *
     * @param $disk
     * @param $path
     * @param $type
     * @param $additionalParameters
     */
    public function __construct($disk, $path, $type, $additionalParameters)
    {
        $this->laraFile = new LaraFile([
            'disk'        => $disk,
            'path'        => $path,
            'type'        => $type,
            'name'        => array_key_exists('name', $additionalParameters) ? $additionalParameters['name'] : null,
            'visibility'  => array_key_exists('visibility', $additionalParameters) ? $additionalParameters['visibility'] : config('lara-files.public'),
            'description' => array_key_exists('description', $additionalParameters) ? $additionalParameters['description'] : null,
            'author_id'   => array_key_exists('user', $additionalParameters) ? $additionalParameters['user']->id : null,
        ]);
    }
}
