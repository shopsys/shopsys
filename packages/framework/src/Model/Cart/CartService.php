<?php

namespace Shopsys\FrameworkBundle\Model\Cart;

use DateTime;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser;
use Shopsys\FrameworkBundle\Model\Product\Product;

class CartService
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser
     */
    private $productPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface
     */
    protected $cartItemFactory;

    public function __construct(
        ProductPriceCalculationForUser $productPriceCalculation,
        CartItemFactoryInterface $cartItemFactory
    ) {
        $this->productPriceCalculation = $productPriceCalculation;
        $this->cartItemFactory = $cartItemFactory;
    }

    public function addProductToCart(Cart $cart, CustomerIdentifier $customerIdentifier, Product $product, int $quantity): \Shopsys\FrameworkBundle\Model\Cart\AddProductResult
    {
        if (!is_int($quantity) || $quantity <= 0) {
            throw new \Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException($quantity);
        }

        foreach ($cart->getItems() as $cartItem) {
            if ($cartItem->getProduct() === $product) {
                $cartItem->changeQuantity($cartItem->getQuantity() + $quantity);
                $cartItem->changeAddedAt(new DateTime());
                return new AddProductResult($cartItem, false, $quantity);
            }
        }

        $productPrice = $this->productPriceCalculation->calculatePriceForCurrentUser($product);
        $newCartItem = $this->cartItemFactory->create($customerIdentifier, $product, $quantity, $productPrice->getPriceWithVat());
        $cart->addItem($newCartItem);
        return new AddProductResult($newCartItem, true, $quantity);
    }

    public function changeQuantities(Cart $cart, array $quantitiesByCartItemId): void
    {
        foreach ($cart->getItems() as $cartItem) {
            if (array_key_exists($cartItem->getId(), $quantitiesByCartItemId)) {
                $cartItem->changeQuantity($quantitiesByCartItemId[$cartItem->getId()]);
            }
        }
    }

    public function getCartItemById(Cart $cart, int $cartItemId): \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
    {
        foreach ($cart->getItems() as $cartItem) {
            if ($cartItem->getId() === $cartItemId) {
                return $cartItem;
            }
        }
        $message = 'CartItem with id = ' . $cartItemId . ' not found in cart.';
        throw new \Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException($message);
    }

    public function cleanCart(Cart $cart): void
    {
        $cart->clean();
    }

    public function mergeCarts(Cart $resultingCart, Cart $mergedCart, CustomerIdentifier $customerIdentifier): void
    {
        foreach ($mergedCart->getItems() as $cartItem) {
            $similarCartItem = $this->findSimilarCartItemByCartItem($resultingCart, $cartItem);
            if ($similarCartItem instanceof CartItem) {
                $similarCartItem->changeQuantity($cartItem->getQuantity());
            } else {
                $newCartItem = $this->cartItemFactory->create(
                    $customerIdentifier,
                    $cartItem->getProduct(),
                    $cartItem->getQuantity(),
                    $cartItem->getWatchedPrice()
                );
                $resultingCart->addItem($newCartItem);
            }
        }
    }

    private function findSimilarCartItemByCartItem(Cart $cart, CartItem $cartItem): ?\Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
    {
        foreach ($cart->getItems() as $similarCartItem) {
            if ($similarCartItem->isSimilarItemAs($cartItem)) {
                return $similarCartItem;
            }
        }

        return null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    public function getQuantifiedProductsIndexedByCartItemId(Cart $cart): array
    {
        $quantifiedProductsByCartItemId = [];
        foreach ($cart->getItems() as $cartItem) {
            $quantifiedProductsByCartItemId[$cartItem->getId()] = new QuantifiedProduct($cartItem->getProduct(), $cartItem->getQuantity());
        }

        return $quantifiedProductsByCartItemId;
    }
}
