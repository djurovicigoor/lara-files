<?php

namespace Djurovicigoor\LaraFiles\Tests\TestSupport\TestModels;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use DjurovicIgoor\LaraFiles\Traits\LaraFileTrait;

class TestModel extends Model
{
    use LaraFileTrait;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
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
