<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use App\FrontendApi\Model\Order\OrderApiFacade;
use App\FrontendApi\Mutation\Login\Exception\InvalidCredentialsUserError;
use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class LastOrderQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade
     */
    public function __construct(
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly OrderApiFacade $orderApiFacade,
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

        return $this->orderApiFacade->findLastOrderByCustomerUser($customerUser);
    }
}
