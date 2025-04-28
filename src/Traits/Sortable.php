<?php

namespace DjurovicIgoor\LaraFiles\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    public function setHighestOrderNumber(): void
    {
        $orderColumnName = $this->getOrderColumnName();
        
        $this->$orderColumnName = $this->getHighestOrderNumber() + 1;
    }
    
    public function getHighestOrderNumber(): int
    {
        if ( ! $this->larafilesable_type && ! $this->larafilesable_id) {
            return 0;
        }
        return (int) static::where('larafilesable_type', $this->larafilesable_type)->where('larafilesable_id', $this->larafilesable_id)->max($this->getOrderColumnName());
    }
    
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->getOrderColumnName());
    }
    
    public static function setNewOrder(array $ids, int $startOrder = 1): void
    {
        foreach ($ids as $id) {
            $model = static::find($id);
            if ( ! $model) {
                continue;
            }
            
            $orderColumnName = $model->getOrderColumnName();
            
            $model->$orderColumnName = $startOrder++;
            
            $model->save();
        }
    }
    
    protected function getOrderColumnName(): string
    {
        return 'order';
    }
    
    public function shouldSortWhenCreating(): bool
    {
        return $this->sortable['sort_when_creating'] ?? true;
    }
}