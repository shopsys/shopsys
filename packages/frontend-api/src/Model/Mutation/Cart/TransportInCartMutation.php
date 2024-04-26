<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Cart;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;

class TransportInCartMutation extends AbstractMutation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportFacade $cartTransportFacade
     */
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CartWatcherFacade $cartWatcherFacade,
        protected readonly CartTransportFacade $cartTransportFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult
     */
    public function changeTransportInCartMutation(Argument $argument): CartWithModificationsResult
    {
        $input = $argument['input'];
        $cartUuid = $input['cartUuid'];
        $transportUuid = $input['transportUuid'];
        $pickupPlaceIdentifier = $input['pickupPlaceIdentifier'];

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        $this->cartTransportFacade->updateTransportInCart($cart, $transportUuid, $pickupPlaceIdentifier);

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }
}
