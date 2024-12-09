<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Cart;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
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
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartWatcherFacade $cartWatcherFacade,
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly CartFacade $cartFacade,
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @throws \Overblog\GraphQLBundle\Validator\Exception\ArgumentsValidationException
     * @return array
     */
    public function removeCartMutation(Argument $argument, InputValidator $validator): array
    {
        $validator->validate();
        $input = $argument['input'];

        if (array_key_exists('cartUuid', $input) && $input['cartUuid'] !== null) {
            $cart = $this->cartApiFacade->findCart(null, $input['cartUuid']);
        } else {
            $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
            $cart = $this->cartApiFacade->findCart($customerUser, null);
        }

        if ($cart !== null) {
            $this->cartFacade->deleteCart($cart);
        }

        return [
            true,
        ];
    }
}
