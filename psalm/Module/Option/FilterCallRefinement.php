<?php

declare(strict_types=1);

namespace Fp4\PHP\PsalmIntegration\Module\Option;

use Fp4\PHP\Module\ArrayList as L;
use Fp4\PHP\Module\Option as O;
use Fp4\PHP\PsalmIntegration\PsalmUtils\PsalmApi;
use Fp4\PHP\PsalmIntegration\PsalmUtils\Refinement\FilterRefinement;
use Fp4\PHP\PsalmIntegration\PsalmUtils\Refinement\RefinementType;
use Fp4\PHP\PsalmIntegration\PsalmUtils\Refinement\RefineTypeParams;
use Fp4\PHP\Type\Option;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Union;

use function Fp4\PHP\Module\Functions\constNull;
use function Fp4\PHP\Module\Functions\pipe;

final class FilterCallRefinement implements AfterExpressionAnalysisInterface
{
    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        return pipe(
            $event,
            FilterRefinement::refine(
                function: 'Fp4\PHP\Module\Option\filter',
                getKeyType: O\none,
                getValType: fn(Union $inferred) => pipe(
                    O\some($inferred),
                    O\flatMap(PsalmApi::$cast->toSingleGenericObjectOf(Option::class)),
                    O\flatMap(fn(TGenericObject $option) => L\first($option->type_params)),
                ),
                toReturnType: fn(RefineTypeParams $refined) => pipe(
                    $refined->value,
                    PsalmApi::$create->genericObjectAtomic(Option::class),
                    PsalmApi::$create->union(...),
                ),
                type: RefinementType::Value,
            ),
            constNull(...),
        );
    }
}
