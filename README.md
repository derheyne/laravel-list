<p align="center">
<img width="600" src="art/logo.png" alt="Laravel Excel logo">
</p>

<p align="center">
  A Laravel Collection that guarantees sequential, 0-based integer keys at all times.
</p>
<p align="center">
  <a href="https://packagist.org/packages/derheyne/laravel-list">
    <img src="https://img.shields.io/packagist/v/derheyne/laravel-list.svg?style=flat-square" alt="Latest Version on Packagist">
  </a>

  <a href="https://github.com/derheyne/laravel-list/actions?query=workflow%3Arun-tests+branch%3Amain">
    <img src="https://img.shields.io/github/actions/workflow/status/derheyne/laravel-list/run-tests.yml?branch=main&label=tests&style=flat-square" alt="GitHub Tests Action Status">
  </a>

  <a href="https://packagist.org/packages/derheyne/laravel-list">
    <img src="https://img.shields.io/packagist/dt/derheyne/laravel-list.svg?style=flat-square" alt="Total Downloads">
  </a>
</p>

# Laravel List

`ListCollection` extends `Illuminate\Support\Collection` and enforces the **array list invariant**: keys are always
`[0, 1, 2, ...]` no matter what operations you perform. Operations that would break this invariant (like `filter`,
`sort`, or `forget`) automatically re-index, and operations that inherently produce associative keys (like `flip` or
`keyBy`) throw a `BadMethodCallException`.

## Why?

Laravel's `Collection` is a general-purpose wrapper around PHP arrays. After operations like `filter()` or `sort()`, the
original keys are preserved, which can lead to subtle bugs:

```php
$c = collect(['a', 'b', 'c'])->filter(fn ($v) => $v !== 'b');

$c->all();    // [0 => 'a', 2 => 'c']   -- gap at index 1
$c->toJson(); // {"0":"a","2":"c"}      -- JSON object, not array
$c[1];        // null                   -- unexpected
```

`ListCollection` eliminates this class of bugs entirely:

```php
$list = new ListCollection(['a', 'b', 'c']);
$filtered = $list->filter(fn ($v) => $v !== 'b');

$filtered->all();    // [0 => 'a', 1 => 'c']   -- sequential
$filtered->toJson(); // ["a","c"]              -- JSON array
$filtered[1];        // 'c'                    -- predictable
```

This is useful when you need a collection that always behaves like a proper list -- for JSON APIs, frontend data,
indexed access, or anywhere key gaps would cause problems.

## Installation

```bash
composer require derheyne/laravel-list
```

No service provider, facade, or configuration needed. Just use the class directly.

## Usage

### Creating a ListCollection

```php
use dhy\LaravelList\ListCollection;

// From values -- associative keys are discarded
$list = new ListCollection(['a' => 1, 'b' => 2, 'c' => 3]);
$list->all(); // [0 => 1, 1 => 2, 2 => 3]

// From a regular Collection
$list = new ListCollection(collect([10 => 'x', 20 => 'y']));
$list->all(); // [0 => 'x', 1 => 'y']

// Static factory methods
$list = ListCollection::make([1, 2, 3]);
$list = ListCollection::wrap([1, 2, 3]);
$list = ListCollection::times(5, fn ($i) => $i * 2);
```

### Filtering and Sorting

All filtering and sorting methods return a `ListCollection` with re-indexed keys:

```php
$list = new ListCollection([10, 25, 30, 5, 15]);

$list->filter(fn ($v) => $v > 10)->all();   // [0 => 25, 1 => 30, 2 => 15]
$list->sort()->all();                       // [0 => 5, 1 => 10, 2 => 15, 3 => 25, 4 => 30]
$list->reject(fn ($v) => $v > 20)->all();   // [0 => 10, 1 => 5, 2 => 15]
$list->unique()->all();                     // already re-indexed
$list->where('>', 10)->all();               // re-indexed
```

### Adding and Removing Items

```php
$list = new ListCollection(['a', 'b', 'c', 'd']);

// Remove by index -- remaining items re-index
$list->forget(1);
$list->all(); // [0 => 'a', 1 => 'c', 2 => 'd']

// Remove multiple indices at once
$list->forget([0, 2]);

// Pull removes and returns the value
$value = $list->pull(1); // returns 'b', list re-indexes

// Prepend always adds to the beginning (key parameter is ignored)
$list->prepend('z');

// Push, pop, shift work as expected
$list->push('x');
$popped = $list->pop();
$shifted = $list->shift();
```

### Setting Values by Index

