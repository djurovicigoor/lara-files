<?php

use DjurovicIgoor\LaraFiles\Classes\LaraFileUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uploads a file without model in local storage', function () {
    Storage::fake('local');

    $file = UploadedFile::fake()->image('avatar.jpg');

    $uploadedFile = (new LaraFileUploader($file))->setDisk('local')->setType('avatar')->upload();
  dd($uploadedFile);
    Storage::disk('local')->assertExists($uploadedFile->data_path);

    $this->assertDatabaseHas('lara_files', [
        'id' => $uploadedFile->id,
    ]);
});