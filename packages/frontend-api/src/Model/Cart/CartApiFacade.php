<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\AddProductResult;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFactory;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrontendApiBundle\Model\Cart\Exception\InvalidCartItemUserError;
use Shopsys\FrontendApiBundle\Model\Cart\Exception\UnavailableCartUserError;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;

class CartApiFacade
{
    public function __construct(
        protected readonly CartFacade $cartFacade,
        protected readonly CustomerUserIdentifierFactory $customerUserIdentifierFactory,
        protected readonly CartFactory $cartFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    public function findCart(?CustomerUser $customerUser, ?string $cartUuid): ?Cart
    {
        $this->assertFilledCustomerUserOrUuid($customerUser, $cartUuid);

        if ($customerUser !== null) {
            $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

            return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
        }

        return $this->getCartByUuid($cartUuid);
    }

    protected function assertFilledCustomerUserOrUuid(?CustomerUser $customerUser, ?string $cartUuid): void
    {
        if ($customerUser === null && $cartUuid === null) {
            throw new UnavailableCartUserError('Either cart UUID has to be provided, or the user has to be logged in.');
        }
    }

    public function getCartByUuid(string $cartUuid): Cart
    {
        $cart = $this->cartFacade->findCartByCartIdentifier($cartUuid);

        if ($cart === null) {
            $cartIdentifier = $this->customerUserIdentifierFactory->getOnlyWithCartIdentifier($cartUuid);
            $cart = $this->cartFactory->create($cartIdentifier);
        }

        return $cart;
    }

    public function getCartCreateIfNotExists(?CustomerUser $customerUser, ?string $cartUuid): Cart
    {
        if ($customerUser === null && $cartUuid !== null) {
            $cart = $this->getCartByUuid($cartUuid);

            if ($cart->getCustomerUser() === null) {
                return $cart;
            }
        }

        if ($customerUser !== null) {
            $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);
        } else {
            $customerUserIdentifier = $this->customerUserIdentifierFactory->getOnlyWithCartIdentifier($cartUuid);
        }

        return $this->cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);
    }

    public function deleteCart(Cart $cart): void
    {
        $this->cartFacade->deleteCart($cart);
    }

    public function addProductByUuidToCart(
        string $productUuid,
        int $quantity,
        bool $isAbsoluteQuantity,
        Cart $cart,
    ): AddProductResult {
        try {
            $product = $this->productFacade->getSellableByUuid(
                $productUuid,
                $this->domain->getId(),
                $this->currentCustomerUser->getPricingGroup(),
            );

            return $this->cartFacade->addProductToExistingCart($product, $quantity, $cart, $isAbsoluteQuantity);
        } catch (ProductNotFoundException|InvalidQuantityException) {
            throw new InvalidCartItemUserError(sprintf('Product with UUID "%s" is not available', $productUuid));
        }
    }

    public function removeItemByUuidFromCart(string $cartItemUuid, Cart $cart): Cart
    {
        try {
            return $this->cartFacade->removeItemFromExistingCartByUuid($cartItemUuid, $cart);
        } catch (InvalidCartItemException $e) {
            throw new InvalidCartItemUserError($e->getMessage());
        }
    }
}
