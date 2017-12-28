<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 28.12.17.
 * Time: 16.07
 */

namespace DjurovicIgoor\LaraFiles\Helpers;


class LaraFileError {
	
	public $errorMessage;
	public $errorCode;
	public $errorFile;
	public $errorLine;
	public $errorTrace;
	public $errorPath;
	public $errorHashName;
	public $errorName;
	public $errorMime;
	public $errorType;
	public $errorDescription;
	public $errorStorage;
	
	public function __construct() {
	
	}
	
	public function setError(LaraFileHandler $LaraFilesHandler){
		$this->errorMessage = $LaraFilesHandler->error->getMessage();
		$this->errorCode = $LaraFilesHandler->error->getCode();
		$this->errorFile = $LaraFilesHandler->error->getFile();
		$this->errorLine = $LaraFilesHandler->error->getLine();
		$this->errorTrace = $LaraFilesHandler->error->getTrace();
		$this->errorPath = $LaraFilesHandler->path;
		$this->errorHashName = $LaraFilesHandler->hash_name;
		$this->errorName = $LaraFilesHandler->name;
		$this->errorMime = $LaraFilesHandler->mime;
		$this->errorType = $LaraFilesHandler->type;
		$this->errorDescription = $LaraFilesHandler->description;
		$this->errorStorage = $LaraFilesHandler->storage;
	}
}