<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use DjurovicIgoor\LaraFiles\Classes\LaraFileUploader;
use Djurovicigoor\LaraFiles\Tests\TestSupport\TestModels\TestModel;
use DjurovicIgoor\LaraFiles\Exceptions\VisibilityIsNotValidException;
use DjurovicIgoor\LaraFiles\Exceptions\UnsupportedDiskAdapterException;
use DjurovicIgoor\LaraFiles\Exceptions\FileTypeIsNotPresentedException;

beforeEach(function () {
    Storage::fake('local');
});

it('uploads a file without model in local storage using package main class', function () {
    $file = UploadedFile::fake()->image('avatar.jpg');
    
    $uploadedFile = (new LaraFileUploader($file))->setDisk('local')->setType('avatar')->upload();
    
    Storage::disk('local')->assertExists($uploadedFile->data_path);
    
    $this->assertDatabaseHas('lara_files', [
            'id' => $uploadedFile->id,
    ]);
});

it('uploads a file and attach it to test model using package main class', function () {
    $file      = UploadedFile::fake()->image('avatar.jpg');
    $testModel = TestModel::first();
    
    $uploadedFile = (new LaraFileUploader($file))->setDisk('local')->setType('avatar')->setModel($testModel)->upload();
    
    Storage::disk('local')->assertExists($uploadedFile->data_path);
    
    $this->assertDatabaseHas('lara_files', [
            'id' => $uploadedFile->id, 'larafilesable_type' => TestModel::class, 'larafilesable_id' => $testModel->id,
    ]);
});

it('fails to upload file with invalid visibility', function () {
    $file      = UploadedFile::fake()->image('avatar.jpg');
    
    (new LaraFileUploader($file))->setVisibility('foo');
    
})->throws(VisibilityIsNotValidException::class);

it('fails to upload file with invalid disk', function () {
    $file      = UploadedFile::fake()->image('avatar.jpg');
    
    (new LaraFileUploader($file))->setDisk('foo');
    
})->throws(UnsupportedDiskAdapterException::class);

it('fails to upload file with empty type', function () {
    $file      = UploadedFile::fake()->image('avatar.jpg');
    
    (new LaraFileUploader($file))->setType('');
    
})->throws(FileTypeIsNotPresentedException::class);