<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 20.11.17.
 * Time: 21.29
 */

namespace DjurovicIgoor\LaraFiles\Traits;

use DjurovicIgoor\LaraFiles\Helpers\LaraFilesHandler;
use DjurovicIgoor\LaraFiles\LaraFile;

trait LaraFileTrait {
	
	public $modelPath;
	public $laraErrors;
	
	/**
	 * LaraFileTrait constructor.
	 */
	public function __construct() {
		$namespaceArray   = explode( '\\', get_class() );
		$this->modelPath  = config( 'lara-files.default_folder' ) . '/' . strtolower( end( $namespaceArray ) );
		$this->laraErrors = collect();
	}
	
	/**
	 * @param      $files
	 * @param null $description
	 * @param null $user
	 * @param int  $type
	 * @param null $storage
	 * @return $this|null
	 */
	public function addFiles( $files, $description = NULL, $user = NULL, $type = 1, $storage = NULL ) {
		if(!is_null( $storage )) {
			$this->storage = $storage;
		}
		
		if(!isset( $files )) {
			return NULL;
		}
		
		if(!is_array( $files )) {
			$files = [$files];
		}
		
		$files = collect( $files );
		
		$files->each( function( $file ) use ( $description, $user ) {
			
			$LaraFilesHandler = LaraFilesHandler::uploadPath( $this->setPath() )->addFile( $file );
			if($LaraFilesHandler->errors->isEmpty()) {
				$laraFile = $this->storeFile( $LaraFilesHandler, $description, $user );
				$this->laraFiles()->save( $laraFile );
			} else {
				$this->laraErrors->push( $LaraFilesHandler );
			}
			
		} );
		
		
		if($this->laraErrors->isNotEmpty()) {
			return $this;
		} else {
			return NULL;
		}
	}
	
	/**
	 * @return string
	 */
	public function setPath() {
		if($this->storage) {
			$path = storage_path( $this->modelPath );
		} else {
			$path = public_path( $this->modelPath );
		}
		
		return $path;
	}
	
	/**
	 * @param \DjurovicIgoor\LaraFiles\helpers\LaraFilesHandler $LaraFilesHandler
	 * @param null                                              $description
	 * @param null                                              $user
	 * @return \DjurovicIgoor\LaraFiles\LaraFile
	 */
	public function storeFile( LaraFilesHandler $LaraFilesHandler, $description = NULL, $user = NULL ) {
		return new LaraFile( [
			'path'               => $this->modelPath . '/' . $this->attributes['id'] . '/',
			'hash_name'          => $LaraFilesHandler->hash_name,
			'name'               => $LaraFilesHandler->name,
			'mime'               => $LaraFilesHandler->mime,
			'type'               => 1,
			'larafilesable_type' => get_class(),
			'larafilesable_id'   => $this->id,
			'description'        => $description,
			'author_id'          => !is_null( $user ) ?: $user->id,
		] );
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function laraFiles() {
		
		return $this->morphMany( LaraFile::class, 'larafilesable' );
	}
	
}