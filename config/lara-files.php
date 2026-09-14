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
    | Temporary URL expiration
    |--------------------------------------------------------------------------
    |
    | Minutes a temporary URL stays valid. The url attribute of a private
    | file on a disk that can sign URLs (s3, DigitalOcean Spaces) is a
    | temporary URL that expires after this many minutes.
    */
    'temporary_url_expiration' => env('LARA_FILE_TEMPORARY_URL_EXPIRATION', 120),

    /*
    |--------------------------------------------------------------------------
    | Type of files - relations
    |--------------------------------------------------------------------------
    */
    'types' => [
        'file', 'avatar', 'thumbnail',
    ],
];
