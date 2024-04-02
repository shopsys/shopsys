<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class CartFacade
{
    protected const DAYS_LIMIT = 130;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFactory $cartFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface $cartItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartRepository $cartRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CartFactory $cartFactory,
        protected readonly ProductRepository $productRepository,
        protected readonly CustomerUserIdentifierFactory $customerUserIdentifierFactory,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculation,
        protected readonly CartItemFactoryInterface $cartItemFactory,
        protected readonly CartRepository $cartRepository,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $quantity
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param bool $isAbsoluteQuantity
     * @return \Shopsys\FrameworkBundle\Model\Cart\AddProductResult
     */
    public function addProductToExistingCart(
        Product $product,
        int $quantity,
        Cart $cart,
        bool $isAbsoluteQuantity = false,
    ): AddProductResult {
        $maximumOrderQuantity = $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $this->domain->getId());
        $notOnStockQuantity = 0;

        if (!is_int($quantity) || $quantity <= 0) {
            throw new InvalidQuantityException($quantity);
        }

        foreach ($cart->getItems() as $item) {
            if ($item->getProduct() === $product) {
                if (!$isAbsoluteQuantity) {
                    $newQuantity = $item->getQuantity() + $quantity;
                } else {
                    $newQuantity = $quantity;
                }

                $addedQuantity = $quantity;

                if ($newQuantity > $maximumOrderQuantity) {
                    $notOnStockQuantity = $newQuantity - $maximumOrderQuantity;
                    $newQuantity = $maximumOrderQuantity;
                    $addedQuantity = $quantity - $notOnStockQuantity;
                }
                $item->changeQuantity($newQuantity);
                $item->changeAddedAt(new DateTime());
                $result = new AddProductResult($item, false, $addedQuantity, $notOnStockQuantity);
                $this->em->persist($result->getCartItem());
                $this->em->flush();

                return $result;
            }
        }

        if ($quantity > $maximumOrderQuantity) {
            $notOnStockQuantity = $quantity - $maximumOrderQuantity;
            $quantity = $maximumOrderQuantity;
        }

        $productPrice = $this->productPriceCalculation->calculatePriceForCurrentUser($product);
        $newCartItem = $this->cartItemFactory->create($cart, $product, $quantity, $productPrice->getPriceWithVat());
        $cart->addItem($newCartItem);
        $cart->setModifiedNow();

        $result = new AddProductResult($newCartItem, true, $quantity, $notOnStockQuantity);

        $this->em->persist($result->getCartItem());
        $this->em->flush();

        return $result;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function deleteCart(Cart $cart)
    {
        foreach ($cart->getItems() as $item) {
            $this->em->remove($item);
        }

        $cart->clean();
        $this->em->remove($cart);
        $this->em->flush();
    }

    /**
     * @param int $cartItemId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProductByCartItemId($cartItemId)
    {
        $cart = $this->findCartOfCurrentCustomerUser();

        if ($cart === null) {
            $message = 'CartItem with id = ' . $cartItemId . ' not found in cart.';

            throw new InvalidCartItemException($message);
        }

        return $cart->getItemById($cartItemId)->getProduct();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    public function findCartByCustomerUserIdentifier(CustomerUserIdentifier $customerUserIdentifier)
    {
        return $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    public function findCartOfCurrentCustomerUser()
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->get();

        return $this->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    public function getCartByCustomerUserIdentifierCreateIfNotExists(CustomerUserIdentifier $customerUserIdentifier)
    {
        $cart = $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);

        if ($cart === null) {
            $cart = $this->cartFactory->create($customerUserIdentifier);

            $this->em->persist($cart);
            $this->em->flush();
        } else {
            $cart->setModifiedNow();
            $this->em->flush();
        }

        return $cart;
    }

    public function deleteOldCarts()
    {
        $this->cartRepository->deleteOldCartsForUnregisteredCustomerUsers(static::DAYS_LIMIT);
        $this->cartRepository->deleteOldCartsForRegisteredCustomerUsers(static::DAYS_LIMIT);
    }

    /**
     * @param string $cartIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    public function findCartByCartIdentifier(string $cartIdentifier): ?Cart
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->getOnlyWithCartIdentifier($cartIdentifier);

        return $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);
    }

    /**
     * @param string $cartItemUuid
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    public function removeItemFromExistingCartByUuid(string $cartItemUuid, Cart $cart): Cart
    {
        $cartItemToRemove = $cart->getItemByUuid($cartItemUuid);

        $cart->removeItemById($cartItemToRemove->getId());

        $this->em->remove($cartItemToRemove);
        $this->em->flush();

        return $cart;
    }
}
