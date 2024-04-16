<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Cart;

use App\FrontendApi\Model\Cart\AddToCartResult;
use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Cart\CartWatcherFacade;
use App\FrontendApi\Model\Cart\CartWithModificationsResult;
use App\FrontendApi\Model\Cart\Exception\InvalidCartItemUserError;
use App\FrontendApi\Model\Order\OrderApiFacade;
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
     * @param \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade
     */
    public function __construct(
        private readonly CartFacade $cartFacade,
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly CartWatcherFacade $cartWatcherFacade,
        private readonly OrderApiFacade $orderApiFacade,
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function addOrderItemsToCartMutation(
        Argument $argument,
        InputValidator $validator,
    ): CartWithModificationsResult {
        $validator->validate();

        $input = $argument['input'];
        $orderUuid = $input['orderUuid'];
        $cartUuid = $input['cartUuid'];
        $shouldMerge = $input['shouldMerge'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $order = $this->orderApiFacade->getByUuid($orderUuid);

        if (!$shouldMerge) {
            $this->cartFacade->deleteCart($cart);
            $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        }

        $notAddedProducts = [];
        $addProductResults = [];

        foreach ($order->getProductItems() as $orderItem) {
            try {
                $addProductResults[] = $this->cartFacade->addProductByUuidToCart($orderItem->getProduct()->getUuid(), $orderItem->getQuantity(), false, $cart);
            } catch (InvalidCartItemUserError) {
                $notAddedProducts[] = $orderItem->getProduct();
            }
        }

        $cartWithModificationsResult = $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
        $cartWithModificationsResult->addProductsNotAddedByMultipleAddition($notAddedProducts);

        foreach ($addProductResults as $addProductResult) {
            if ($addProductResult->getNotOnStockQuantity() > 0) {
                $cartWithModificationsResult->addCartItemWithChangedQuantity($addProductResult->getCartItem());
            }
        }

        return $cartWithModificationsResult;
    }
}
