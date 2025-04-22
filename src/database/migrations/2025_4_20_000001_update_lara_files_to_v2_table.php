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
            $table->string('larafilesable_type')->nullable()->change();
            $table->string('larafilesable_id')->nullable()->change();
        });

        DB::table('lara_files')->orderBy('id')->chunk(50, function ($items) {
            $items->each(function ($item) {
                if (! empty($item->id)) {
                    DB::table('lara_files')->where('id', $item->id)->update([
                        'uuid' => (string) Str::uuid(),
                    ]);
                }
            });
        });

        // If you have any reference to lara_files_table . id in other tables , update here any FK columns and copy matching UUIDs .

        DB::statement('ALTER TABLE lara_files MODIFY id BIGINT UNSIGNED NOT NULL;');
        DB::statement('ALTER TABLE lara_files DROP PRIMARY KEY;');

        Schema::table('lara_files', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('lara_files', function (Blueprint $table) {
            $table->renameColumn('uuid', 'id');
            $table->uuid('id')->primary()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
