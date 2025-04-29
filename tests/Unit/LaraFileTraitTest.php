<?php

use Djurovicigoor\LaraFiles\Tests\TestSupport\TestModels\TestModel;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('local');
});

it('it uploads a file using trait', function () {
    $file = UploadedFile::fake()->image('avatar.jpg');
    $testModel = TestModel::first();

    $uploadedFile = $testModel->uploadHttpFile($file, 'avatar');

    Storage::disk('local')->assertExists($uploadedFile->data_path);

    $this->assertDatabaseHas('lara_files', [
        'id' => $uploadedFile->id, 'larafilesable_type' => TestModel::class, 'larafilesable_id' => $testModel->id,
    ]);
});

it('it uploads multiple files using trait', function () {

    $files = [UploadedFile::fake()->image('avatar1.jpg'), UploadedFile::fake()->image('avatar2.jpg'), UploadedFile::fake()->image('avatar3.jpg')];
    $testModel = TestModel::first();

    $uploadedFiles = $testModel->uploadHttpFiles($files, 'avatar');

    foreach ($uploadedFiles as $uploadedFile) {
        Storage::disk('local')->assertExists($uploadedFile->data_path);

        $this->assertDatabaseHas('lara_files', [
            'id' => $uploadedFile->id, 'larafilesable_type' => TestModel::class, 'larafilesable_id' => $testModel->id,
        ]);
    }
});

it('it add file with trait function and the upload it', function () {

    $file = UploadedFile::fake()->image('avatar.jpg');
    $testModel = TestModel::first();

    $uploadedFile = $testModel->addHttpFile($file, 'avatar');
    $uploadedFile->setVisibility('public')->setName('avatar-avatar')->setCustomProperties(['description' => 'Lorem ipsum dolor sit amet.']);

    $uploadedFile = $uploadedFile->upload();

    Storage::disk('local')->assertExists($uploadedFile->data_path);

    $this->assertDatabaseHas('lara_files', [
        'id' => $uploadedFile->id, 'larafilesable_type' => TestModel::class, 'larafilesable_id' => $testModel->id,
    ]);
});
