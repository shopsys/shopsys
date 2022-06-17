<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Cart;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Cart\CartWatcherFacade;
use App\FrontendApi\Model\Cart\CartWithModificationsResult;
use App\Model\Cart\Payment\CartPaymentFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;

class PaymentInCartMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \App\Model\Cart\Payment\CartPaymentFacade
     */
    private CartPaymentFacade $cartPaymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \App\FrontendApi\Model\Cart\CartWatcherFacade
     */
    private CartWatcherFacade $cartWatcherFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\FrontendApi\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \App\Model\Cart\Payment\CartPaymentFacade $cartPaymentFacade
     */
    public function __construct(
        CurrentCustomerUser $currentCustomerUser,
        CartFacade $cartFacade,
        CartWatcherFacade $cartWatcherFacade,
        CartPaymentFacade $cartPaymentFacade
    ) {
        $this->currentCustomerUser = $currentCustomerUser;
        $this->cartFacade = $cartFacade;
        $this->cartWatcherFacade = $cartWatcherFacade;
        $this->cartPaymentFacade = $cartPaymentFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function changePaymentInCart(Argument $argument): CartWithModificationsResult
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

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'changePaymentInCart' => 'changePaymentInCart',
        ];
    }
}
