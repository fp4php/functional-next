<?php

declare(strict_types=1);

namespace Fp4\PHP\Test\Module;

use Fp4\PHP\Module\ArrayList as L;
use Fp4\PHP\Module\Option as O;
use Fp4\PHP\Type\Option;
use Generator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Fp4\PHP\Module\Functions\pipe;
use function PHPUnit\Framework\assertEquals;

/**
 * @api
 */
final class ArrayListTest extends TestCase
{
    #[Test]
    public static function fromIterable(): void
    {
        $generator = function (): Generator {
            yield 1;
            yield 2;
            yield 3;
        };

        assertEquals(
            [1, 2, 3],
            L\fromIterable($generator()),
        );
    }

    #[Test]
    public static function map(): void
    {
        $addOne = fn (int $i): int => $i + 1;

        assertEquals([], pipe(
            [],
            L\map($addOne),
        ));
        assertEquals([2, 3, 4], pipe(
            [1, 2, 3],
            L\map($addOne),
        ));
    }

    #[Test]
    public static function mapKV(): void
    {
        $joinWithIndex = fn (int $index, string $offset): string => "{$index}-{$offset}";

        assertEquals([], pipe(
            [],
            L\mapKV($joinWithIndex),
        ));
        assertEquals(['0-fst', '1-snd', '2-thr'], pipe(
            ['fst', 'snd', 'thr'],
            L\mapKV($joinWithIndex),
        ));
    }

    #[Test]
    public static function flatMap(): void
    {
        $getSiblings = fn (int $i): array => [$i - 1, $i, $i + 1];

        assertEquals([], pipe(
            [],
            L\flatMap($getSiblings),
        ));
        assertEquals([0, 1, 2, 1, 2, 3, 2, 3, 4], pipe(
            [1, 2, 3],
            L\flatMap($getSiblings),
        ));
    }

    #[Test]
    public static function flatMapKV(): void
    {
        $getSiblingsAtPosition = fn (int $position, int $i): array => [
            sprintf('%s:[%s]', $position, $i - 1),
            sprintf('%s:[%s]', $position, $i),
            sprintf('%s:[%s]', $position, $i + 1),
        ];

        assertEquals([], pipe(
            [],
            L\flatMapKV($getSiblingsAtPosition),
        ));
        assertEquals(['0:[0]', '0:[1]', '0:[2]', '1:[1]', '1:[2]', '1:[3]', '2:[2]', '2:[3]', '2:[4]'], pipe(
            [1, 2, 3],
            L\flatMapKV($getSiblingsAtPosition),
        ));
    }

    #[Test]
    public static function prepend(): void
    {
        /** @var list<int> */
        $emptyList = [];

        /** @var list<int> */
        $nonEmptyList = [43, 44];

        assertEquals([42], pipe(
            $emptyList,
            L\prepend(42),
        ));
        assertEquals([42, 43, 44], pipe(
            $nonEmptyList,
            L\prepend(42),
        ));
    }

    #[Test]
    public static function append(): void
    {
        /** @var list<int> */
        $emptyList = [];

        /** @var list<int> */
        $nonEmptyList = [40, 41];

        assertEquals([42], pipe(
            $emptyList,
            L\append(42),
        ));

        assertEquals([40, 41, 42], pipe(
            $nonEmptyList,
            L\append(42),
        ));
    }

    #[Test]
    public static function last(): void
    {
        /** @var list<int> */
        $empty = [];

        /** @var list<int> */
        $withOneElement = [1];

        /** @var list<int> */
        $withMultipleElements = [1, 2, 3];

        assertEquals(O\none, pipe(
            $empty,
            L\last(...),
        ));
        assertEquals(O\some(1), pipe(
            $withOneElement,
            L\last(...),
        ));
        assertEquals(O\some(3), pipe(
            $withMultipleElements,
            L\last(...),
        ));
    }

    #[Test]
    public static function first(): void
    {
        /** @var list<int> */
        $empty = [];

        /** @var list<int> */
        $withOneElement = [1];

        /** @var list<int> */
        $withMultipleElements = [1, 2, 3];

        assertEquals(O\none, pipe(
            $empty,
            L\first(...),
        ));
        assertEquals(O\some(1), pipe(
            $withOneElement,
            L\first(...),
        ));
        assertEquals(O\some(1), pipe(
            $withMultipleElements,
            L\first(...),
        ));
    }

    #[Test]
    public static function traverseOption(): void
    {
        /** @var list<int> */
        $allNumbers = [1, 2, 3];

        /** @var list<int> */
        $evenNumbers = [2, 4, 6];

        $proveEven = fn (int $i): Option => 0 === $i % 2
            ? O\some($i)
            : O\none;

        assertEquals(O\none, pipe(
            $allNumbers,
            L\traverseOption($proveEven),
        ));
        assertEquals(O\some($evenNumbers), pipe(
            $evenNumbers,
            L\traverseOption($proveEven),
        ));
    }

    #[Test]
    public static function sequenceOption(): void
    {
        /** @var list<Option<int>> */
        $withNone = [O\some(1), O\some(2), O\none];

        /** @var list<Option<int>> */
        $allSome = [O\some(1), O\some(2), O\some(3)];

        assertEquals(O\none, pipe(
            $withNone,
            L\sequenceOption(...),
        ));
        assertEquals(O\some([1, 2, 3]), pipe(
            $allSome,
            L\sequenceOption(...),
        ));
    }
}