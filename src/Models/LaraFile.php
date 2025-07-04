<?php

namespace DjurovicIgoor\LaraFiles\Models;

use Exception;
use Throwable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use DjurovicIgoor\LaraFiles\Traits\Sortable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use DjurovicIgoor\LaraFiles\Traits\CustomProperties;
use Symfony\Component\HttpFoundation\StreamedResponse;
use DjurovicIgoor\LaraFiles\Exceptions\UnableToUploadFileException;
use DjurovicIgoor\LaraFiles\Exceptions\VisibilityIsNotValidException;
use DjurovicIgoor\LaraFiles\Exceptions\UnsupportedDiskAdapterException;

/**
 * @property string $id
 * @property string $disk
 * @property string $path
 * @property string $hash_name
 * @property string $extension
 * @property string $name
 * @property string $type
 * @property string $visibility
 * @property int $order
 * @property string $larafilesable_type
 * @property int $larafilesable_id
 * @property array $custom_properties
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read  int $size
 * @property-read string $mime_type
 * @property-read int $last_modified
 * @property-read string $url
 * @property-read string $full_path
 */
class LaraFile extends Model
{
    use CustomProperties;
    use Sortable;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'custom_properties' => 'array',
    ];

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
        'larafilesable_type',
        'larafilesable_id',
        'order',
        'custom_properties',
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
     * @return MorphTo
     */
    public function larafilesable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return Builder
     */
    public function newQuery(): Builder
    {
        return parent::newQuery()->ordered();
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
        // Storage::disk($this->attributes['disk'])->missing($this->fullPath)

        // Optimization: fast base URL
        $baseUrl = match ($this->attributes['disk']) {
            'public' => config('filesystems.disks.public.url'),
            's3' => config('filesystems.disks.s3.url'),
            'DOSpaces' => config('filesystems.disks.DOSpaces.url'),
            default => null,
        };
        $baseUrl = \rtrim($baseUrl, '/');

        return $baseUrl.'/'.$this->fullPath;
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
            return null;
        }

        return Storage::disk($this->attributes['disk'])->get($this->fullPath);
    }

    /**
     * @param $name
     * @param  array  $headers
     *
     * @return StreamedResponse|null
     */
    public function download($name = null, array $headers = []): ?StreamedResponse
    {
        if (Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
            return null;
        }

        return Storage::disk($this->attributes['disk'])->download($this->fullPath, $name, $headers);
    }

    /**
     * @return string|false|null
     */
    public function getMimeType(): string|null|false
    {
        if (Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
            return null;
        }

        return Storage::disk($this->attributes['disk'])->mimeType($this->fullPath);
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        if (Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
            return null;
        }

        return Storage::disk($this->attributes['disk'])->size($this->fullPath);
    }

    /**
     * @return int|null
     */
    public function getLastModified(): ?int
    {
        if (Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
            return null;
        }

        return Storage::disk($this->attributes['disk'])->lastModified($this->fullPath);
    }

    /**
     * @return string|null
     */
    public function getDataPath(): ?string
    {
        if (! \in_array($this->attributes['disk'], ['local', 'public']) || Storage::disk($this->attributes['disk'])->missing($this->fullPath)) {
            return null;
        }

        return Storage::disk($this->attributes['disk'])->path($this->fullPath);
    }

    /**
     * @throws UnsupportedDiskAdapterException|UnableToUploadFileException|Throwable
     */
    public function changeDisk(string $disk): ?LaraFile
    {
        $oldDisk = $this->attributes['disk'];

        throw_if(! array_key_exists($disk, config('filesystems.disks')), new UnsupportedDiskAdapterException($disk));

        $successfullyMoved = Storage::disk($disk)->put($this->full_path, Storage::disk($this->attributes['disk'])->get($this->full_path), [
            'visibility' => $this->visibility,
        ]);

        throw_if(! $successfullyMoved, new UnableToUploadFileException());

        $this->update(['disk' => $disk]);

        Storage::disk($oldDisk)->delete($this->full_path);

        return $this->fresh();
    }

    /**
     * @throws VisibilityIsNotValidException|Throwable
     */
    public function changeVisibility($visibility): ?LaraFile
    {
        \throw_if(! in_array($visibility, ['public', 'private']), new VisibilityIsNotValidException($visibility));

        $successfullyUpdated = Storage::disk($this->attributes['disk'])->setVisibility($this->full_path, $visibility);

        throw_if(! $successfullyUpdated, new Exception('Unable to change visibility.', 500));

        $this->update(['visibility' => $visibility]);

        return $this->fresh();
    }

    /**
     * @param $expirationTime
     * @param  array  $S3RequestParameters
     *
     * @return string|null
     */
    public function getTemporaryUrl($expirationTime = null, array $S3RequestParameters = []): ?string
    {
        if (! $expirationTime) {
            $expirationTime = now()->addMinutes(5);
        }

        return Storage::disk($this->attributes['disk'])->temporaryUrl($this->full_path, $expirationTime, $S3RequestParameters);
    }
}
