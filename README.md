# LaraFiles

[![Latest Version on Packagist](https://img.shields.io/packagist/v/djurovicigoor/lara-files.svg?style=for-the-badge)](https://packagist.org/packages/djurovicigoor/lara-files)
![Total Downloads](https://img.shields.io/packagist/dt/djurovicigoor/lara-files.svg?style=for-the-badge)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

Lara-files is a package which will make it easier to work with files. Package has built-in support for DigitalOcean spaces.
This package can be used in Laravel 5.7 or higher.   

## Installation 
You can install the package via composer:
``` bash
$ composer require djurovicigoor/lara-files
```
The service provider will automatically get registered. Or you may manually add the service provider in your `config/app.php` file:
```php
'providers' => [
    // ...
    DjurovicIgoor\LaraFiles\LaraFilesProvider::class,
];
```
Now you can publish a service provider:
```bash
php artisan vendor:publish --provider="DjurovicIgoor\LaraFiles\LaraFilesProvider"
```
After the config file and migration has been published, you can change default visibility in `config/lara-files.php` config file if you want:
```php
/*
|--------------------------------------------------------------------------
| Default visibility
|--------------------------------------------------------------------------
|
| public  => Files are accessible through a browser
| private => Files are not accessible through a browser
*/
'visibility'   => 'public',
```
After that you can create the lara-files table by running the migrations:
``` bash
$ php artisan migrate
```
## LaraFile model and Database schema 
  
The LaraFile model provides one MorphToMany relation by default.
If you want to use more than default `laraFile()` relation, you should modify types array in `config/lara-files.php` 
```php
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
```
You can modify this array as you want, add or remove an item. In this example, I have 3 types.
For each of those types, package created for you relations between your model and LaraFile model, by default.

If I use an avatar for this example, you have the next relations and properties on your model:

- `avatar()` - Return Morph to One relations query builder
- `avatars()` - Return Morph to Many relations query builder
- `getAvatar()` - Return single model of related LaraFile model
- `getAvatars()` - Return Collection of related LaraFile models

Also, you have lazy loaded relations `avatar` and `avatars` that are doing the same thing as `getAvatar()` and `getAvatars()`  methods.
```php
$avatar  = $post->avatar
$avatars = $post->avatars
```
All of those methods and properties are applicable for any type in this types array.

Database schema:
  - disk                - (string)  Disk driver of stored file. 
  - path                - (string)  Relative file path. 
  - hash_name           - (string)  Hashed name of the file. 
  - extension           - (string)  Original extension of the file. 
  - name                - (string)  Original name of the file. 
  - type                - (string)  Category of file. I.e. avatar, thumbnail, documents, etc. 
  - visibility          - (string)  Browser visibility of the file. 
  - description         - (text)    Description of the file. 
  - author_id           - (integer) Author of the file. 
  - larafilesable_type  - (string)  Name of the belonging model. 
  - larafilesable_id    - (integer) Id of the belonging model. 

## Usage
Before you start using the package, you have to check your `config/filesystems.php` file and set correct disk drivers.
Package support next drivers: 'local' , 'public' , 'DOSpaces'. Below is an example of correct disk drivers.
```php
'disks' => [
     'local' => [
        'driver'    => 'local',
        'root'      => storage_path('app'),
     ],
    'public' => [
        'driver'        => 'local',
        'root'          => storage_path('app/public'),
        'url'           => env('APP_URL').'/storage',
        'visibility'    => 'public',
    ],
    'DOSpaces' => [
        'driver'   => 's3',
        'key'      => env('DO_SPACES_KEY' , 'Your spaces key goes here'),
        'secret'   => env('DO_SPACES_SECRET' , 'Your spaces secret goes here'),
        'endpoint' => env('DO_SPACES_ENDPOINT' , 'Your spaces endpoint goes here'),
        'region'   => env('DO_SPACES_REGION' , 'Your spaces region goes here'),
        'bucket'   => env('DO_SPACES_BUCKET' , 'Your spaces bucket goes here'),
        'url'      => env('AWS_URL' , 'https://{BUCKET}.{REGION}.digitaloceanspaces.com/'),
    ],
],
```
When you setup disk drivers, add the `DjurovicIgoor\LaraFiles\Traits\LaraFileTrait` trait to your model(s):
```php
use DjurovicIgoor\LaraFiles\Traits\LaraFileTrait;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    
    use LaraFileTrait;
    // ...
}
```

After you successfully set disk drivers you have to run `php artisan storage:link` to link your `storage/app/public` with `public/storage` folder if you want to access files through browser who has 'public' driver or visibility. 
Now, you have prepared Eloquent Model for using Trait function:

## Trait functions
All functions can be called on an already stored model in the database. 
The first parameter of all functions is disk adapter one of which you already have defined in your `config/filesistems.php`.

The second parameter is explained for each function in the section below.

The third parameter of all functions is a type of file, that is some kind of category. You can pass anything for this parameter only has to be in string format. Later, all files can be categorized by these parameters. 

The fourth parameter of all functions is `$additionalParameters` variable, and should be key => value array.
```php
$additionalParameters = [
    'visibility'    => 'public',
    'description'   => 'Lorem ipsum dolor sit amet . . .',
    'author_id'     => $user->id
];
```
Every item of the above array is optional. If you want, you can pass an empty array.
#### uploadHttpFile()
With this function, you can upload a single HttpUploadedFile file and associate it with your model.
```php
$post = Post::find($id);
$post->uploadHttpFile('local', $request->file('image'), 'thumbnail', $additionalParameters = [])
```
#### uploadHttpFiles()
With this function, you can upload the array of HttpUploadedFile files and associate them with your model.
```php
$post = Post::find($id);
$post->uploadHttpFiles('local', $arrayOfHttpUploadedFiles, 'thumbnail', $additionalParameters = [])
```
#### uploadBase64File()
With this function, you can upload a single base64 file and associate it with your model.
```php
$post = Post::find($id);
$post->uploadBase64File('local', $base64String, 'thumbnail', $additionalParameters = [])
```
#### uploadBase64Files()
With this function, you can upload the array of base64 files and associate them with your model.
```php
$post = Post::find($id);
$post->uploadBase64Files('local', $arrayOfBase64String, 'thumbnail', $additionalParameters = [])
````
