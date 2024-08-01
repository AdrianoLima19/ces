<?php

declare(strict_types=1);

namespace Note;

use Exception;

class Container
{
    protected $bindings;
    protected $resolved;
    protected $instances;

    /**
     * Check if the container has a binding for the given ID
     *
     * @param  string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    /**
     * Retrieve an entry from the container by its ID
     *
     * @param  string $id
     * @param  array  $parameters
     *
     * @return mixed
     *
     * @throws Exception If the ID is not found or if the entry is not valid
     */
    public function get(string $id, array $parameters = []): mixed
    {
        if (isset($this->resolved[$id]) && $this->resolved[$id]) {
            return $this->instances[$id];
        }

        if (!$this->has($id)) {
            throw new Exception("No binding found for ID: {$id}", 1);
        }

        $bind = $this->bindings[$id]['entry'];
        $shared = $this->bindings[$id]['shared'];

        if ($bind instanceof \Closure) {
            $entry = call_user_func($bind, ...$parameters);

            if ($shared) {
                $this->resolved[$id] = true;
                $this->instances[$id] = $entry;
            }

            return $entry;
        }

        if (!is_string($bind) || (is_string($bind) && !class_exists($bind))) {
            if ($shared) {
                $this->resolved[$id] = true;
                $this->instances[$id] = $bind;
            }

            return $bind;
        }

        // todo: Implementation pending for ReflectionClass

        throw new Exception("Invalid binding for ID: {$id}", 1);
    }

    /**
     * Set a binding in the container
     *
     * @param  string $id
     * @param  mixed  $entry
     * @param  bool   $shared
     *
     * @return void
     */
    public function set(string $id, mixed $entry = null, bool $shared = false): void
    {
        $this->unset($id);

        if (is_null($entry)) $entry = $id;

        $this->bindings[$id] = compact('entry', 'shared');
    }

    /**
     * Remove a binding from the container
     *
     * @param  string $id
     *
     * @return void
     */
    public function unset(string $id): void
    {
        unset(
            $this->bindings[$id],
            $this->resolved[$id],
            $this->instances[$id]
        );
    }
}
