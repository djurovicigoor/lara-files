<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 20.11.17.
 * Time: 21.51
 */

namespace DjurovicIgoor\LaraFiles\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class LaraFilesHandler {
	
	public $file;
	public $path;
	public $hash_name;
	public $name;
	public $mime;
	public $type;
	public $description;
	public $storage;
	public $error;
	
	/**
	 * Default constructor
	 *
	 * @param null $uploadPath
	 */
	public function __construct( $uploadPath = NULL ) {
		$this->file        = NULL;
		$this->path        = $uploadPath;
		$this->hash_name   = NULL;
		$this->name        = NULL;
		$this->mime        = NULL;
		$this->type        = NULL;
		$this->description = NULL;
		$this->storage     = NULL;
		$this->error       = NULL;
	}
	
	/**
	 * Creates a directory
	 *
	 * @param $path
	 */
	public static function makeDir( $path ) {
		File::exists( $path ) or File::makeDirectory( $path, 0777, TRUE );
	}
	
	/**
	 * Copies a file from source
	 * to destination
	 *
	 * @param $source
	 * @param $destination
	 * @return
	 */
	public static function copy( $source, $destination ) {
		
		copy( $source, $destination );
		
		return File::exists( $destination );
	}
	
	/**
	 * Find file by source path
	 *
	 * @param $source
	 *
	 * @return null
	 */
	public static function find( $source ) {
		
		try {
			return File::get( $source );
		} catch(\Exception $e) {
			return NULL;
		}
	}
	
	/**
	 * Find file extension by source path
	 *
	 * @param $source
	 *
	 * @return null
	 */
	public static function extension( $source ) {
		
		try {
			return File::extension( $source );
		} catch(\Exception $e) {
			return NULL;
		}
	}
	
	/**
	 * Delete recursively without deleting parent
	 *
	 * @param $source
	 */
	public static function removeDir( $source ) {
		
		return File::deleteDirectory( $source, TRUE );
	}
	
	/**
	 * Handles files upload
	 *
	 * @param UploadedFile|null             $file
	 * @param \Illuminate\Http\UploadedFile $file
	 *
	 * @return LaraFilesHandler
	 */
	public function addFile( UploadedFile $file = NULL ) {
		
		try {
			if($this->path) {
				$this->file = $file;
				if($this->file) {
					$hashName        = md5( microtime() );
					$this->hash_name = $hashName;
					$this->name      = pathinfo( $file->getClientOriginalName(), PATHINFO_FILENAME );
					$this->mime      = $file->getClientOriginalExtension();
					$file->move( $this->path, $hashName . '.' . $file->getClientOriginalExtension() );
				}
			} else {
				throw new \Exception( '\DjurovicIgoor\LaraFiles\Helpers\LaraFilesHandler::127 $path is not available' );
			}
		} catch(\Exception $e) {
			$this->error = $e;
		}
		
		return $this->isSuccess();
	}
	
	/**
	 * Checks whether there is an error
	 */
	private function isSuccess() {
		return $this;
		
		/*	if($this->errors->isNotEmpty()) {
				return $this;
				#return $this->errors;
			}*/
		
	}
	
	/**
	 * Handles removal of file
	 *
	 * @param $source
	 *
	 * @return $this
	 */
	public function removeFile( $source = NULL ) {
		
		try {
			$this->path = $source;
			if($this->path && File::exists( $this->path )) {
				File::delete( $this->path );
			}
		} catch(\Exception $e) {
			$this->error = $e;
		}
		
		return $this->isSuccess();
	}
	
	/**
	 * Sets a new upload path
	 *
	 * @param $path
	 * @return $this
	 */
	public static function uploadPath( $path ) {
		$obj = new static();
		
		$obj->path = $path;
		if($obj->path) {
			File::makeDirectory( $obj->path, 0777, TRUE, TRUE );
		}
		
		return $obj;
	}
	
	/**
	 * has error
	 *
	 * @return bool
	 */
	public function hasError() {
		if($this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * set error
	 *
	 * @return \DjurovicIgoor\LaraFiles\Helpers\LaraFilesHandler
	 * @throws \Exception
	 */
	public static function setError() {
		$obj = new static();
		
		try {
			throw new \Exception( 'File not provided!', 400 );
		} catch(\Exception $e) {
			$obj->error = $e;
		}
		
		return $obj;
	}
}