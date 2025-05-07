# LaraFiles

![Packagist Downloads](https://img.shields.io/packagist/dt/djurovicigoor/lara-files?label=Total%20downloads)
![Packagist Version](https://img.shields.io/packagist/v/djurovicigoor/lara-files?label=Latest%20version)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Run tests](https://github.com/djurovicigoor/lara-files/actions/workflows/run-tests.yml/badge.svg)](https://github.com/djurovicigoor/lara-files/actions/workflows/run-tests.yml)
[![Fix Code Style](https://github.com/djurovicigoor/lara-files/actions/workflows/pint.yml/badge.svg)](https://github.com/djurovicigoor/lara-files/actions/workflows/pint.yml)

📦 Lara Files

Lara Files is a lightweight and flexible Laravel package for handling file uploads for Eloquent models.

It provides a fully-featured LaraFile model, a powerful upload service, and clean integration via traits—allowing you
to easily attach files (images, documents, etc.) to any model in your application.

Built for Laravel 10, 11, and 12 support in mind, and it's ideal for modern
Laravel projects that need organized file management.

<p align="center">
<img height="300" src="https://repository-images.githubusercontent.com/111447584/a5686a8f-a040-49e9-8bf9-bbf14ffeef70" 
alt="lara-files preview" />
</p>

- [Chose version](#versions)
- [Installation](#installation)
- [Configurations](#config-file)
- [Database setup](#preparing-the-database)
- [LaraFile model](#larafile-model)
- [Multiple disks](#multiple-disks-support)
- [Preparing your model](#preparing-your-model)
- [Associating HTTP file](#associating-http-filefiles)
- [Associating base64 representation of file/files](#associating-base64-representation-of-filefiles)
- [Ordering files](#ordering-files)
- [Upload without model (temp file)](#upload-file-without-model-temp-file)
- [Custom properties](#using-custom-properties)
- [Change log](#change-log)
- [Contributing](#contributing)
- [Security](#security)
- [Donate](#donate)
- [Credits](#credits)
- [Contributors](#contributors)
- [License](#license)

# Versions

| Lara files                                                      | Laravel     |
|-----------------------------------------------------------------|-------------|
| [v2.x](https://github.com/djurovicigoor/lara-files/tree/master) | 10.x - 12.x |
| [v1.x](https://github.com/djurovicigoor/lara-files/tree/v1.x)   | 5.5 - 9.x   |

# Installation

Lara files can be installed via Composer:

``` bash
composer require djurovicigoor/lara-files
```

The service provider will automatically get registered if not you can manually add it in your
`bootstrap/providers.php` file:

```php
return [
    App\Providers\AppServiceProvider::class,
    ...
    DjurovicIgoor\LaraFiles\LaraFilesProvider::class,
];
```

Now you can publish a service provider:

```bash
php artisan vendor:publish --provider="DjurovicIgoor\LaraFiles\LaraFileServiceProvider"
```

# Config file

This is the default content of the config file:

```php
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

```

# Preparing the database

You need to run migrations to create `lara_files` table

``` bash
php artisan migrate
```

# LaraFile model

The LaraFile model provides one MorphToMany relation by default.
If you want to use more than default `laraFile()` relation, you should modify types array in `config/lara-files.php`

```php
/*
|--------------------------------------------------------------------------
| Type of files - relations
|--------------------------------------------------------------------------
*/
'types' => [
    'file', 'avatar', 'thumbnail',
],
```

Every type you add in this array, represents the new relation with LaraFile model but filtered with certain type.
For avatar example you have all those methods available:

- `avatar()` - Return Morph to One relations query builder
- `avatars()` - Return Morph to Many relations query builder
- `getAvatar()` - Return single model of related LaraFile model
- `getAvatars()` - Return Collection of related LaraFile models

All of those methods and properties are applicable for any type in this types array.

# Multiple disks support

By default, the library will store its files on disk from the config/env file. If you want a dedicated disk you should
add a disk to config/filesystems.php. Library support Laravel's default disk system and all disk with s3 driver. If
you want to use disk with s3 driver you have to
install [league/flysystem-aws-s3-v3](https://github.com/thephpleague/flysystem-aws-s3-v3)

```php
'disks' => [
	'local' => [
		'driver' => 'local',
		'root'   => storage_path('app/private'),
		'serve'  => TRUE,
		'throw'  => FALSE,
		'report' => FALSE,
	],
	'public' => [
		'driver'     => 'local',
		'root'       => storage_path('app/public'),
		'url'        => env('APP_URL') . '/storage',
		'visibility' => 'public',
		'throw'      => FALSE,
		'report'     => FALSE,
	],
     's3' => [
        'driver' => 's3',
        'key'       => env('AWS_ACCESS_KEY_ID' , 'Your aws acces key goes here'),
        'secret'    => env('AWS_SECRET_ACCESS_KEY' , 'Your aws secret key goes here'),
        'region'    => env('AWS_DEFAULT_REGION' , 'Your aws default region goes here'),
        'bucket'    => env('AWS_BUCKET' , 'Your aws bucket name goes here'),
        'url'       => env('AWS_URL' , 'https://s3.{REGION}.amazonaws.com/{BUCKET}/'),
    ],
    'DOSpaces' => [
        'driver'   => 's3',
        'key'      => env('DO_SPACES_KEY' , 'Your spaces key goes here'),
        'secret'   => env('DO_SPACES_SECRET' , 'Your spaces secret goes here'),
        'region'   => env('DO_SPACES_REGION' , 'Your spaces region goes here'),
        'bucket'   => env('DO_SPACES_BUCKET' , 'Your spaces bucket goes here'),
        'url'      => env('AWS_URL' , 'https://{BUCKET}.{REGION}.digitaloceanspaces.com/'),
    ]
],
```

# Preparing your model

To associate files with a model, the model must implement the following trait:

```php
use DjurovicIgoor\LaraFiles\Traits\LaraFileTrait;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    
    use LaraFileTrait;
    // ...
}
```

# Associating HTTP file/files

You can associate a file with a model like this:

```php
$post = Post::find($id);
$uploadedFile = $post->uploadHttpFile($request->file('image'), 'avatar')
```

These two parameters are required and file will be directly uploaded to disk you set in config/lara-files.php. This
method will return LaraFile model. If you want to customize the upload process, you can use addHttpFile() method
instead. This method will be return
service object which you can use to customize the upload process.

```php
$post = Post::find($id);
$fileUploadObject = $post->addHttpFile($request->file('image'), 'avatar')
```

You can chain multiple methods on `$fileUploadObject` object:

### Custom disk

You can set custom disk but disk must be presented in `config/filesystems.php` disks array.

```php
$fileUploadObject->setDisk('s3')
```

### Custom visibility

You can set visibility of uploaded file, visibility must be one of `['public', 'private']`

```php
$fileUploadObject->setVisibility('private')
``` 

### Custom name

Set custom name for file without extension

```php
$fileUploadObject->setName('custom-file-name')
```       

### Custom properties

Custom properties are expected as array and will be stored as json string in database.

```php
$fileUploadObject->setProperties([
    'description' => 'Some description',
    'author' => 'John Doe',
]);
```

At the end, to finish upload process you have to call `upload()` method without any parameters.

```php
$fileUploadObject->upload();
```

If you need to upload multiple files with same parameters, you can use `uploadHttpFiles()` method instead.

```php
$post = Post::find($id);
$uploadedFilesCollection = $post->uploadHttpFiles($request->file('images'), 'avatar')
```

# Associating base64 representation of file/files

You can associate a base64 representation of file with a model like this:

```php
$post = Post::find($id);
$uploadedFile = $post->uploadBase64File($request->get('image'), 'avatar')
```

These two parameters are required and file will be directly uploaded to disk you set in config/lara-files.php. This
method will return LaraFile model. If you want to customize the upload process, you can use addBase64File() method
instead. This method will be return service object which you can use to customize the upload process.

```php
$post = Post::find($id);
$fileUploadObject = $post->addBase64File($request->get('image'), 'avatar')
```

You can chain multiple methods on `$fileUploadObject` object:

### Custom disk

You can set custom disk but disk must be presented in `config/filesystems.php` disks array.

```php
$fileUploadObject->setDisk('s3')
```

### Custom visibility

You can set visibility of uploaded file, visibility must be one of `['public', 'private']`

```php
$fileUploadObject->setVisibility('private')
``` 

### Custom name

Set custom name for file without extension

```php
$fileUploadObject->setName('custom-file-name')
```       

### Custom properties

Custom properties are expected as array and will be stored as json string in database.

```php
$fileUploadObject->setProperties([
    'description' => 'Some description',
    'author' => 'John Doe',
]);
```

At the end, to finish upload process you have to call `upload()` method without any parameters.

```php
$fileUploadObject->upload();
```

If you need to upload multiple files with same parameters, you can use `uploadBase64Files()` method instead.

```php
$post = Post::find($id);
$uploadedFilesCollection = $post->uploadBase64Files($request->file('images'), 'avatar')
```

This method will return Collection of LaraFile models.

# Upload file without model (temp file)

If you need to upload and store file without certain model you need to call the static method of the
`LaraFileUploader` class:

```php
$tempLaraFile = LaraFileUploader::uploadForOptimizationAndManipulation(
    uploadedFile: $request->file('file'),
    fileUploaderType: 'http_file',
    type: 'image',
    disk: 'local',
    visibility: 'private',
    name: 'Some image image',
    customProperties: ['description' => 'Lorem ipsum']
);
```

# Ordering files

This package has a built-in feature to help you order the files in your project. By default, all inserted files
are arranged in order by their time of creation (from the oldest to the newest) using the order column of the
lara-files table.

You can easily reorder a list of media by calling `LaraFile::setNewOrder`. This function reorders the records so that
the record with the first ID in the array receives the starting order (default is 1), the second ID gets starting
order + 1, and so on. An optional starting order value can be provided as second parameters.

```php
LaraFile::setNewOrder(['d317a690-cc51-4cf4-a3ca-a11c1e7a8673','c7c2c5a0-9e0c-459f-baf2-b9fa02202acd']);
```

Of course, you can also manually change the value of the order.

```php
$laraFile->order = 10;
$laraFile->save();
```

# Using custom properties

When adding a file to the model you can pass an array with custom properties:

```php
$post = Post::find($id);
$post->addHttpFile($request->file('image'), 'avatar')
     ->setProperties([
            'description' => 'Some description',
            'author' => 'John Doe',
        ])
     ->upload();
```

There are some methods to work with custom properties:

```php
$laraFile->hasCustomProperty('author'); // returns true
$laraFile->getCustomProperty('author'); // returns 'John Doe'

$laraFile->hasCustomProperty('does not exist'); // returns false
$laraFile->getCustomProperty('does not exist'); // returns null

$laraFile->setCustomProperty('name', 'value'); // adds a new custom property
$laraFile->forgetCustomProperty('name'); // removes a custom property
```

If you are setting or removing custom properties outside the process of adding media then you will need to persist/save
these changes:

```php
$laraFile = LaraFile::find($id);
   
$laraFile->setCustomProperty('name', 'value'); // adds a new custom property or updates an existing one
$laraFile->forgetCustomProperty('name'); // removes a custom property

$laraFile->save();
```

You can also specify a default value when retrieving a custom property.

```php
$laraFile->getCustomProperty('is_public', false);
```

If you're dealing with nested custom properties, you can use dot notation.

```php
$laraFile->hasCustomProperty('group.primaryColor'); // returns true
$laraFile->getCustomProperty('group.primaryColor'); // returns 'red'

$laraFile->hasCustomProperty('nested.does-not-exist'); // returns false
$laraFile->getCustomProperty('nested.does-not-exist'); // returns null

```

# LaraFile model methods

When you get LaraFile model instance, you have access to certain attributes and methods for current file:

```php
$avatar = User::find($id)->avatar;
```

You have full url accessible trough browser for all disk except if file is stored using `local` disk.

```php
$avatar->url;
```

You can get full path.

```php
$avatar->full_path;
```

You can get size of the file in bytes.

```php
$avatar->size;
```

You can get mime type of the file.

```php
$avatar->mime_type;
```

You can get last modified at time of the file.

```php
$avatar->last_modified;
```

Calling `getContents` method you can get contents for the file.

```php
$avatar->getContents();
```

Calling `download` method you can directly download the file.

```php
$avatar->download();
```

If you want to move file to another disk you need to call `changeDisk` method.

```php
$avatar->changeDisk('s3');
```

If you need to change visibility of the file you have to call `changeVisibility` method.

```php
$avatar->changeVisibility('public');
```

Using the `getTemporaryUrl` method, you may create temporary URLs to files stored using the local and s3 drivers.
This method accepts DateTime instance specifying when the URL should expire and additional S3 request parameters, you
may pass the array of request parameters as the second argument to the getTemporaryUrl method:

```php
$avatar->getTemporaryUrl(now()->addMinutes(30));
```

# Change log

Please see the [changelog.md](CHANGELOG.md) for more information on what has changed recently.

# Contributing

Please see [contributing.md](CONTRIBUTING.md) for details and a todolist.

# Security

If you discover any security related issues, please email djurovic.igoor@gmail.com instead of using the issue tracker.

# Donate

If you found this project helpful or you learned something from the source code and want to appreciate

- [PayPal](https://paypal.me/djurovicigoor?locale.x=en_US)

# Credits

- [Djurovic Igor](https://github.com/djurovicigoor)

# Contributors

| Name                                   |                                                               Changes                                                                |    Date    |
|----------------------------------------|:------------------------------------------------------------------------------------------------------------------------------------:|:----------:|
| [@niladam](https://github.com/niladam) |             Refactor to PSR2 and simplify accessor [pull request #2](https://github.com/djurovicigoor/lara-files/pull/2)             | 2019-05-18 |
| [@chefe](https://github.com/chefe)     |                 Fix typo in exception message  [pull request #5](https://github.com/djurovicigoor/lara-files/pull/5)                 | 2019-05-23 |
| [@omly97](https://github.com/omly97)   | Laravel 10 - Fix - Error Call to undefined function str_plural [pull request #6](https://github.com/djurovicigoor/lara-files/pull/6) | 2023-08-17 |

# License

MIT. Please see the [license file](LICENSE.md) for more information.