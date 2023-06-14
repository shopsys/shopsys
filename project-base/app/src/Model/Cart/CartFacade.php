<?php

declare(strict_types=1);

namespace App\Model\Cart;

use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Product;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart as BaseCart;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade as BaseCartFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFactory;
use Shopsys\FrameworkBundle\Model\Cart\CartRepository;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @property \App\Model\Cart\Watcher\CartWatcherFacade $cartWatcherFacade
 * @property \App\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
 * @method \App\Model\Product\Product getProductByCartItemId(int $cartItemId)
 * @method \App\Model\Cart\Cart|null findCartOfCurrentCustomerUser()
 * @method \App\Model\Cart\Cart getCartOfCurrentCustomerUserCreateIfNotExists()
 * @method \App\Model\Cart\Cart getCartByCustomerUserIdentifierCreateIfNotExists(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier)
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 */
class CartFacade extends BaseCartFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFactory $cartFactory
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \App\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory $cartItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartRepository $cartRepository
     * @param \App\Model\Cart\Watcher\CartWatcherFacade $cartWatcherFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        CartFactory $cartFactory,
        ProductRepository $productRepository,
        CustomerUserIdentifierFactory $customerUserIdentifierFactory,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        ProductPriceCalculationForCustomerUser $productPriceCalculation,
        CartItemFactoryInterface $cartItemFactory,
        CartRepository $cartRepository,
        CartWatcherFacade $cartWatcherFacade,
        private ProductAvailabilityFacade $productAvailabilityFacade,
    ) {
        parent::__construct(
            $em,
            $cartFactory,
            $productRepository,
            $customerUserIdentifierFactory,
            $domain,
            $currentCustomerUser,
            $currentPromoCodeFacade,
            $productPriceCalculation,
            $cartItemFactory,
            $cartRepository,
            $cartWatcherFacade,
        );
    }

    /**
     * @param int $productId
     * @param int $quantity
     * @return \App\Model\Cart\AddProductResult
     */
    public function addProductToCart($productId, $quantity): AddProductResult
    {
        $product = $this->productRepository->getSellableById(
            $productId,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );
        $cart = $this->getCartOfCurrentCustomerUserCreateIfNotExists();

        return $this->addProductToExistingCart($product, $quantity, $cart);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $quantity
     * @param \App\Model\Cart\Cart $cart
     * @param bool $isAbsoluteQuantity
     * @return \App\Model\Cart\AddProductResult
     */
    public function addProductToExistingCart(
        Product $product,
        $quantity,
        BaseCart $cart,
        bool $isAbsoluteQuantity = false,
    ): AddProductResult {
        $maximumOrderQuantity = $this->productAvailabilityFacade->getMaximumOrderQuantity($product, $this->domain->getId());
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
        /** @var \App\Model\Cart\Item\CartItem $newCartItem */
        $newCartItem = $this->cartItemFactory->create($cart, $product, $quantity, $productPrice->getPriceWithVat());
        $cart->addItem($newCartItem);
        $cart->setModifiedNow();

        $result = new AddProductResult($newCartItem, true, $quantity, $notOnStockQuantity);

        $this->em->persist($result->getCartItem());
        $this->em->flush();

        return $result;
    }

    /**
     * @param string $cartItemUuid
     * @param \App\Model\Cart\Cart $cart
     * @return \App\Model\Cart\Cart
     */
    public function removeItemFromExistingCartByUuid(string $cartItemUuid, BaseCart $cart): BaseCart
    {
        $cartItemToRemove = $cart->getItemByUuid($cartItemUuid);

        $cart->removeItemById($cartItemToRemove->getId());

        $this->em->remove($cartItemToRemove);
        $this->em->flush();

        return $cart;
    }

    /**
     * @param string $cartIdentifier
     * @return \App\Model\Cart\Cart|null
     */
    public function findCartByCartIdentifier(string $cartIdentifier): ?BaseCart
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCartIdentifier($cartIdentifier);

        /** @var \App\Model\Cart\Cart|null $cart */
        $cart = $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);

        return $cart;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \App\Model\Cart\Cart|null
     */
    public function findCartByCustomerUserIdentifier(CustomerUserIdentifier $customerUserIdentifier): ?BaseCart
    {
        /** @var \App\Model\Cart\Cart $cart */
        $cart = $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);

        return $cart;
    }

    /**
     * @param int $cartItemId
     * @param \App\Model\Cart\Cart|null $cart
     */
    public function deleteCartItem($cartItemId, ?BaseCart $cart = null)
    {
        if (!$cart) {
            $cart = $this->findCartOfCurrentCustomerUser();
        }

        if ($cart === null) {
            return;
        }

        $cartItemToDelete = $cart->getItemById($cartItemId);
        $cart->removeItemById($cartItemId);
        $this->em->remove($cartItemToDelete);
        $this->em->flush();

        if ($cart->isEmpty()) {
            $this->deleteCart($cart);
        }
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    public function deleteCart(BaseCart $cart)
    {
        foreach ($cart->getItems() as $item) {
            $this->em->remove($item);
        }

        $cart->clean();
        $this->em->remove($cart);
        $this->em->flush();
    }

    /**
     * @param string $cartUuid
     * @return \App\Model\Cart\Cart
     */
    public function createCart(string $cartUuid): Cart
    {
        $cart = new Cart($cartUuid, null);

        $this->em->persist($cart);
        $this->em->flush();

        return $cart;
    }
}
