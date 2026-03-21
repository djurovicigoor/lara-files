<?php

namespace DjurovicIgoor\LaraFiles\Traits;

use Illuminate\Support\Arr;
use DjurovicIgoor\LaraFiles\Models\LaraFile;

trait CustomProperties
{
    /**
     * @param  string  $propertyName
     * @return bool
     */
    public function hasCustomProperty(string $propertyName): bool
    {
        return Arr::has($this->custom_properties, $propertyName);
    }

    /**
     * @param  string  $propertyName
     * @param  mixed|null  $default
     * @return mixed
     */
    public function getCustomProperty(string $propertyName, mixed $default = null): mixed
    {
        return Arr::get($this->custom_properties, $propertyName, $default);
    }

    /**
     * @param  string  $name
     * @param  mixed  $value
     * @return CustomProperties|LaraFile
     */
    public function setCustomProperty(string $name, mixed $value): self
    {
        $customProperties = $this->custom_properties;

        Arr::set($customProperties, $name, $value);

        $this->custom_properties = $customProperties;

        return $this;
    }

    /**
     * @param  string  $name
     * @return CustomProperties|LaraFile
     */
    public function forgetCustomProperty(string $name): self
    {
        $customProperties = $this->custom_properties;

        Arr::forget($customProperties, $name);

        $this->custom_properties = $customProperties;

        return $this;
    }
}
