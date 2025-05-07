<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default disk
    |--------------------------------------------------------------------------
    |
    | The disk on which to store added files by default. Choose
    | one of the disks you've configured in config/filesystems.php.
    */
    'default_disk' => env('LARA_FILE_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Default visibility
    |--------------------------------------------------------------------------
    |
    | public  => Files are accessible through browser
    | private => Files are not accessible through browser
    */
    'visibility' => env('LARA_FILE_VISIBILITY', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Type of files - relations
    |--------------------------------------------------------------------------
    */
    'types' => [
        'file', 'avatar', 'thumbnail',
    ],
];
