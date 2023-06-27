<?php

declare(strict_types=1);

namespace Fp4\PHP\PsalmIntegration\PsalmUtils\Type;

use Fp4\PHP\Module\ArrayList as L;
use Fp4\PHP\Module\Evidence as Ev;
use Fp4\PHP\Module\Option as O;
use Fp4\PHP\PsalmIntegration\PsalmUtils\PsalmApi;
use Fp4\PHP\Type\Option;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;

use function Fp4\PHP\Module\Functions\constVoid;
use function Fp4\PHP\Module\Functions\pipe;

final class Widening
{
    /**
     * @param non-empty-list<non-empty-string> $names
     * @return Option<void>
     */
    public static function widen(AfterExpressionAnalysisEvent $event, array $names): Option
    {
        return pipe(
            $event->getExpr(),
            Ev\proveOf([FuncCall::class, ConstFetch::class]),
            O\filter(fn(FuncCall|ConstFetch $c) => pipe(
                $names,
                L\contains($c->name->getAttribute('resolvedName')),
            )),
            O\filter(fn(FuncCall|ConstFetch $c) => $c instanceof ConstFetch || !$c->isFirstClassCallable()),
            O\flatMap(PsalmApi::$types->getExprType($event)),
            O\map(PsalmApi::$types->asNonLiteralType(...)),
            O\tap(PsalmApi::$types->setExprType($event->getExpr(), $event)),
            O\map(constVoid(...)),
        );
    }
}