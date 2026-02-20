<?php

declare(strict_types=1);

use dhy\LaravelList\ListCollection;
use Illuminate\Support\Collection;

describe('constructor', function () {
    test('constructor re-indexes associative array', function () {
        $list = new ListCollection(['a' => 1, 'b' => 2, 'c' => 3]);

        expect($list->all())->toBe([0 => 1, 1 => 2, 2 => 3]);
    });

    test('constructor re-indexes non-sequential integer keys', function () {
        $list = new ListCollection([5 => 'a', 10 => 'b', 15 => 'c']);

        expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });

    test('constructor handles empty input', function () {
        $list = new ListCollection;

        expect($list->all())->toBe([])
            ->and($list)->toHaveCount(0);
    });

    test('constructor handles single item', function () {
        $list = new ListCollection(['only']);

        expect($list->all())->toBe([0 => 'only']);
    });

    test('constructor from Collection re-indexes', function () {
        $collection = new Collection(['x' => 1, 'y' => 2, 'z' => 3]);

        $list = new ListCollection($collection);

        expect($list)->toBeInstanceOf(ListCollection::class)
            ->and($list->all())->toBe([0 => 1, 1 => 2, 2 => 3]);
    });
});

describe('filter', function () {
    test('filter returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3, 4, 5]);

        $filtered = $list->filter(fn (int $v): bool => $v > 2);

        expect($filtered)->toBeInstanceOf(ListCollection::class)
            ->and($filtered->all())->toBe([0 => 3, 1 => 4, 2 => 5]);
    });

    test('filter removes falsy values with sequential keys', function () {
        $list = new ListCollection([0, 1, '', 'hello', null, false, true]);

        $filtered = $list->filter();

        expect(array_keys($filtered->all()))->toBe([0, 1, 2]);
    });
});

describe('sort', function () {
    test('sort returns sequential keys', function () {
        $list = new ListCollection([3, 1, 4, 1, 5]);

        $sorted = $list->sort();

        expect($sorted)->toBeInstanceOf(ListCollection::class)
            ->and($sorted->all())->toBe([0 => 1, 1 => 1, 2 => 3, 3 => 4, 4 => 5]);
    });

    test('sortBy returns sequential keys', function () {
        $list = new ListCollection([
            ['name' => 'Charlie'],
            ['name' => 'Alice'],
            ['name' => 'Bob'],
        ]);

        $sorted = $list->sortBy('name');

        expect($sorted)->toBeInstanceOf(ListCollection::class)
            ->and(array_keys($sorted->all()))->toBe([0, 1, 2])
            ->and($sorted->first()['name'])->toBe('Alice')
            ->and($sorted->last()['name'])->toBe('Charlie');
    });

    test('sortDesc returns sequential keys', function () {
        $list = new ListCollection([1, 3, 2]);

        $sorted = $list->sortDesc();

        expect($sorted->all())->toBe([0 => 3, 1 => 2, 2 => 1]);
    });

    test('sortByDesc returns sequential keys', function () {
        $list = new ListCollection([
            ['name' => 'Alice'],
            ['name' => 'Charlie'],
            ['name' => 'Bob'],
        ]);

        $sorted = $list->sortByDesc('name');

        expect($sorted)->toBeInstanceOf(ListCollection::class)
            ->and(array_keys($sorted->all()))->toBe([0, 1, 2])
            ->and($sorted->first()['name'])->toBe('Charlie')
            ->and($sorted->last()['name'])->toBe('Alice');
    });
});

describe('unique', function () {
    test('unique returns sequential keys', function () {
        $list = new ListCollection([1, 2, 2, 3, 3, 3]);

        $unique = $list->unique();

        expect($unique)->toBeInstanceOf(ListCollection::class)
            ->and($unique->all())->toBe([0 => 1, 1 => 2, 2 => 3]);
    });
});

describe('diff / intersect', function () {
    test('diff returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3, 4, 5]);

        $diff = $list->diff([2, 4]);

        expect($diff->all())->toBe([0 => 1, 1 => 3, 2 => 5]);
    });

    test('intersect returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3, 4, 5]);

        $intersect = $list->intersect([2, 4, 6]);

        expect($intersect->all())->toBe([0 => 2, 1 => 4]);
    });
});

describe('slice / reverse', function () {
    test('slice returns sequential keys', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd', 'e']);

        $sliced = $list->slice(2);

        expect($sliced->all())->toBe([0 => 'c', 1 => 'd', 2 => 'e']);
    });

    test('reverse returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3]);

        $reversed = $list->reverse();

        expect($reversed->all())->toBe([0 => 3, 1 => 2, 2 => 1]);
    });
});

