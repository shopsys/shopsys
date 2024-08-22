<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerNotFoundException;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class OrderCustomerIdFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'customerId';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag
     */
    public function __construct(
        protected readonly CustomerFacade $customerFacade,
        protected readonly FlashBagInterface $flashBag,
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string[]
     */
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_IS,
            self::OPERATOR_NOT_REGISTERED,
        ];
    }

    /**
     * @return string
     */
    public function getValueFormType(): string
    {
        return NumberType::class;
    }

    /**
     * @return array
     */
    public function getValueFormOptions(): array
    {
        return [];
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData[] $rulesData
     */
    public function extendQueryBuilder(QueryBuilder $queryBuilder, $rulesData): void
    {
        $customerIds = [];

        foreach ($rulesData as $index => $ruleData) {
            if ($ruleData->operator === self::OPERATOR_NOT_REGISTERED) {
                $queryBuilder->andWhere('o.customer IS NULL');

                continue;
            }

            try {
                $customer = $this->customerFacade->getById((int)$ruleData->value);
                $customerIds[] = $customer->getId();
            } catch (CustomerNotFoundException) {
                $this->flashBag->add(
                    FlashMessage::KEY_ERROR,
                    t(
                        'Customer with ID %customerId% not found.',
                        ['%customerId%' => $ruleData->value],
                    ),
                );
            }
        }

        if (count($customerIds) === 0) {
            return;
        }

        $queryBuilder->andWhere('o.customer IN(:customer_id_filter)');
        $queryBuilder->setParameter('customer_id_filter', $customerIds);
    }
}
