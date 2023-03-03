<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use App\FrontendApi\Model\Order\OrderFacade;
use App\FrontendApi\Mutation\Login\Exception\InvalidCredentialsUserError;
use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class LastOrderQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly OrderFacade $orderFacade
    ) {
    }

    /**
     * @return \App\Model\Order\Order|null
     */
    public function lastOrderQuery(): ?Order
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        if ($customerUser === null) {
            throw new InvalidCredentialsUserError('You need to be logged in.');
        }

        return $this->orderFacade->findLastOrderByCustomerUser($customerUser);
    }
}
