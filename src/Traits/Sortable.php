<?php

namespace DjurovicIgoor\LaraFiles\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    /**
     * @return void
     */
    public function setHighestOrderNumber(): void
    {
        $orderColumnName = $this->getOrderColumnName();

        $this->$orderColumnName = $this->getHighestOrderNumber() + 1;
    }

    /**
     * @return int
     */
    public function getHighestOrderNumber(): int
    {
        if (! $this->larafilesable_type && ! $this->larafilesable_id && ! $this->type) {
            return 0;
        }

        return (int) static::where('larafilesable_type', $this->larafilesable_type)
            ->where('larafilesable_id', $this->larafilesable_id)
            ->where('type', $this->type)
            ->max($this->getOrderColumnName());
    }

    /**
     * @param  Builder  $query
     *
     * @return Builder
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->getOrderColumnName());
    }

    /**
     * @param  array  $ids
     * @param  int  $startOrder
     *
     * @return void
     */
    public static function setNewOrder(array $ids, int $startOrder = 1): void
    {
        foreach ($ids as $id) {
            $model = static::find($id);
            if (! $model) {
                continue;
            }

            $orderColumnName = $model->getOrderColumnName();

            $model->$orderColumnName = $startOrder++;

            $model->save();
        }
    }

    /**
     * @return string
     */
    protected function getOrderColumnName(): string
    {
        return 'order';
    }

    /**
     * @return bool
     */
    public function shouldSortWhenCreating(): bool
    {
        return $this->sortable['sort_when_creating'] ?? true;
    }
}