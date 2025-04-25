<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lara_files', function (Blueprint $table) {
            $table->uuid()->nullable()->after('id');
            $table->unsignedInteger('order')->nullable()->index()->after('visibility');
            $table->string('larafilesable_type')->nullable()->index()->change();
            $table->string('larafilesable_id')->nullable()->index()->change();
            $table->json('custom_properties')->after('larafilesable_id');
            $table->string('type')->index()->change();
        });

        DB::table('lara_files')->orderBy('id')->chunk(50, function ($items) {
            $items->each(function ($item) {
                $customProperties = [];
                if ($item->description) {
                    $customProperties['description'] = $item->description;
                }
                if ($item->author_id) {
                    $customProperties['author_id'] = $item->author_id;
                }
                DB::table('lara_files')->where('id', $item->id)->update([
                    'uuid' => (string) Str::uuid(), 'custom_properties' => $customProperties,
                ]);
            });
        });

        if (DB::getDriverName() === 'sqlite') {
            Schema::rename('lara_files', 'lara_files_old');

            Schema::create('lara_files', function (Blueprint $table) {
                $table->increments('id'); // or uuid or whatever new type
                $table->uuid()->nullable();
                $table->string('disk')->default('public')->comment('Disk Adapter must be defined in your config/filesystems.php');
                $table->string('path')->nullable()->comment('Relative file path.');
                $table->string('hash_name')->nullable()->comment('Hashed name of the file.');
                $table->string('extension')->nullable()->comment('Extension of the file.');
                $table->string('name')->nullable()->comment('Original name of the file.');
                $table->string('type')->nullable()->comment('Category of file. I.e. avatar, thumbnail, documents, etc.');
                $table->string('visibility')->default(config('lara-files.visibility'))->comment('Browser visibility of the file.');
                $table->text('description')->nullable()->comment('Description of the file.');
                $table->unsignedInteger('author_id')->nullable()->comment('Author of the file.');
                $table->string('larafilesable_type')->nullable()->comment('Name of the belonging model.');
                $table->integer('larafilesable_id')->nullable()->comment('Id of the belonging model.');
                $table->timestamps();
            });

            DB::statement('INSERT INTO lara_files SELECT * FROM lara_files_old');
            Schema::drop('lara_files_old');
        } else {
            DB::statement('ALTER TABLE lara_files MODIFY id BIGINT UNSIGNED NOT NULL;');
            DB::statement('ALTER TABLE lara_files DROP PRIMARY KEY;');
        }

        // If you have any reference to lara_files_table . id in other tables , update here any FK columns and copy matching UUIDs .

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('lara_files', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }

        Schema::table('lara_files', function (Blueprint $table) {
            $table->renameColumn('uuid', 'id');
            $table->uuid('id')->primary()->change();
            $table->dropColumn('description');
            $table->dropColumn('author_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
