<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\FrontendApi\Model\Cart\Exception\InvalidCartItemUserError;
use App\FrontendApi\Model\Cart\Exception\UnavailableCartUserError;
use App\FrontendApi\Model\Product\ProductFacade;
use App\Model\Cart\AddProductResult;
use App\Model\Cart\Cart;
use App\Model\Cart\CartFacade as BaseCartFacade;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;

class CartFacade
{
    /**
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\FrontendApi\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     */
    public function __construct(
        protected BaseCartFacade $cartFacade,
        protected ProductFacade $productFacade,
        protected Domain $domain,
        protected CurrentCustomerUser $currentCustomerUser,
        protected CustomerUserIdentifierFactory $customerUserIdentifierFactory,
    ) {
    }

    /**
     * @param string $productUuid
     * @param int $quantity
     * @param bool $isAbsoluteQuantity
     * @param \App\Model\Cart\Cart $cart
     * @return \App\Model\Cart\AddProductResult
     */
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
        } catch (ProductNotFoundException $exception) {
            throw new InvalidCartItemUserError(sprintf('Product with UUID "%s" is not available', $productUuid));
        }

        return $this->cartFacade->addProductToExistingCart($product, $quantity, $cart, $isAbsoluteQuantity);
    }

    /**
     * @param string $cartItemUuid
     * @param \App\Model\Cart\Cart $cart
     * @return \App\Model\Cart\Cart
     */
    public function removeItemByUuidFromCart(string $cartItemUuid, Cart $cart): Cart
    {
        try {
            return $this->cartFacade->removeItemFromExistingCartByUuid($cartItemUuid, $cart);
        } catch (InvalidCartItemException $e) {
            throw new InvalidCartItemUserError($e->getMessage());
        }
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $cartUuid
     * @return \App\Model\Cart\Cart
     */
    public function getCartCreateIfNotExists(?CustomerUser $customerUser, ?string $cartUuid): Cart
    {
        if ($customerUser === null && $cartUuid !== null) {
            return $this->getCartByUuid($cartUuid);
        }

        if ($customerUser !== null) {
            $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);
        } else {
            $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCartIdentifier($cartUuid);
        }

        return $this->cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $cartUuid
     * @return \App\Model\Cart\Cart|null
     */
    public function findCart(?CustomerUser $customerUser, ?string $cartUuid): ?Cart
    {
        $this->assertFilledCustomerUserOrUuid($customerUser, $cartUuid);

        if ($customerUser !== null) {
            $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);
            return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
        }

        return $this->getCartByUuid($cartUuid);
    }

    /**
     * @param string $cartUuid
     * @return \App\Model\Cart\Cart
     */
    private function getCartByUuid(string $cartUuid): Cart
    {
        $cart = $this->cartFacade->findCartByCartIdentifier($cartUuid);
        if ($cart === null) {
            $cart = $this->cartFacade->createCart($cartUuid);
        }

        return $cart;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $cartUuid
     */
    private function assertFilledCustomerUserOrUuid(?CustomerUser $customerUser, ?string $cartUuid): void
    {
        if ($customerUser === null && $cartUuid === null) {
            throw new UnavailableCartUserError('Either cart UUID has to be provided, or the user has to be logged in.');
        }
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    public function deleteCart(Cart $cart): void
    {
        $this->cartFacade->deleteCart($cart);
    }
}
