<?php

declare(strict_types=1);

namespace Fp4\PHP\PsalmIntegration\PsalmUtils\Refinement;

use Closure;
use Fp4\PHP\Module\Option as O;
use Fp4\PHP\PsalmIntegration\PsalmUtils\PsalmApi;
use Fp4\PHP\Type\Option;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Type\Atomic\TClosure;
use Psalm\Type\Union;

use function Fp4\PHP\Module\Functions\pipe;

final class RefineTypeParams
{
    public function __construct(
        public readonly Union $key,
        public readonly Union $value,
    ) {
    }

    /**
     * @param callable(Union): Option<Union> $extractKey
     * @param callable(Union): Option<Union> $extractValue
     * @return Closure(AfterExpressionAnalysisEvent): Option<RefineTypeParams>
     */
    public static function from(callable $extractKey, callable $extractValue): Closure
    {
        return fn(AfterExpressionAnalysisEvent $event) => pipe(
            O\bindable(),
            O\bind(
                closureReturnType: fn() => pipe(
                    O\some($event->getExpr()),
                    O\flatMap(PsalmApi::$type->get($event)),
                    O\flatMap(PsalmApi::$cast->toSingleAtomicOf(TClosure::class)),
                    O\flatMap(fn(TClosure $closure) => O\fromNullable($closure->return_type)),
                ),
                keyType: fn($i) => $extractKey($i->closureReturnType),
                valueType: fn($i) => $extractValue($i->closureReturnType),
            ),
            O\map(fn($i) => new self($i->keyType, $i->valueType)),
        );
    }
}
