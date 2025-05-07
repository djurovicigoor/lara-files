# Upgrading

## v1.x to v2.x

### Composer version

First things first bump the version in `composer.json` to `^2.0`

```composer log
"djurovicigoor/lara-files": "^2.0",
```

Please keep in mind if you want to use aws s3 driver, you have to
install [league/flysystem-aws-s3-v3](https://github.com/thephpleague/flysystem-aws-s3-v3) manually now. Add it into
`composer.json`

```composer log
"league/flysystem-aws-s3-v3": "^3.0",
```

and run `composer update`

### Publish service provider

Next step is to publish service provider.

```bash
php artisan vendor:publish --provider="DjurovicIgoor\LaraFiles\LaraFileServiceProvider"
```

### Update config file

Update content of the `config/lara-files.php` file, please keep in mind to use your old types

```php
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
      // Add your types from previus version here
    ],
];
```

### Reference to LaraFile model (optional)

Since new version is using UUID as a primary key, you have to update your reference to `lara_files` table.
Inside new migration file you have to add your implementation for referencing to `lara_files` table in place where this
comment is located in file.

```php
// If you have any reference to lara_files_table . id in other tables , update here any FK columns and copy matching UUIDs .
```

### Running migration

```bash
php artisan migrate
```

### Update implementation of `uploadHttpFile`

```php
$post = Post::find($id);

$post->uploadHttpFile('public', $request->file('image'), 'image', [
	    'name'        => $request->file('image')->getClientOriginalName(),
	    'visibility'  => 'public',
	    'description' => Post::class . ' image',
	    'author_id'   => \auth()->id(),
	]);
```

Since version 2 `uploadHttpFile` now require only 2 parameters and above implementation is not valid anymore.

```php
public function uploadHttpFile(UploadedFile $uploadedFile, string $type, ?string $disk = null, $visibility = null, $name = null, array $customProperties = [])
```

New implementation have to look like this:

```php
$post = Post::find($id);

$post->uploadHttpFile(
uploadedFile: $request->file('image'),
type: 'image',
disk: 'public',
visibility: 'public',
name: $request->file('image')->getClientOriginalName(),
customProperties: ['description' => Post::class . ' image', 'author_id'   => \auth()->id()]);
```