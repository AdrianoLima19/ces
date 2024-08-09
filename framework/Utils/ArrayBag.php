<?php

declare(strict_types=1);

namespace Note\Utils;

class ArrayBag
{
    /**
     * Indicates whether property overloading is enabled.
     *
     * @var bool
     */
    protected static bool $isPropertyOverloadEnabled = false;

    /**
     * Initialize the ArrayBag with an optional array of parameters
     *
     * @param  array $parameters
     */
    public function __construct(protected array $parameters = [])
    {
    }

    /**
     * Get all parameters
     *
     * @return array
     */
    public function all(): array
    {
        return $this->parameters;
    }

    /**
     * Get a parameter by key, with an optional default value
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * Set a parameter by key
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Check if a parameter exists by key
     *
     * @param  string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Remove a parameter by key
     *
     * @param  string $key
     *
     * @return void
     */
    public function unset(string $key): void
    {
        unset($this->parameters[$key]);
    }

    /**
     * Replace all parameters with a new set of parameters
     *
     * @param  array $parameters
     *
     * @return void
     */
    public function replace(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Merge a new set of parameters with the existing ones
     *
     * @param  array $parameters
     *
     * @return void
     */
    public function merge(array $parameters): void
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * Get all keys of the parameters
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->parameters);
    }

    /**
     * Get the count of parameters
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->parameters);
    }

    /**
     * Check if the parameters are empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->parameters);
    }

    /**
     * Remove all parameters
     *
     * @return void
     */
    public function flush(): void
    {
        $this->parameters = [];
    }

    /**
     * Enables property overloading.
     *
     * @return void
     */
    public static function enablePropertyOverloading(): void
    {
        static::$isPropertyOverloadEnabled = true;
    }

    /**
     * Sets a property value if property overloading is enabled.
     *
     * @param  string $name
     * @param  mixed  $value
     *
     * @return void
     *
     * @throws \BadMethodCallException If property overloading is disabled.
     */
    public function __set(string $name, mixed $value = null): void
    {
        if (static::$isPropertyOverloadEnabled) {
            $this->set($name, $value);
        } else {
            throw new \BadMethodCallException(
                'Unable to set property because overloading is currently disabled. To enable property overloading, use %s::enablePropertyOverloading().',
                1
            );
        }
    }

    /**
     * Gets a property value if property overloading is enabled.
     *
     * @param  string $name
     *
     * @return mixed
     *
     * @throws \BadMethodCallException If property overloading is disabled.
     */
    public function __get(string $name): mixed
    {
        if (static::$isPropertyOverloadEnabled) {
            return $this->get($name);
        } else {
            throw new \BadMethodCallException(
                'Unable to get property because overloading is currently disabled. To enable property overloading, use %s::enablePropertyOverloading().',
                1
            );
        }
    }
}
