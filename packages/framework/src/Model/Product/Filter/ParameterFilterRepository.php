<?php

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;

class ParameterFilterRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[] $parameters
     */
    public function filterByParameters(QueryBuilder $productsQueryBuilder, array $parameters): void
    {
        $parameterIndex = 1;
        $valueIndex = 1;

        foreach ($parameters as $parameterFilterData) {
            /* @var $parameterFilterData \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData */

            if (count($parameterFilterData->values) === 0) {
                continue;
            }

            $parameterQueryBuilder = $this->getParameterQueryBuilder(
                $parameterFilterData,
                $productsQueryBuilder->getEntityManager(),
                $parameterIndex,
                $valueIndex
            );

            $productsQueryBuilder->andWhere($productsQueryBuilder->expr()->exists($parameterQueryBuilder));
            foreach ($parameterQueryBuilder->getParameters() as $parameter) {
                $productsQueryBuilder->setParameter($parameter->getName(), $parameter->getValue());
            }

            $parameterIndex++;
        }
    }
    
    private function getParameterQueryBuilder(
        ParameterFilterData $parameterFilterData,
        EntityManagerInterface $em,
        int $parameterIndex,
        int &$valueIndex
    ): \Doctrine\ORM\QueryBuilder {
        $ppvAlias = 'ppv' . $parameterIndex;
        $parameterPlaceholder = ':parameter' . $parameterIndex;

        $parameterQueryBuilder = $em->createQueryBuilder();

        $valuesExpr = $this->getValuesExpr(
            $parameterFilterData->values,
            $parameterQueryBuilder,
            $ppvAlias,
            $valueIndex
        );

        $parameterQueryBuilder
            ->select('1')
            ->from(ProductParameterValue::class, $ppvAlias)
            ->where($ppvAlias . '.product = p')
                ->andWhere($ppvAlias . '.parameter = ' . $parameterPlaceholder)
                ->andWhere($valuesExpr);

        $parameterQueryBuilder->setParameter($parameterPlaceholder, $parameterFilterData->parameter);

        return $parameterQueryBuilder;
    }

    /**
     * Generates:
     * ppv.value = :parameterValueM OR ppv.value = :parameterValueN OR ...
     *
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues
     */
    private function getValuesExpr(
        array $parameterValues,
        QueryBuilder $parameterQueryBuilder,
        string $ppvAlias,
        int &$valueIndex
    ): \Doctrine\ORM\Query\Expr {
        $valuesExpr = $parameterQueryBuilder->expr()->orX();

        foreach ($parameterValues as $parameterValue) {
            $valuePlaceholder = ':parameterValue' . $valueIndex;

            $valuesExpr->add($ppvAlias . '.value = ' . $valuePlaceholder);
            $parameterQueryBuilder->setParameter($valuePlaceholder, $parameterValue);

            $valueIndex++;
        }

        return $valuesExpr;
    }
}