describe('except / only', function () {
    test('except returns sequential keys', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd']);

        $result = $list->except([1, 3]);

        expect($result->all())->toBe([0 => 'a', 1 => 'c']);
    });

    test('only returns sequential keys', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd']);

        $result = $list->only([0, 2]);

        expect($result->all())->toBe([0 => 'a', 1 => 'c']);
    });
});

describe('forget / offsetUnset', function () {
    test('forget re-indexes after removal', function () {
        $list = new ListCollection(['a', 'b', 'c']);

        $list->forget(1);

        expect($list->all())->toBe([0 => 'a', 1 => 'c']);
    });

    test('forget with multiple keys removes correct elements', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd']);

        $list->forget([0, 2]);

        expect($list->all())->toBe([0 => 'b', 1 => 'd']);
    });

    test('forget with single key as array works', function () {
        $list = new ListCollection(['a', 'b', 'c']);

        $list->forget([1]);

        expect($list->all())->toBe([0 => 'a', 1 => 'c']);
    });

    test('forget with out-of-bounds key is a no-op', function () {
        $list = new ListCollection(['a', 'b', 'c']);

        $list->forget(99);

        expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });

    test('offsetUnset re-indexes', function () {
        $list = new ListCollection(['x', 'y', 'z']);

        unset($list[0]);

        expect($list->all())->toBe([0 => 'y', 1 => 'z']);
    });
});

describe('pull', function () {
    test('pull re-indexes after removal', function () {
        $list = new ListCollection(['a', 'b', 'c']);

        $pulled = $list->pull(1);

        expect($pulled)->toBe('b')
            ->and($list->all())->toBe([0 => 'a', 1 => 'c']);
    });

    test('pull returns default for missing key', function () {
        $list = new ListCollection(['a']);

        $pulled = $list->pull(99, 'default');

        expect($pulled)->toBe('default')
            ->and($list->all())->toBe([0 => 'a']);
    });

    test('pull returns Closure result for missing key', function () {
        $list = new ListCollection(['a']);

        $pulled = $list->pull(99, fn () => 'closure-default');

        expect($pulled)->toBe('closure-default')
            ->and($list->all())->toBe([0 => 'a']);
    });
});

describe('offsetSet', function () {
    test('offsetSet with null key appends', function () {
        $list = new ListCollection(['a', 'b']);

        $list[] = 'c';

        expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });

    test('offsetSet with valid integer index replaces', function () {
        $list = new ListCollection(['a', 'b', 'c']);

        $list[1] = 'B';

        expect($list->all())->toBe([0 => 'a', 1 => 'B', 2 => 'c']);
    });

    test('offsetSet with index equal to count appends', function () {
        $list = new ListCollection(['a', 'b']);

        $list[2] = 'c';

        expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });

    test('offsetSet with string key appends', function () {
        $list = new ListCollection(['a', 'b']);

        $list->put('name', 'c');

        expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });

    test('offsetSet with string array key appends value', function () {
        $list = new ListCollection(['a', 'b']);

        $list['foo'] = 'c';

        expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });

    test('offsetSet with out-of-range integer appends', function () {
        $list = new ListCollection(['a', 'b']);

        $list[99] = 'c';

        expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });

    test('offsetSet with negative integer appends', function () {
        $list = new ListCollection(['a', 'b']);

        $list[-1] = 'c';

        expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });
});

describe('prepend', function () {
    test('prepend adds to beginning and ignores key', function () {
        $list = new ListCollection(['b', 'c']);

        $list->prepend('a', 'some-key');

        expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });

    test('prepend without key adds to beginning', function () {
        $list = new ListCollection(['b', 'c']);

        $list->prepend('a');

        expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });
});

describe('transform', function () {
    test('transform re-indexes', function () {
        $list = new ListCollection([1, 2, 3]);

        $list->transform(fn (int $v): int => $v * 10);

        expect($list->all())->toBe([0 => 10, 1 => 20, 2 => 30]);
    });
});

describe('push / pop / shift', function () {
    test('push maintains sequential keys', function () {
        $list = new ListCollection(['a']);

        $list->push('b', 'c');

        expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });

    test('pop maintains sequential keys on remaining items', function () {
        $list = new ListCollection(['a', 'b', 'c']);

        $popped = $list->pop();

        expect($popped)->toBe('c')
            ->and($list->all())->toBe([0 => 'a', 1 => 'b']);
    });

    test('shift maintains sequential keys', function () {
        $list = new ListCollection(['a', 'b', 'c']);

        $shifted = $list->shift();

        expect($shifted)->toBe('a')
            ->and($list->all())->toBe([0 => 'b', 1 => 'c']);
    });
});

