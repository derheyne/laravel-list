<?php

declare(strict_types=1);

use dhy\LaravelList\ListCollection;
use Illuminate\Support\Collection;

describe('list_of()', function () {
    test('returns an empty ListCollection when called with no arguments', function () {
        $list = list_of();

        expect($list)->toBeInstanceOf(ListCollection::class)
            ->and($list->all())->toBe([]);
    });

    test('accepts a single array and re-indexes it', function () {
        $list = list_of(['a' => 1, 'b' => 2, 'c' => 3]);

        expect($list)->toBeInstanceOf(ListCollection::class)
            ->and($list->all())->toBe([0 => 1, 1 => 2, 2 => 3]);
    });

    test('accepts a single Collection and re-indexes it', function () {
        $list = list_of(new Collection([10 => 'x', 20 => 'y']));

        expect($list->all())->toBe([0 => 'x', 1 => 'y']);
    });

    test('wraps a single scalar into a one-element list', function () {
        expect(list_of('hello')->all())->toBe([0 => 'hello'])
            ->and(list_of(42)->all())->toBe([0 => 42]);
    });

    test('treats multiple arguments as the list items', function () {
        $list = list_of(1, 2, 3);

        expect($list)->toBeInstanceOf(ListCollection::class)
            ->and($list->all())->toBe([0 => 1, 1 => 2, 2 => 3]);
    });

    test('keeps nested arrays as items in variadic form', function () {
        $list = list_of([1, 2], [3, 4]);

        expect($list->all())->toBe([0 => [1, 2], 1 => [3, 4]]);
    });

    test('result behaves as a ListCollection through chained operations', function () {
        $result = list_of(3, 1, 4, 1, 5, 9, 2)
            ->filter(fn ($v) => $v > 1)
            ->unique()
            ->sort();

        expect($result->all())->toBe([0 => 2, 1 => 3, 2 => 4, 3 => 5, 4 => 9]);
    });
});
