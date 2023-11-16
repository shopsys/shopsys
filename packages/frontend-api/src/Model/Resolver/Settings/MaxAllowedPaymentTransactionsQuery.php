<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class MaxAllowedPaymentTransactionsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @return int
     */
    public function maxAllowedPaymentTransactionsQuery(): int
    {
        /** @var \Shopsys\FrameworkBundle\Model\Order\Order $orderClass */
        $orderClass = $this->entityNameResolver->resolve(Order::class);

        return $orderClass::MAX_TRANSACTION_COUNT;
    }
}
