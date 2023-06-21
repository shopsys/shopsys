<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Cart;

use App\FrontendApi\Model\Cart\AddToCartResult;
use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Cart\CartWatcherFacade;
use App\FrontendApi\Model\Cart\CartWithModificationsResult;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;

class CartMutation extends AbstractMutation
{
    /**
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartWatcherFacade $cartWatcherFacade
     */
    public function __construct(
        private CartFacade $cartFacade,
        private CurrentCustomerUser $currentCustomerUser,
        protected CartWatcherFacade $cartWatcherFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \App\FrontendApi\Model\Cart\AddToCartResult
     */
    public function addToCartMutation(Argument $argument, InputValidator $validator): AddToCartResult
    {
        $validator->validate();

        $input = $argument['input'];

        $productUuid = $input['productUuid'];
        $quantity = $input['quantity'];
        $cartUuid = $input['cartUuid'];
        $isAbsoluteQuantity = $input['isAbsoluteQuantity'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $addProductResult = $this->cartFacade->addProductByUuidToCart(
            $productUuid,
            $quantity,
            $isAbsoluteQuantity,
            $cart,
        );

        $cartWithModifications = $this->cartWatcherFacade->getCheckedCartWithModifications($cart);

        return new AddToCartResult($cartWithModifications, $addProductResult);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult|null
     */
    public function removeFromCartMutation(Argument $argument, InputValidator $validator): ?CartWithModificationsResult
    {
        $validator->validate();

        $input = $argument['input'];

        $cartUuid = $input['cartUuid'];
        $cartItemUuid = $input['cartItemUuid'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $cart = $this->cartFacade->removeItemByUuidFromCart(
            $cartItemUuid,
            $cart,
        );

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }
}
