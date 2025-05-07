<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lara_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('disk')->default(config('lara-files.default_disk'))->comment('Disk Adapter must be defined in your config/filesystems.php');
            $table->string('path')->nullable()->comment('Relative file path.');
            $table->string('hash_name')->nullable()->comment('Hashed name of the file.');
            $table->string('extension')->nullable()->comment('Extension of the file.');
            $table->string('name')->nullable()->comment('Original name of the file.');
            $table->string('type')->nullable()->comment('Category of file. I.e. avatar, thumbnail, documents, etc.');
            $table->string('visibility')->default(config('lara-files.visibility'))->comment('Browser visibility of the file.');
            $table->text('description')->nullable()->comment('Description of the file.');
            $table->unsignedInteger('author_id')->nullable()->comment('Author of the file.');
            $table->string('larafilesable_type')->comment('Name of the belonging model.');
            $table->integer('larafilesable_id')->comment('Id of the belonging model.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lara_files');
    }
};
