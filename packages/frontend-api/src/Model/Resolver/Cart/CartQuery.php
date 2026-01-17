<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Cart;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CartQuery extends AbstractQuery
{
    public function __construct(
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartWatcherFacade $cartWatcherFacade,
    ) {
    }

    public function cartQuery(Argument $argument): ?CartWithModificationsResult
    {
        $input = $argument['cartInput'] ?? ['cartUuid' => null];

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartApiFacade->findCart($customerUser, $input['cartUuid']);

        if ($cart === null) {
            return null;
        }

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }
}
