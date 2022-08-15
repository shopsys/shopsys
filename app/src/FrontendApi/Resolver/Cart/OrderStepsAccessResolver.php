<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Cart;

use App\FrontendApi\Model\Cart\CartFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;

class OrderStepsAccessResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly CartFacade $cartFacade
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array{canAccessTransportAndPayment: bool, canAccessContactInformation: bool}
     */
    public function resolve(Argument $argument): array
    {
        $input = CartInputDefaultValueInitializer::initializeDefaultValues($argument);

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->findCart($customerUser, $input['cartUuid']);

        return [
            'canAccessTransportAndPayment' => !$cart->isEmpty(),
            'canAccessContactInformation' => !$cart->isEmpty() && $cart->getTransport() !== null && $cart->getPayment() !== null,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'checkOrderStepsAccessibility',
        ];
    }
}
