<?php

declare(strict_types=1);

use dhy\LaravelList\ListCollection;

if (! function_exists('list_of')) {
    /**
     * Create a new ListCollection.
     *
     * - list_of()              -> empty list
     * - list_of([1, 2, 3])     -> from any iterable / Arrayable / Collection
     * - list_of(1, 2, 3)       -> variadic; arguments become the items
     * - list_of('x')           -> [0 => 'x']
     *
     * @return ListCollection<mixed>
     */
    function list_of(mixed ...$items): ListCollection
    {
        return match (count($items)) {
            0 => new ListCollection,
            /** @phpstan-ignore argument.type */
            1 => new ListCollection($items[0]),
            default => new ListCollection($items),
        };
    }
}
