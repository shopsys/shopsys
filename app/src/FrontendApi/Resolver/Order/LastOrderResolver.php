<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use App\FrontendApi\Model\Order\OrderFacade;
use App\FrontendApi\Mutation\Login\Exception\InvalidCredentialsUserError;
use App\Model\Order\Order;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;

class LastOrderResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\FrontendApi\Model\Order\OrderFacade
     */
    protected OrderFacade $orderFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        CurrentCustomerUser $currentCustomerUser,
        OrderFacade $orderFacade
    ) {
        $this->currentCustomerUser = $currentCustomerUser;
        $this->orderFacade = $orderFacade;
    }

    /**
     * @return \App\Model\Order\Order|null
     */
    public function resolve(): ?Order
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        if ($customerUser === null) {
            throw new InvalidCredentialsUserError('You need to be logged in.');
        }

        return $this->orderFacade->findLastOrderByCustomerUser($customerUser);
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'lastOrder'];
    }
}
