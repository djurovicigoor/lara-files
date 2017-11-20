<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 20.11.17.
 * Time: 21.29
 */

namespace DjurovicIgoor\LaraFiles\Traits;

use DjurovicIgoor\LaraFiles\LaraFile;

trait LaraFileTrait {

	/**
	 * @param     $files
	 * @param int $type
	 *
	 * @return $this
	 * @internal param $fileable
	 * @internal param $commentable
	 */
	public function file($files, $type = 1) {

		if (!is_array($files)) {
			$files = [$files];
		}
		$files = collect($files);
		$path  = 'uploads/' . strtolower(substr(get_class(), strpos(get_class(), '\\') + 1)) . '/' . $this->id;
		if ($files->isNotEmpty()) {
			foreach ($files as $value) {
				$fileHandler = new FileHandler;
				$fileHandler->uploadPath(public_path($path))->addFile($value);
				$file = new LaraFile([
					'path'               => $path,
					'hash_name'          => $fileHandler->hashName,
					'name'               => $fileHandler->originalName,
					'mime'               => $fileHandler->originalExtension,
					'type'               => $type,
					'larafilesable_type' => get_class(),
					'larafilesable_id'   => $this->id,
					'description'        => '',
				]);
				$this->files()->save($file);
			}
		}

		return $this;
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function files() {

		return $this->morphMany(LaraFile::class, 'larafilesable')->where('type', 1);
	}

}