describe('map / reject', function () {
    test('map returns ListCollection instance', function () {
        $list = new ListCollection([1, 2, 3]);

        $mapped = $list->map(fn (int $v): int => $v * 2);

        expect($mapped)->toBeInstanceOf(ListCollection::class)
            ->and($mapped->all())->toBe([0 => 2, 1 => 4, 2 => 6]);
    });

    test('reject returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3, 4, 5]);

        $result = $list->reject(fn (int $v): bool => $v % 2 === 0);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 1, 1 => 3, 2 => 5]);
    });
});

describe('where / whereIn', function () {
    test('where returns sequential keys', function () {
        $list = new ListCollection([
            ['active' => true, 'name' => 'A'],
            ['active' => false, 'name' => 'B'],
            ['active' => true, 'name' => 'C'],
        ]);

        $result = $list->where('active', true);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and(array_keys($result->all()))->toBe([0, 1])
            ->and($result->first()['name'])->toBe('A')
            ->and($result->last()['name'])->toBe('C');
    });

    test('whereIn returns sequential keys', function () {
        $list = new ListCollection([
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 3, 'name' => 'Charlie'],
        ]);

        $result = $list->whereIn('id', [1, 3]);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and(array_keys($result->all()))->toBe([0, 1])
            ->and($result->first()['name'])->toBe('Alice')
            ->and($result->last()['name'])->toBe('Charlie');
    });
});

describe('chained operations', function () {
    test('chained operations maintain list invariant', function () {
        $list = new ListCollection([5, 3, 1, 4, 2, 3, 5]);

        $result = $list
            ->filter(fn (int $v): bool => $v > 1)
            ->unique()
            ->sort();

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 2, 1 => 3, 2 => 4, 3 => 5]);
    });

    test('chained forget calls use re-indexed keys', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd']);

        $list->forget(1); // removes 'b' → [0 => 'a', 1 => 'c', 2 => 'd']
        $list->forget(1); // removes 'c' at new index 1

        expect($list->all())->toBe([0 => 'a', 1 => 'd']);
    });
});

describe('all / toArray', function () {
    test('all always returns sequential array', function () {
        $list = new ListCollection(['x' => 1, 'y' => 2, 'z' => 3]);

        expect(array_is_list($list->all()))->toBeTrue();
    });

    test('toArray returns sequential array', function () {
        $list = new ListCollection(['a' => 1, 'b' => 2]);

        expect(array_is_list($list->toArray()))->toBeTrue();
    });
});

describe('splice', function () {
    test('splice returns sequential keys', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd', 'e']);

        $spliced = $list->splice(1, 2);

        expect($spliced)->toBeInstanceOf(ListCollection::class)
            ->and($spliced->all())->toBe([0 => 'b', 1 => 'c'])
            ->and($list->all())->toBe([0 => 'a', 1 => 'd', 2 => 'e']);
    });

    test('splice with replacement inserts items and returns sequential keys', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd']);

        $spliced = $list->splice(1, 1, ['X', 'Y']);

        expect($spliced)->toBeInstanceOf(ListCollection::class)
            ->and($spliced->all())->toBe([0 => 'b'])
            ->and($list->all())->toBe([0 => 'a', 1 => 'X', 2 => 'Y', 3 => 'c', 4 => 'd']);
    });
});

describe('take / skip', function () {
    test('take returns sequential keys', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd', 'e']);

        $taken = $list->take(3);

        expect($taken)->toBeInstanceOf(ListCollection::class)
            ->and($taken->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
    });

    test('take with negative count returns elements from the end', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd', 'e']);

        $result = $list->take(-2);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 'd', 1 => 'e']);
    });

    test('skip returns sequential keys', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd', 'e']);

        $skipped = $list->skip(2);

        expect($skipped)->toBeInstanceOf(ListCollection::class)
            ->and($skipped->all())->toBe([0 => 'c', 1 => 'd', 2 => 'e']);
    });

    test('skipUntil returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3, 4, 5]);

        $result = $list->skipUntil(fn (int $v): bool => $v >= 3);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 3, 1 => 4, 2 => 5]);
    });

    test('skipWhile returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3, 4, 5]);

        $result = $list->skipWhile(fn (int $v): bool => $v < 3);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 3, 1 => 4, 2 => 5]);
    });

    test('takeUntil returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3, 4, 5]);

        $result = $list->takeUntil(fn (int $v): bool => $v > 3);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 1, 1 => 2, 2 => 3]);
    });

    test('takeWhile returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3, 4, 5]);

        $result = $list->takeWhile(fn (int $v): bool => $v <= 3);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 1, 1 => 2, 2 => 3]);
    });
});

