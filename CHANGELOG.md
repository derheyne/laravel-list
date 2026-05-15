# Changelog

All notable changes to `laravel-list` will be documented in this file.

## 1.0.0 - 2026-05-15

### Release Notes – v1.0.0

I'm excited to announce the initial release of `laravel-list` — a drop-in Laravel Collection that guarantees sequential, 0-based integer keys at all times. No more `{"1":"a","3":"b"}` surprises in your JSON after a `filter()`.

#### What's inside

- **`ListCollection`** — extends `Illuminate\Support\Collection` and enforces the list invariant on every operation. `filter()`, `sort()`, `unique()`, `forget()`, `pull()`, `splice()` and friends automatically re-index, so `toJson()` always returns a JSON array and `$list[$i]` never hits a gap.
- **`list_of()` helper** — the `ListCollection` analogue of `collect()`, accepting a single iterable or any number of variadic items:
  ```php
  list_of();              // empty list
  list_of([1, 2, 3]);     // from iterable
  list_of(1, 2, 3);       // variadic
  list_of('x');           // [0 => 'x']
  
  ```
- **Safe `offsetSet`** — `$list[$i] = $value` replaces in range `0..count($list)` and appends otherwise. String keys, negative indices, and out-of-range writes can never punch a hole or create an associative key.
- **Guarded misuse** — methods that inherently produce associative keys (`flip`, `combine`, `groupBy`, `keyBy`, `countBy`, `mapWithKeys`, `mapToDictionary`, `mapToGroups`, and `pluck` with a `$key` argument) throw `BadMethodCallException` instead of silently changing the shape of your data.
- **Typed generics** — `@extends Collection<int, TValue>` so PHPStan and your IDE track value types through chained operations.

#### Installation

```bash
composer require derheyne/laravel-list

```
No service provider, facade, or config to publish — just import `dhy\LaravelList\ListCollection` (or call `list_of()`) and you're set.

#### Requirements

- PHP 8.3+
- Laravel 11 or 12

This is the very first release — feedback, issues, and contributions are welcome!
