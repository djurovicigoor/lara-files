<?php

namespace DjurovicIgoor\LaraFiles;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
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
 */
class LaraFile extends Model {
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'lara_files';
    
    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = ['id'];
    
    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    protected $appends = ['url', 'fullPath'];
    
    /**
     * The attributes that are mass assignable.
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
     * @var array
     */
    protected $hidden = [
        'path',
        'hash_name',
        'larafilesable_type',
        'larafilesable_id',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function larafilesable() {
        
        return $this->morphTo();
    }
    
    /**
     * Delete the model from the database.
     * @return bool|null
     * @throws \Exception
     */
    public function delete() {
        
        Storage::disk($this->attributes['disk'])->delete($this->fullPath);
        
        return parent::delete();
    }
    
    /**
     * Return full url to the file
     * @return string
     */
    public function getFullPathAttribute() {
        
        return "{$this->attributes['path']}/{$this->attributes['hash_name']}.{$this->attributes['extension']}";
    }
    
    /**
     * Return full url to the file
     * @return string
     */
    public function getUrlAttribute() {
        
        if ($this->attributes['visibility'] === 'public') {
            switch ($this->attributes['disk']) {
                case 'public':
                    $rootUrl = config('filesystems.disks.public.url');
                break;
                case 'DOSpaces':
                    $rootUrl = config('filesystems.disks.DOSpaces.url');
                break;
                default :
                    return NULL;
                break;
            }
            
            return $rootUrl . $this->fullPath;
        } else {
            return NULL;
        }
    }
    
}
