<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Cart;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Cart\CartWatcherFacade;
use App\FrontendApi\Model\Cart\CartWithModificationsResult;
use App\Model\Cart\Payment\CartPaymentFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;

class PaymentInCartMutation extends AbstractMutation
{
    /**
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\FrontendApi\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \App\Model\Cart\Payment\CartPaymentFacade $cartPaymentFacade
     */
    public function __construct(
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly CartFacade $cartFacade,
        private readonly CartWatcherFacade $cartWatcherFacade,
        private readonly CartPaymentFacade $cartPaymentFacade
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function changePaymentInCartMutation(Argument $argument): CartWithModificationsResult
    {
        $input = $argument['input'];
        $cartUuid = $input['cartUuid'];
        $paymentUuid = $input['paymentUuid'];
        $paymentGoPayBankSwift = $input['paymentGoPayBankSwift'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        $this->cartPaymentFacade->updatePaymentInCart($cart, $paymentUuid, $paymentGoPayBankSwift);

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }
}
