<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterOperatorNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OrderStatusFilter implements AdvancedSearchFilterInterface
{
    public const NAME = 'orderStatus';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     */
    public function __construct(protected readonly OrderStatusFacade $orderStatusFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_IS,
            self::OPERATOR_IS_NOT,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFormType(): string|\Symfony\Component\Form\FormTypeInterface
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     * @return string[]|bool[]|\Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[][]
     */
    public function getValueFormOptions(): array
    {
        return [
            'choices' => $this->orderStatusFacade->getAll(),
            'choice_label' => 'name',
            'choice_value' => 'id',
            'expanded' => false,
            'multiple' => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function extendQueryBuilder(QueryBuilder $queryBuilder, $rulesData): void
    {
        foreach ($rulesData as $index => $ruleData) {
            $dqlOperator = $this->getContainsDqlOperator($ruleData->operator);
            $searchValue = $ruleData->value;
            $parameterName = 'orderStatusId_' . $index;
            $queryBuilder->andWhere('o.status ' . $dqlOperator . ' :' . $parameterName);
            $queryBuilder->setParameter($parameterName, $searchValue);
        }
    }

    /**
     * @param string $operator
     * @return string
     */
    protected function getContainsDqlOperator($operator): string
    {
        switch ($operator) {
            case self::OPERATOR_IS:
                return '=';
            case self::OPERATOR_IS_NOT:
                return '!=';
        }

        throw new AdvancedSearchFilterOperatorNotFoundException($operator);
    }
}