describe('flatten / collapse / flatMap', function () {
    test('flatten returns sequential keys', function () {
        $list = new ListCollection([[1, 2], [3, 4], [5]]);

        $result = $list->flatten();

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5]);
    });

    test('collapse returns sequential keys', function () {
        $list = new ListCollection([[1, 2], [3, 4], [5]]);

        $result = $list->collapse();

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5]);
    });

    test('flatMap returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3]);

        $result = $list->flatMap(fn (int $v): array => [$v, $v * 10]);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 1, 1 => 10, 2 => 2, 3 => 20, 4 => 3, 5 => 30]);
    });
});

describe('merge / replace', function () {
    test('merge returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3]);

        $result = $list->merge([4, 5]);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5]);
    });

    test('replace returns sequential keys', function () {
        $list = new ListCollection(['a', 'b', 'c']);

        $result = $list->replace([1 => 'B']);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 'a', 1 => 'B', 2 => 'c']);
    });
});

describe('pad / zip', function () {
    test('pad returns sequential keys', function () {
        $list = new ListCollection([1, 2]);

        $result = $list->pad(5, 0);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result->all())->toBe([0 => 1, 1 => 2, 2 => 0, 3 => 0, 4 => 0]);
    });

    test('zip returns sequential keys', function () {
        $list = new ListCollection([1, 2, 3]);

        $result = $list->zip(['a', 'b', 'c']);

        expect($result)->toBeInstanceOf(ListCollection::class)
            ->and($result)->toHaveCount(3);
    });
});

describe('shuffle / random', function () {
    test('shuffle returns ListCollection with sequential keys', function () {
        $list = new ListCollection([1, 2, 3, 4, 5]);

        $shuffled = $list->shuffle();

        expect($shuffled)->toBeInstanceOf(ListCollection::class)
            ->and(array_keys($shuffled->all()))->toBe([0, 1, 2, 3, 4])
            ->and($shuffled)->toHaveCount(5);
    });

    test('random with count returns ListCollection with sequential keys', function () {
        $list = new ListCollection([1, 2, 3, 4, 5]);

        $random = $list->random(3);

        expect($random)->toBeInstanceOf(ListCollection::class)
            ->and(array_keys($random->all()))->toBe([0, 1, 2])
            ->and($random)->toHaveCount(3);
    });
});

describe('shift / pop with count', function () {
    test('shift with count returns ListCollection and mutates in place', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd']);

        $shifted = $list->shift(2);

        expect($shifted)->toBeInstanceOf(ListCollection::class)
            ->and($shifted->all())->toBe([0 => 'a', 1 => 'b'])
            ->and($list->all())->toBe([0 => 'c', 1 => 'd']);
    });

    test('pop with count returns ListCollection and mutates in place', function () {
        $list = new ListCollection(['a', 'b', 'c', 'd']);

        $popped = $list->pop(2);

        expect($popped)->toBeInstanceOf(ListCollection::class)
            ->and($popped->all())->toBe([0 => 'd', 1 => 'c'])
            ->and($list->all())->toBe([0 => 'a', 1 => 'b']);
    });
});

describe('serialization', function () {
    test('toJson produces JSON array not object', function () {
        $list = new ListCollection(['a', 'b', 'c']);

        $json = $list->toJson();

        expect($json)->toBe('["a","b","c"]');
    });

    test('toJson produces JSON array after filter', function () {
        $list = new ListCollection([1, 2, 3, 4, 5]);

        $json = $list->filter(fn (int $v): bool => $v > 2)->toJson();

        expect($json)->toBe('[3,4,5]');
    });
});

describe('static factories', function () {
    test('make returns ListCollection', function () {
        $list = ListCollection::make(['z' => 3, 'a' => 1]);

        expect($list)->toBeInstanceOf(ListCollection::class)
            ->and($list->all())->toBe([0 => 3, 1 => 1]);
    });

    test('times returns ListCollection', function () {
        $list = ListCollection::times(3, fn (int $i): int => $i * 10);

        expect($list)->toBeInstanceOf(ListCollection::class)
            ->and($list->all())->toBe([0 => 10, 1 => 20, 2 => 30]);
    });

    test('wrap returns ListCollection', function () {
        $list = ListCollection::wrap('single');

        expect($list)->toBeInstanceOf(ListCollection::class)
            ->and($list->all())->toBe([0 => 'single']);
    });
});