`ListCollection` constrains `offsetSet` to maintain list semantics:

```php
$list = new ListCollection(['a', 'b', 'c']);

$list[] = 'd';       // Appends: [a, b, c, d]
$list[1] = 'B';      // Replaces index 1: [a, B, c, d]
$list[99] = 'z';     // Out of range -- appends: [a, B, c, d, z]
$list['key'] = 'x';  // String key -- appends: [a, B, c, d, z, x]
```

Valid indices for replacement are `0` through `count($list)`. Anything else appends.

### Transformations

```php
$list = new ListCollection([1, 2, 3]);

// map returns a new ListCollection
$doubled = $list->map(fn ($v) => $v * 2); // [0 => 2, 1 => 4, 2 => 6]

// transform mutates in place
$list->transform(fn ($v) => $v * 10); // [0 => 10, 1 => 20, 2 => 30]

// Other operations that return re-indexed ListCollections
$list->slice(1, 2);
$list->splice(1, 1, ['replacement']);
$list->reverse();
$list->flatten();
$list->collapse();
$list->flatMap(fn ($v) => [$v, $v]);
$list->merge([4, 5, 6]);
$list->diff([2]);
$list->intersect([1, 3]);
$list->chunk(2);  // ListCollection of ListCollections
$list->partition(fn ($v) => $v > 1); // two ListCollections
```

### JSON Serialization

Because keys are always sequential, `toJson()` always produces a JSON array, not an object:

```php
$list = new ListCollection([1, 2, 3]);
$list->filter(fn ($v) => $v > 1)->toJson(); // "[2,3]"
```

Compare this to a standard Collection, which would produce `{"1":2,"2":3}` after the same filter.

### Pluck

`pluck()` without a key argument works normally and returns a `ListCollection`:

```php
$list = new ListCollection([
    ['name' => 'Alice', 'age' => 30],
    ['name' => 'Bob', 'age' => 25],
]);

$list->pluck('name')->all(); // [0 => 'Alice', 1 => 'Bob']
```

Calling `pluck()` with a key argument throws a `BadMethodCallException`, because it would produce associative keys.

## Blocked Methods

The following methods are blocked with a `BadMethodCallException` because they inherently produce associative (
non-sequential) keys:

| Method                       | Reason                                    |
|------------------------------|-------------------------------------------|
| `flip()`                     | Uses values as keys                       |
| `combine($values)`           | Uses current items as keys                |
| `groupBy($groupBy)`          | Groups into associative structure         |
| `keyBy($keyBy)`              | Re-keys by a field or callback            |
| `countBy($countBy)`          | Counts into associative structure         |
| `mapWithKeys($callback)`     | Callback defines custom keys              |
| `mapToDictionary($callback)` | Produces dictionary structure             |
| `mapToGroups($callback)`     | Produces grouped structure                |
| `pluck($value, $key)`        | With `$key` argument, uses values as keys |

If you need any of these operations, convert to a regular Collection first:

```php
$collection = collect($list->all());
$grouped = $collection->groupBy('category');
```

## All Supported Collection Methods

Every `Illuminate\Support\Collection` method not listed in the blocked table above works on `ListCollection`. This
includes but is not limited to:

`add`, `all`, `chunk`, `collapse`, `concat`, `contains`, `count`, `diff`, `diffAssoc`, `diffKeys`, `each`, `every`,
`except`, `filter`, `first`, `firstWhere`, `flatMap`, `flatten`, `forget`, `get`, `implode`, `intersect`,
`intersectAssoc`, `intersectByKeys`, `isEmpty`, `isNotEmpty`, `join`, `last`, `map`, `max`, `median`, `merge`, `min`,
`nth`, `only`, `pad`, `partition`, `pipe`, `pluck` (without key), `pop`, `prepend`, `pull`, `push`, `put`, `random`,
`reduce`, `reject`, `replace`, `reverse`, `search`, `shift`, `shuffle`, `skip`, `skipUntil`, `skipWhile`, `slice`,
`sole`, `some`, `sort`, `sortBy`, `sortByDesc`, `sortDesc`, `sortKeys`, `sortKeysDesc`, `splice`, `sum`, `take`,
`takeUntil`, `takeWhile`, `toArray`, `toJson`, `transform`, `unique`, `unless`, `values`, `when`, `where`, `whereIn`,
`whereNotIn`, `zip`

All of these automatically maintain sequential 0-based keys.

## Requirements

- PHP 8.3+
- Laravel 11 or 12

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Daniel Heyne](https://github.com/derheyne)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
