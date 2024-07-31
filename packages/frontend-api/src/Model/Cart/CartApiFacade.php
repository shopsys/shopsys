<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\AddProductResult;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFactory;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrontendApiBundle\Model\Cart\Exception\InvalidCartItemUserError;
use Shopsys\FrontendApiBundle\Model\Cart\Exception\UnavailableCartUserError;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;

class CartApiFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFactory $cartFactory
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly CartFacade $cartFacade,
        protected readonly CustomerUserIdentifierFactory $customerUserIdentifierFactory,
        protected readonly CartFactory $cartFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $cartUuid
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function findCart(?CustomerUser $customerUser, ?string $cartUuid): ?Order
    {
        $this->assertFilledCustomerUserOrUuid($customerUser, $cartUuid);

        if ($customerUser !== null) {
            $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

            return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
        }

        return $this->getCartByUuid($cartUuid);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $cartUuid
     */
    protected function assertFilledCustomerUserOrUuid(?CustomerUser $customerUser, ?string $cartUuid): void
    {
        if ($customerUser === null && $cartUuid === null) {
            throw new UnavailableCartUserError('Either cart UUID has to be provided, or the user has to be logged in.');
        }
    }

    /**
     * @param string $cartUuid
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getCartByUuid(string $cartUuid): Order
    {
        $cart = $this->cartFacade->findCartByCartIdentifier($cartUuid);

        if ($cart === null) {
            $cartIdentifier = $this->customerUserIdentifierFactory->getOnlyWithCartIdentifier($cartUuid);
            $cart = $this->cartFactory->create($cartIdentifier);
            $this->em->persist($cart);
            $this->em->flush();
        }

        return $cart;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $cartUuid
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getCartCreateIfNotExists(?CustomerUser $customerUser, ?string $cartUuid): Order
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function deleteCart(Cart $cart): void
    {
        $this->cartFacade->deleteCart($cart);
    }

    /**
     * @param string $productUuid
     * @param int $quantity
     * @param bool $isAbsoluteQuantity
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     * @return \Shopsys\FrameworkBundle\Model\Cart\AddProductResult
     */
    public function addProductByUuidToCart(
        string $productUuid,
        int $quantity,
        bool $isAbsoluteQuantity,
        Order $cart,
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
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    public function removeItemByUuidFromCart(string $cartItemUuid, Cart $cart): Cart
    {
        try {
            return $this->cartFacade->removeItemFromExistingCartByUuid($cartItemUuid, $cart);
        } catch (InvalidCartItemException $e) {
            throw new InvalidCartItemUserError($e->getMessage());
        }
    }
}
