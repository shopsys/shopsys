<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class LastOrderQuery extends AbstractQuery
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderApiFacade $orderApiFacade,
    ) {
    }

    public function lastOrderQuery(): ?Order
    {
        $customerUser = $this->currentCustomerUser->getCurrentCustomerUser();

        return $this->orderApiFacade->findLastOrderByCustomerUser($customerUser);
    }
}
