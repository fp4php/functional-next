<?php

declare(strict_types=1);

namespace Fp4\PHP\PsalmIntegration\PsalmUtils\Type;

use Closure;
use Fp4\PHP\Module\Option as O;
use Fp4\PHP\Type\Option;
use PhpParser\Node\Expr;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\StatementsSource;
use Psalm\Type\Union;

final class Types
{
    /**
     * @return Closure(Expr): void
     */
    public function setExprType(Union $type, AfterExpressionAnalysisEvent|StatementsSource $scope): Closure
    {
        return function (Expr $expr) use ($type, $scope): void {
            $types = match (true) {
                $scope instanceof StatementsSource => $scope->getNodeTypeProvider(),
                $scope instanceof AfterExpressionAnalysisEvent => $scope->getStatementsSource()->getNodeTypeProvider(),
            };

            $types->setType($expr, $type);
        };
    }

    /**
     * @return Closure(Union): void
     */
    public function setType(Expr $expr, AfterExpressionAnalysisEvent|StatementsSource $scope): Closure
    {
        return function (Union $type) use ($expr, $scope): void {
            $types = match (true) {
                $scope instanceof StatementsSource => $scope->getNodeTypeProvider(),
                $scope instanceof AfterExpressionAnalysisEvent => $scope->getStatementsSource()->getNodeTypeProvider(),
            };

            $types->setType($expr, $type);
        };
    }

    /**
     * @return Closure(Expr): Option<Union>
     */
    public function getExprType(AfterExpressionAnalysisEvent|StatementsSource $scope): Closure
    {
        return function (Expr $expr) use ($scope): Option {
            $types = match (true) {
                $scope instanceof StatementsSource => $scope->getNodeTypeProvider(),
                $scope instanceof AfterExpressionAnalysisEvent => $scope->getStatementsSource()->getNodeTypeProvider(),
            };

            return O\fromNullable($types->getType($expr));
        };
    }

    public function asNonLiteralType(Union $type): Union
    {
        return AsNonLiteralType::transform($type);
    }
}