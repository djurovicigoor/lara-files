<?php

namespace Djurovicigoor\LaraFiles\Tests\TestSupport\TestModels;

use DjurovicIgoor\LaraFiles\Traits\LaraFileTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TestModel extends Model
{
    use LaraFileTrait;

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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Str::uuid()->toString();
        });
    }
}
