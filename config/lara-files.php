<?php

/**
 * config/lara-files.php
 * Lara-files configuration file.
 * Created by PhpStorm.
 * Date: 20.11.17.
 * Time: 19.45
 *
 * @author  Djurovic Igor djurovic.igoor@gmail.com
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Default visibility
    |--------------------------------------------------------------------------
    |
    | public  => Files are accessible through browser
    | private => Files are not accessible through browser
    */
    'visibility' => 'public',
    /*
    |--------------------------------------------------------------------------
    | Type of files - relations
    |--------------------------------------------------------------------------
    |
    */
    'types' => [
        'file',
        'avatar',
        'thumbnail',
    ],
    /*
    |--------------------------------------------------------------------------
    | Use author
    |--------------------------------------------------------------------------
    |
    */
    'author' => true,
    /*
    |--------------------------------------------------------------------------
    | Author model
    |--------------------------------------------------------------------------
    |
    */
    'author_model' => "App\Models\User",
];
