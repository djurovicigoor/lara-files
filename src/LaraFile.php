<?php

namespace DjurovicIgoor\LaraFiles;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Symfony\Component\HttpFoundation\StreamedResponse;
use DjurovicIgoor\LaraFiles\Exceptions\UnableToUploadFileException;
use DjurovicIgoor\LaraFiles\Exceptions\UnsupportedDiskAdapterException;

/**
 * @property string  $id
 * @property string  $disk
 * @property string  $path
 * @property string  $hash_name
 * @property string  $extension
 * @property string  $name
 * @property string  $type
 * @property string  $visibility
 * @property string  $description
 * @property integer $author_id
 * @property string  $larafilesable_type
 * @property integer $larafilesable_id
 * @property integer $size
 * @property string  $mime_type
 * @property integer $last_modified
 */
class LaraFile extends Model
{
	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = FALSE;
	
	/**
	 * The "type" of the primary key ID.
	 *
	 * @var string
	 */
	protected $keyType = 'string';
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'lara_files';
	
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = ['id'];
	
	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = ['url', 'fullPath'];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'disk',
		'path',
		'hash_name',
		'extension',
		'name',
		'type',
		'visibility',
		'description',
		'author_id',
		'larafilesable_type',
		'larafilesable_id',
	];
	
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'path',
		'hash_name',
		'larafilesable_type',
		'larafilesable_id',
	];
	
	/**
	 * The "booting" method of the model.
	 *
	 * This method is called when the model is booting.
	 * It adds two global scopes to the model: UserScope and NewestScope.
	 */
	protected static function boot(): void
	{
		// Call the parent's boot method
		parent::boot();
		
		static::creating(function ($model) {
			$model->id = Str::uuid()->toString();
		});
	}
	
	/**
	 * @return MorphTo
	 */
	public function larafilesable(): MorphTo
	{
		return $this->morphTo();
	}
	
	/**
	 * @return bool|null
	 */
	public function delete(): ?bool
	{
		if (Storage::disk($this->attributes['disk'])->exists($this->fullPath)) {
			Storage::disk($this->attributes['disk'])->delete($this->fullPath);
		}
		
		return parent::delete();
	}
	
	/**
	 * Return relative path to the file
	 *
	 * @return string
	 */
	public function getFullPathAttribute()
	{
		return "{$this->attributes['path']}/{$this->attributes['hash_name']}.{$this->attributes['extension']}";
	}
	
	/**
	 * Return full url to the file
	 *
	 * @return string
	 */
	public function getUrlAttribute()
	{
		if ($this->attributes['disk'] === 'local' || Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
			return NULL;
		}
		
		return Storage::disk($this->attributes['disk'])->url($this->fullPath);
	}
	
	/**
	 * @return int|null
	 */
	public function getSizeAttribute(): ?int
	{
		return $this->getSize();
	}
	
	/**
	 * @return string|null
	 */
	public function getMimeTypeAttribute(): ?string
	{
		return $this->getMimeType();
	}
	
	/**
	 * @return int|null
	 */
	public function getLastModifiedAttribute(): ?int
	{
		return $this->getLastModified();
	}
	
	/**
	 * @return string|null
	 */
	public function getContents(): ?string
	{
		if (Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
			return NULL;
		}
		
		return Storage::disk($this->attributes['disk'])->get($this->fullPath);
	}
	
	/**
	 * @param       $name
	 * @param array $headers
	 *
	 * @return StreamedResponse|null
	 */
	public function download($name = NULL, array $headers = []): ?StreamedResponse
	{
		if (Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
			return NULL;
		}
		
		return Storage::disk($this->attributes['disk'])->download($this->fullPath, $name, $headers);
	}
	
	/**
	 * @return false|string|null
	 */
	public function getMimeType(): string|null|false
	{
		if (Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
			return NULL;
		}
		
		return Storage::disk($this->attributes['disk'])->mimeType($this->fullPath);
	}
	
	/**
	 * @return int|null
	 */
	public function getSize(): ?int
	{
		if (Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
			return NULL;
		}
		
		return Storage::disk($this->attributes['disk'])->size($this->fullPath);
	}
	
	/**
	 * @return int|null
	 */
	public function getLastModified(): ?int
	{
		if (Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
			return NULL;
		}
		
		return Storage::disk($this->attributes['disk'])->lastModified($this->fullPath);
	}
	
	/**
	 * @return string|null
	 */
	public function getDataPath(): ?string
	{
		if (!\in_array($this->attributes['disk'], ['local', 'public']) || Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
			return NULL;
		}
		
		return Storage::disk($this->attributes['disk'])->path($this->fullPath);
	}
	
	/**
	 * @throws UnsupportedDiskAdapterException|UnableToUploadFileException|Throwable
	 */
	public function changeDisk(string $disk): ?LaraFile
	{
		$oldDisk = $this->attributes['disk'];
		
		throw_if(!array_key_exists($disk, config('filesystems.disks')), new UnsupportedDiskAdapterException($disk));
		
		$successfullyMoved = Storage::disk($disk)->put($this->full_path, Storage::disk($this->attributes['disk'])->get($this->full_path), [
			'visibility' => $this->visibility,
		]);
		
		throw_if(!$successfullyMoved, new UnableToUploadFileException());
		
		$this->update(['disk' => $disk]);
		
		Storage::disk($oldDisk)->delete($this->full_path);
		
		return $this->fresh();
	}
}