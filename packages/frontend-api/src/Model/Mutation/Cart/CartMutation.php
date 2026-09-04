<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Cart;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Cart\AddToCartResult;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;
use Shopsys\FrontendApiBundle\Model\Cart\Exception\InvalidCartItemUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;

class CartMutation extends AbstractMutation
{
    public function __construct(
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartWatcherFacade $cartWatcherFacade,
        protected readonly OrderApiFacade $orderApiFacade,
    ) {
    }

    public function addToCartMutation(Argument $argument, InputValidator $validator): AddToCartResult
    {
        $validator->validate();

        $input = $argument['input'];

        $productUuid = $input['productUuid'];
        $quantity = $input['quantity'];
        $cartUuid = $input['cartUuid'];
        $isAbsoluteQuantity = $input['isAbsoluteQuantity'];

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $addProductResult = $this->cartApiFacade->addProductByUuidToCart(
            $productUuid,
            $quantity,
            $isAbsoluteQuantity,
            $cart,
        );

        $cartWithModifications = $this->cartWatcherFacade->getCheckedCartWithModifications($cart);

        return new AddToCartResult($cartWithModifications, $addProductResult);
    }

    public function removeFromCartMutation(Argument $argument, InputValidator $validator): ?CartWithModificationsResult
    {
        $validator->validate();

        $input = $argument['input'];

        $cartUuid = $input['cartUuid'];
        $cartItemUuid = $input['cartItemUuid'];

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $cart = $this->cartApiFacade->removeItemByUuidFromCart(
            $cartItemUuid,
            $cart,
        );

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }

    public function setCartItemAdditionalServicesMutation(
        Argument $argument,
        InputValidator $validator,
    ): CartWithModificationsResult {
        $validator->validate();

        $input = $argument['input'];

        $cartUuid = $input['cartUuid'];
        $cartItemUuid = $input['cartItemUuid'];
        $additionalServiceUuids = $input['additionalServiceUuids'];

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $cart = $this->cartApiFacade->setCartItemAdditionalServicesByUuid(
            $cartItemUuid,
            $additionalServiceUuids,
            $cart,
        );

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }

    public function addOrderItemsToCartMutation(
        Argument $argument,
        InputValidator $validator,
    ): CartWithModificationsResult {
        $validator->validate();

        $input = $argument['input'];
        $orderUuid = $input['orderUuid'];
        $cartUuid = $input['cartUuid'];
        $shouldMerge = $input['shouldMerge'];

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $order = $this->orderApiFacade->getByUuid($orderUuid);

        if (!$shouldMerge) {
            $this->cartApiFacade->deleteCart($cart);
            $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        }

        $notAddedProducts = [];

        foreach ($order->getProductItems() as $orderItem) {
            if ($orderItem->getProduct() === null) {
                continue;
            }

            try {
                $this->cartApiFacade->addProductByUuidToCart($orderItem->getProduct()->getUuid(), $orderItem->getQuantity(), false, $cart);
            } catch (InvalidCartItemUserError) {
                $notAddedProducts[] = $orderItem->getProduct();
            }
        }

        $cartWithModificationsResult = $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
        $cartWithModificationsResult->addProductsNotAddedByMultipleAddition($notAddedProducts);

        return $cartWithModificationsResult;
    }
}
