<?php

declare(strict_types=1);

namespace App\Model\AdvancedSearch\Filter;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductAvailabilityFilter as BaseProductAvailabilityFilter;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductAvailabilityFilter extends BaseProductAvailabilityFilter
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param mixed $rulesData
     */
    public function extendQueryBuilder(QueryBuilder $queryBuilder, $rulesData)
    {
        $isNotAvailabilities = [];

        foreach ($rulesData as $index => $ruleData) {
            if ($ruleData->operator === self::OPERATOR_IS) {
                $tableAlias = 'a' . $index;
                $availabilityParameter = 'availability' . $index;
                $queryBuilder->join('p.availability', $tableAlias, Join::WITH, $tableAlias . '.id = :' . $availabilityParameter);
                $queryBuilder->setParameter($availabilityParameter, $ruleData->value);
            } elseif ($ruleData->operator === self::OPERATOR_IS_NOT) {
                $isNotAvailabilities[] = $ruleData->value;
            }
        }

        if (count($isNotAvailabilities) > 0) {
            $subQuery = 'SELECT availability_p.id FROM ' . Product::class . ' availability_p
                JOIN availability_p.availability _a WITH _a.id IN (:isNotAvailabilities)';
            $queryBuilder->andWhere('p.id NOT IN (' . $subQuery . ')');
            $queryBuilder->setParameter('isNotAvailabilities', $isNotAvailabilities);
        }
    }
}
