<?php

declare(strict_types=1);

namespace dhy\LaravelList;

use BadMethodCallException;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @template TValue
 *
 * @extends Collection<int, TValue>
 */
class ListCollection extends Collection
{
    /** @param  \Illuminate\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>|null  $items */
    public function __construct($items = [])
    {
        parent::__construct($items);
        $this->items = array_values($this->items);
    }

    /**
     * @param  int|null  $key
     * @param  TValue  $value
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;

            return;
        }

        if (is_int($key) && $key >= 0 && $key <= count($this->items)) {
            $this->items[$key] = $value;

            return;
        }

        $this->items[] = $value;
    }

    /** @param  int  $key */
    public function offsetUnset($key): void
    {
        parent::offsetUnset($key);
        $this->items = array_values($this->items);
    }

    /**
     * @template TPullDefault
     *
     * @param  int  $key
     * @param  TPullDefault|(Closure(): TPullDefault)  $default
     * @return TValue|TPullDefault
     */
    public function pull($key, $default = null)
    {
        $items = $this->items;
        $value = Arr::pull($items, $key, $default);
        $this->items = array_values($items);

        return $value;
    }

    /** @param  TValue  $value */
    public function prepend($value, $key = null): static
    {
        array_unshift($this->items, $value);

        return $this;
    }

    /** @param  array<array-key, int>|int  $keys */
    public function forget($keys): static
    {
        foreach ((array) $keys as $key) {
            unset($this->items[$key]);
        }

        $this->items = array_values($this->items);

        return $this;
    }

    /**
     * @template TMapValue
     *
     * @param  callable(TValue, int): TMapValue  $callback
     *
     * @phpstan-this-out static<TMapValue>
     */
    public function transform(callable $callback): static
    {
        $this->items = array_values($this->map($callback)->all());

        return $this;
    }

    /** @throws BadMethodCallException */
    public function flip(): never
    {
        throw new BadMethodCallException('flip() is not supported on ListCollection because it produces associative keys.');
    }

    /** @throws BadMethodCallException */
    public function combine($values): never
    {
        throw new BadMethodCallException('combine() is not supported on ListCollection because it produces associative keys.');
    }

    /** @throws BadMethodCallException */
    public function groupBy($groupBy, $preserveKeys = false): never
    {
        throw new BadMethodCallException('groupBy() is not supported on ListCollection because it produces associative keys.');
    }

    /** @throws BadMethodCallException */
    public function keyBy($keyBy): never
    {
        throw new BadMethodCallException('keyBy() is not supported on ListCollection because it produces associative keys.');
    }

    /** @throws BadMethodCallException */
    public function countBy($countBy = null): never
    {
        throw new BadMethodCallException('countBy() is not supported on ListCollection because it produces associative keys.');
    }

    /** @throws BadMethodCallException */
    public function mapWithKeys(callable $callback): never
    {
        throw new BadMethodCallException('mapWithKeys() is not supported on ListCollection because it produces associative keys.');
    }

    /** @throws BadMethodCallException */
    public function mapToDictionary(callable $callback): never
    {
        throw new BadMethodCallException('mapToDictionary() is not supported on ListCollection because it produces associative keys.');
    }

    /** @throws BadMethodCallException */
    public function mapToGroups(callable $callback): never
    {
        throw new BadMethodCallException('mapToGroups() is not supported on ListCollection because it produces associative keys.');
    }

    /** @throws BadMethodCallException */
    public function pluck($value, $key = null): static
    {
        if (! is_null($key)) {
            throw new BadMethodCallException('pluck() with a key argument is not supported on ListCollection because it produces associative keys.');
        }

        /** @phpstan-ignore return.type, new.static */
        return new static(parent::pluck($value));
    }
}
