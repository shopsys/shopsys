<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Cart;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Cart\CartWatcherFacade;
use App\FrontendApi\Model\Cart\CartWithModificationsResult;
use App\Model\Cart\Transport\CartTransportFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;

class TransportMutation extends AbstractMutation
{
    /**
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\FrontendApi\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \App\Model\Cart\Transport\CartTransportFacade $cartTransportFacade
     */
    public function __construct(
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly CartFacade $cartFacade,
        private readonly CartWatcherFacade $cartWatcherFacade,
        private readonly CartTransportFacade $cartTransportFacade
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function changeTransportInCartMutation(Argument $argument): CartWithModificationsResult
    {
        $input = $argument['input'];
        $cartUuid = $input['cartUuid'];
        $transportUuid = $input['transportUuid'];
        $pickupPlaceIdentifier = $input['pickupPlaceIdentifier'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        $this->cartTransportFacade->updateTransportInCart($cart, $transportUuid, $pickupPlaceIdentifier);

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }
}