describe('blocked methods', function () {
    test('flip throws BadMethodCallException', function () {
        $list = new ListCollection([1, 2, 3]);

        $list->flip();
    })->throws(BadMethodCallException::class);

    test('combine throws BadMethodCallException', function () {
        $list = new ListCollection(['a', 'b']);

        $list->combine([1, 2]);
    })->throws(BadMethodCallException::class);

    test('groupBy throws BadMethodCallException', function () {
        $list = new ListCollection([
            ['type' => 'a', 'value' => 1],
            ['type' => 'b', 'value' => 2],
        ]);

        $list->groupBy('type');
    })->throws(BadMethodCallException::class);

    test('keyBy throws BadMethodCallException', function () {
        $list = new ListCollection([
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ]);

        $list->keyBy('id');
    })->throws(BadMethodCallException::class);

    test('countBy throws BadMethodCallException', function () {
        $list = new ListCollection(['a', 'b', 'a', 'c', 'b', 'a']);

        $list->countBy();
    })->throws(BadMethodCallException::class);

    test('mapWithKeys throws BadMethodCallException', function () {
        $list = new ListCollection([
            ['key' => 'a', 'value' => 1],
        ]);

        $list->mapWithKeys(fn (array $item): array => [$item['key'] => $item['value']]);
    })->throws(BadMethodCallException::class);

    test('mapToDictionary throws BadMethodCallException', function () {
        $list = new ListCollection([
            ['type' => 'a', 'value' => 1],
        ]);

        $list->mapToDictionary(fn (array $item): array => [$item['type'] => $item['value']]);
    })->throws(BadMethodCallException::class);

    test('mapToGroups throws BadMethodCallException', function () {
        $list = new ListCollection([
            ['type' => 'a', 'value' => 1],
        ]);

        $list->mapToGroups(fn (array $item): array => [$item['type'] => $item['value']]);
    })->throws(BadMethodCallException::class);

    test('pluck with key throws BadMethodCallException', function () {
        $list = new ListCollection([
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ]);

        $list->pluck('name', 'id');
    })->throws(BadMethodCallException::class);
});

test('partition returns two ListCollections with sequential keys', function () {
    $list = new ListCollection([1, 2, 3, 4, 5]);

    [$even, $odd] = $list->partition(fn (int $v): bool => $v % 2 === 0);

    expect($even)->toBeInstanceOf(ListCollection::class)
        ->and($even->all())->toBe([0 => 2, 1 => 4])
        ->and($odd)->toBeInstanceOf(ListCollection::class)
        ->and($odd->all())->toBe([0 => 1, 1 => 3, 2 => 5]);
});

test('concat returns sequential keys', function () {
    $list = new ListCollection([1, 2, 3]);

    $result = $list->concat([4, 5]);

    expect($result)->toBeInstanceOf(ListCollection::class)
        ->and($result->all())->toBe([0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5]);
});

test('values returns equivalent ListCollection', function () {
    $list = new ListCollection([1, 2, 3]);

    $values = $list->values();

    expect($values)->toBeInstanceOf(ListCollection::class)
        ->and($values->all())->toBe($list->all());
});

test('chunk returns ListCollection of ListCollections', function () {
    $list = new ListCollection([1, 2, 3, 4, 5]);

    $chunks = $list->chunk(2);

    expect($chunks)->toBeInstanceOf(ListCollection::class)
        ->and($chunks)->toHaveCount(3)
        ->and($chunks[0])->toBeInstanceOf(ListCollection::class)
        ->and($chunks[0]->all())->toBe([0 => 1, 1 => 2])
        ->and($chunks[1]->all())->toBe([0 => 3, 1 => 4])
        ->and($chunks[2]->all())->toBe([0 => 5]);
});

test('nth returns sequential keys', function () {
    $list = new ListCollection(['a', 'b', 'c', 'd', 'e', 'f']);

    $result = $list->nth(2);

    expect($result)->toBeInstanceOf(ListCollection::class)
        ->and($result->all())->toBe([0 => 'a', 1 => 'c', 2 => 'e']);
});

test('add appends and maintains sequential keys', function () {
    $list = new ListCollection(['a', 'b']);

    $list->add('c');

    expect($list->all())->toBe([0 => 'a', 1 => 'b', 2 => 'c']);
});

test('pluck without key returns sequential keys', function () {
    $list = new ListCollection([
        ['id' => 10, 'name' => 'Alice'],
        ['id' => 20, 'name' => 'Bob'],
    ]);

    $result = $list->pluck('name');

    expect($result)->toBeInstanceOf(ListCollection::class)
        ->and($result->all())->toBe([0 => 'Alice', 1 => 'Bob']);
});
