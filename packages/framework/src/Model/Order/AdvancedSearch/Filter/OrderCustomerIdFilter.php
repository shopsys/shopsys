<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\OrderAdvancedSearchFacade;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class OrderCustomerIdFilter extends AbstractAdvancedSearchFilter
{
    public const string NAME = 'customerId';

    public function __construct(
        protected readonly CustomerFacade $customerFacade,
        protected readonly FlashBagInterface $flashBag,
    ) {
    }

    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string[]
     */
    #[Override]
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_IS,
            self::OPERATOR_NOT_REGISTERED,
        ];
    }

    #[Override]
    public function getValueFormType(): string
    {
        return NumberType::class;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData[] $rulesData
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        $customerIds = [];

        foreach ($rulesData as $ruleData) {
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

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getEntityType(): string
    {
        return OrderAdvancedSearchFacade::getEntityType();
    }
}
