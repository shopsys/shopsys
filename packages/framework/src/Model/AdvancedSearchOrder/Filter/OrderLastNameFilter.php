<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterOperatorNotFoundException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeInterface;

class OrderLastNameFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'customerLastName';

    public function __construct(
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[Override]
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_CONTAINS,
            self::OPERATOR_NOT_CONTAINS,
        ];
    }

    #[Override]
    public function getValueFormType(): FormTypeInterface|string
    {
        return TextType::class;
    }

    #[Override]
    public function getValueFormOptions(): array
    {
        return [];
    }

    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        foreach ($rulesData as $index => $ruleData) {
            if ($ruleData->value === null || $ruleData->value === '') {
                $searchValue = '%';
            } else {
                $searchValue = $this->databaseSearchingHelper->getLikeSearchString($ruleData->value) . '%';
            }
            $dqlOperator = $this->getContainsDqlOperator($ruleData->operator);
            $parameterName = 'lastName_' . $index;
            $queryBuilder->andWhere(
                'NORMALIZED(o.lastName) ' . $dqlOperator . ' NORMALIZED(:' . $parameterName . ') OR NORMALIZED(o.deliveryLastName) ' . $dqlOperator . ' NORMALIZED(:' . $parameterName . ')',
            );
            $queryBuilder->setParameter($parameterName, $searchValue);
        }
    }

    protected function getContainsDqlOperator(string $operator): string
    {
        switch ($operator) {
            case self::OPERATOR_CONTAINS:
                return 'LIKE';
            case self::OPERATOR_NOT_CONTAINS:
                return 'NOT LIKE';
        }

        throw new AdvancedSearchFilterOperatorNotFoundException($operator);
    }
}
