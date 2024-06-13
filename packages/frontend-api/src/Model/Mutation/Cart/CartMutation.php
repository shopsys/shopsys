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
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade $orderApiFacade
     */
    public function __construct(
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartWatcherFacade $cartWatcherFacade,
        protected readonly OrderApiFacade $orderApiFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrontendApiBundle\Model\Cart\AddToCartResult
     */
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult|null
     */
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult
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

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $order = $this->orderApiFacade->getByUuid($orderUuid);

        if (!$shouldMerge) {
            $this->cartApiFacade->deleteCart($cart);
            $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        }

        $notAddedProducts = [];
        $addProductResults = [];

        foreach ($order->getProductItems() as $orderItem) {
            if ($orderItem->getProduct() === null) {
                continue;
            }

            try {
                $addProductResults[] = $this->cartApiFacade->addProductByUuidToCart($orderItem->getProduct()->getUuid(), $orderItem->getQuantity(), false, $cart);
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
