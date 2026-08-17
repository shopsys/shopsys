<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;

class CartFacade
{
    protected const DAYS_LIMIT = 130;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CartFactory $cartFactory,
        protected readonly CustomerUserIdentifierFactory $customerUserIdentifierFactory,
        protected readonly Domain $domain,
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculation,
        protected readonly CartItemFactory $cartItemFactory,
        protected readonly CartRepository $cartRepository,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly GiftCartFacade $giftCartFacade,
    ) {
    }

    public function addProductToExistingCart(
        Product $product,
        int $quantity,
        Cart $cart,
        bool $isAbsoluteQuantity = false,
    ): AddProductResult {
        if ($quantity <= 0) {
            throw new InvalidQuantityException($quantity);
        }

        foreach ($cart->getProductCartItems() as $productCartItem) {
            if ($productCartItem->getProduct() === $product) {
                if (!$isAbsoluteQuantity) {
                    $newQuantity = $productCartItem->getQuantity() + $quantity;
                } else {
                    $newQuantity = $quantity;
                }

                $notOnStockQuantity = $this->productAvailabilityFacade->getNotOnStockQuantity($product, $this->domain->getId(), $newQuantity) ?? 0;

                if (!$product->isAllowedNegativeStock()) {
                    $newQuantity -= $notOnStockQuantity;
                }

                $productCartItem->changeQuantity($newQuantity);
                $productCartItem->setAddedAtToNow();
                $result = new AddProductResult($productCartItem, false, $newQuantity, $notOnStockQuantity);
                $this->em->persist($result->getCartItem());
                $this->em->flush();

                $this->giftCartFacade->refreshProductGiftsInCart($cart, $this->domain->getId());

                return $result;
            }
        }

        $productPrice = $this->productPriceCalculation->calculatePricesForCurrentUser($product)->sellingProductPrice;
        $notOnStockQuantity = $this->productAvailabilityFacade->getNotOnStockQuantity($product, $this->domain->getId(), $quantity) ?? 0;

        if (!$product->isAllowedNegativeStock()) {
            $quantity -= $notOnStockQuantity;
        }

        $newCartItem = $this->cartItemFactory->create($cart, $product, $quantity, $productPrice->getPrice()->getPriceWithVat(), CartItemTypeEnum::TYPE_PRODUCT);
        $cart->addItem($newCartItem);
        $cart->setModifiedNow();

        $result = new AddProductResult($newCartItem, true, $quantity, $notOnStockQuantity);

        $this->em->persist($result->getCartItem());
        $this->em->flush();

        $this->giftCartFacade->refreshProductGiftsInCart($cart, $this->domain->getId());

        return $result;
    }

    public function deleteCart(Cart $cart): void
    {
        foreach ($cart->getItems() as $item) {
            $this->em->remove($item);
        }

        $cart->clean();
        $this->em->remove($cart);
        $this->em->flush();
    }

    public function findCartByCustomerUserIdentifier(
        CustomerUserIdentifier $customerUserIdentifier,
    ): ?Cart {
        return $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);
    }

    public function getCartByCustomerUserIdentifierCreateIfNotExists(
        CustomerUserIdentifier $customerUserIdentifier,
    ): Cart {
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

    public function deleteOldCarts(): void
    {
        $this->cartRepository->deleteOldCartsForUnregisteredCustomerUsers(static::DAYS_LIMIT);
        $this->cartRepository->deleteOldCartsForRegisteredCustomerUsers(static::DAYS_LIMIT);
    }

    public function findCartByCartIdentifier(string $cartIdentifier): ?Cart
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->getOnlyWithCartIdentifier($cartIdentifier);

        return $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);
    }

    public function removeItemFromExistingCartByUuid(string $cartItemUuid, Cart $cart): Cart
    {
        $cartItemToRemove = $cart->getItemByUuid($cartItemUuid);

        $cart->removeItemById($cartItemToRemove->getId());

        $this->em->remove($cartItemToRemove);
        $this->em->flush();

        return $cart;
    }
}
