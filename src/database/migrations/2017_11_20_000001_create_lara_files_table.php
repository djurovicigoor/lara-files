<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaraFilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('lara_files', function (Blueprint $table) {

            $table->increments('id');
            $table->string('path')->nullable();
            $table->string('hash_name')->nullable();
            $table->string('name')->nullable();
            $table->string('extension')->nullable();
            $table->string('type')->nullable()->comment();
            $table->string('larafilesable_type')->nullable();
            $table->integer('larafilesable_id')->default(0);
            $table->text('description')->nullable();
            $table->unsignedInteger('author_id')->nullable();
            $table->boolean('storage')->default(config('lara-files.storage'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('lara_files');
    }
}
