<?php

declare(strict_types=1);

namespace Fp4\PHP\Module\Option;

use Closure;
use Fp4\PHP\Type\Bindable;
use Fp4\PHP\Type\None;
use Fp4\PHP\Type\Option;
use Fp4\PHP\Type\Some;
use Throwable;

// region: constructor

/**
 * @template A
 *
 * @param A $value
 * @return Option<A>
 */
function some(mixed $value): Option
{
    return new Some($value);
}

/**
 * @template A
 *
 * @param A $value
 * @return Option<A>
 */
function fromLiteral(mixed $value): Option
{
    return some($value);
}

const none = new None();

/**
 * @template A
 *
 * @param A|null $value
 * @return Option<A>
 */
function fromNullable(mixed $value): Option
{
    return null !== $value ? some($value) : none;
}

/**
 * @template A
 *
 * @param callable(): A $callback
 * @return Option<A>
 */
function tryCatch(callable $callback): Option
{
    try {
        return some($callback());
    } catch (Throwable) {
        return none;
    }
}

// endregion: constructor

// region: destructors

/**
 * @template A
 *
 * @param Option<A> $option
 * @return A|null
 */
function getOrNull(Option $option): mixed
{
    return isSome($option)
        ? $option->value
        : null;
}

/**
 * @template A
 * @template B
 *
 * @param callable(): B $call
 * @return Closure(Option<A>): (A|B)
 */
function getOrCall(callable $call): Closure
{
    return fn (Option $option) => isSome($option)
        ? $option->value
        : $call();
}

/**
 * @template A
 * @template TNone
 * @template TSome
 *
 * @param callable(): TNone $ifNone
 * @param callable(A): TSome $ifSome
 * @return Closure(Option<A>): (TNone|TSome)
 */
function fold(callable $ifNone, callable $ifSome): Closure
{
    return fn (Option $option) => isSome($option)
        ? $ifSome($option->value)
        : $ifNone();
}

// endregion: destructors

// region: refinements

/**
 * @template A
 *
 * @param Option<A> $option
 * @psalm-assert-if-true Some<A> $option
 * @psalm-assert-if-false None $option
 */
function isSome(Option $option): bool
{
    return $option instanceof Some;
}

/**
 * @template A
 *
 * @param Option<A> $option
 * @psalm-assert-if-true None $option
 * @psalm-assert-if-false Some<A> $option
 */
function isNone(Option $option): bool
{
    return $option instanceof None;
}

// endregion: refinements

// region: ops

/**
 * @template A
 * @template B
 *
 * @param callable(A): B $callback
 * @return Closure(Option<A>): Option<B>
 */
function map(callable $callback): Closure
{
    return fn (Option $option) => isSome($option)
        ? some($callback($option->value))
        : none;
}

/**
 * @template A
 *
 * @param callable(A): void $callback
 * @return Closure(Option<A>): Option<A>
 */
function tap(callable $callback): Closure
{
    return function (Option $option) use ($callback) {
        if (isSome($option)) {
            $callback($option->value);

            return some($option->value);
        }

        return none;
    };
}

/**
 * @template A
 * @template B
 *
 * @param callable(A): Option<B> $callback
 * @return Closure(Option<A>): Option<B>
 */
function flatMap(callable $callback): Closure
{
    return fn (Option $option) => isSome($option)
        ? $callback($option->value)
        : none;
}

/**
 * @template A
 * @template B
 *
 * @param callable(): Option<B> $callback
 * @return Closure(Option<A>): Option<A|B>
 */
function orElse(callable $callback): Closure
{
    return fn (Option $option) => isSome($option)
        ? some($option->value)
        : $callback();
}

/**
 * @template A
 *
 * @param callable(A): bool $callback
 * @return Closure(Option<A>): Option<A>
 */
function filter(callable $callback): Closure
{
    return fn (Option $a) => isSome($a) && $callback($a->value)
        ? some($a->value)
        : none;
}

// endregion: ops

// region: bindable

/**
 * @return Option<Bindable>
 */
function bindable(): Option
{
    return some(new Bindable());
}

/**
 * @param Closure(Bindable): Option ...$params
 * @return Closure(Option<Bindable>): Option<Bindable>
 */
function bind(Closure ...$params): Closure
{
    return function (Option $optionBindable) use ($params) {
        if (isSome($optionBindable)) {
            $bindable = $optionBindable->value;
        } else {
            return none;
        }

        foreach ($params as $key => $param) {
            $result = $param($bindable);

            if (isSome($result)) {
                $bindable = $bindable->with((string) $key, $result->value);
            } else {
                return none;
            }
        }

        return some($bindable);
    };
}

// endregion: bindable
