<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Cart;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Cart\CartWatcherFacade;
use App\FrontendApi\Model\Cart\CartWithModificationsResult;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;

class CartResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\FrontendApi\Model\Cart\CartWatcherFacade
     */
    private CartWatcherFacade $cartWatcherFacade;

    /**
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartWatcherFacade $cartWatcherFacade
     */
    public function __construct(
        CartFacade $cartFacade,
        CurrentCustomerUser $currentCustomerUser,
        CartWatcherFacade $cartWatcherFacade
    ) {
        $this->cartFacade = $cartFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->cartWatcherFacade = $cartWatcherFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult|null
     */
    public function resolve(Argument $argument): ?CartWithModificationsResult
    {
        $input = CartInputDefaultValueInitializer::initializeDefaultValues($argument);

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->findCart($customerUser, $input['cartUuid']);
        if ($cart === null) {
            return null;
        }

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'getCart',
        ];
    }
}
