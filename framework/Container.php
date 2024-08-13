<?php

declare(strict_types=1);

namespace Note;

class Container
{
    /**
     * @var array<string, mixed>
     */
    protected array $entries = [];

    /**
     * @var array<string, mixed>
     */
    protected array $binds = [];

    /**
     * @var array<string, mixed>
     */
    protected array $instances = [];

    /**
     * Checks if the identifier is registered in the container.
     *
     * @param  string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->binds[$id]);
    }

    /**
     * Registers an entry in the container.
     *
     * @param  string $id
     * @param  mixed  $entry
     * @param  bool   $singleton
     *
     * @return void
     */
    public function set(string $id, mixed $entry = null, bool $singleton = false): void
    {
        $this->unset($id);

        if (is_null($entry)) {
            $entry = $id;
        }

        $this->binds[$id] = compact('entry', 'singleton');
    }

    /**
     * Retrieves an entry from the container.
     *
     * @param  string $id
     * @param  array  $parameters
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function get(string $id, array $parameters = []): mixed
    {
        if (!$this->has($id)) {
            throw new \InvalidArgumentException("Entry '{$id}' is not bound in the container.");
        }

        $singleton = $this->binds[$id]['singleton'];

        if ($singleton && isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $bind = $this->binds[$id]['entry'];

        if ($bind instanceof \Closure) {
            try {
                $entry = call_user_func($bind, ...$parameters);
            } catch (\Throwable $th) {
                throw new \RuntimeException("Error invoking the closure for '{$id}'.", 0, $th);
            }

            if ($singleton) {
                $this->instances[$id] = $entry;
            }

            return $entry;
        }

        if (!is_string($bind) || !class_exists($bind)) {
            if ($singleton) {
                $this->instances[$id] = $bind;
            }

            return $bind;
        }

        $reflection = new \ReflectionClass($bind);

        if (!$reflection->isInstantiable()) {
            throw new \LogicException("Class '{$bind}' is not instantiable.");
        }

        try {
            $entry = is_null($reflection->getConstructor())
                ? new $bind()
                : $reflection->newInstanceArgs($parameters);
        } catch (\Throwable $th) {
            throw new \RuntimeException("Error instantiating class '{$bind}'.", 0, $th);
        }

        if ($singleton) {
            $this->instances[$id] = $entry;
        }

        return $entry;
    }

    /**
     * Removes an entry from the container.
     *
     * @param  string $id
     *
     * @return void
     */
    public function unset(string $id): void
    {
        unset(
            $this->binds[$id],
            $this->instances[$id]
        );
    }

    /**
     * Accesses an entry directly.
     *
     * @param  string $key
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function __get(string $key): mixed
    {
        if (!isset($this->entries[$key])) {
            throw new \InvalidArgumentException("Entry '{$key}' does not exist in the container.");
        }

        return $this->entries[$key];
    }

    /**
     * Sets an entry directly.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function __set(string $key, mixed $value): void
    {
        $this->entries[$key] = $value;
    }
}
