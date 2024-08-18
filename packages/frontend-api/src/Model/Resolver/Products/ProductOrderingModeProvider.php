<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleProvider;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrontendApiBundle\Model\Resolver\Customer\Error\CustomerUserAccessDeniedUserError;

class ProductOrderingModeProvider
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleProvider $customerUserRoleProvider
     */
    public function __construct(
        protected readonly CustomerUserRoleProvider $customerUserRoleProvider,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string
     */
    public function getOrderingModeFromArgument(Argument $argument): string
    {
        $orderingMode = $this->getDefaultOrderingMode($argument);

        if ($argument->offsetExists('orderingMode') && $argument->offsetGet('orderingMode') !== null) {
            $orderingMode = $argument->offsetGet('orderingMode');
        }

        if (!$this->customerUserRoleProvider->canCurrentCustomerUserSeePrices()) {
            if (in_array($orderingMode, [ProductListOrderingConfig::ORDER_BY_PRICE_ASC, ProductListOrderingConfig::ORDER_BY_PRICE_DESC], true)) {
                throw new CustomerUserAccessDeniedUserError('Ordering by price is not allowed for current user.');
            }
        }

        return $orderingMode;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string
     */
    public function getDefaultOrderingMode(Argument $argument): string
    {
        if (isset($argument['search'])) {
            return $this->getDefaultOrderingModeForSearch();
        }

        return $this->getDefaultOrderingModeForListing();
    }

    /**
     * @return string
     */
    public function getDefaultOrderingModeForListing(): string
    {
        return ProductListOrderingConfig::ORDER_BY_PRIORITY;
    }

    /**
     * @return string
     */
    public function getDefaultOrderingModeForSearch(): string
    {
        return ProductListOrderingConfig::ORDER_BY_RELEVANCE;
    }
}
