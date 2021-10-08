<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\Model\Cart\Cart;
use App\Model\Cart\Item\CartItem;
use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
use LogicException;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class CartWithModificationsResult
{
    /**
     * @var \App\Model\Cart\Cart
     */
    protected Cart $cart;

    /**
     * @var array<string, array>
     */
    private array $cartModifications = [
        'itemModifications' => [],
        'transportModifications' => [],
        'paymentModifications' => [],
    ];

    /**
     * @var array<string, array<int, \App\Model\Cart\Item\CartItem>>
     */
    private array $itemModifications = [
        'noLongerListableCartItems' => [],
        'cartItemsWithModifiedPrice' => [],
        'cartItemsWithChangedQuantity' => [],
        'noLongerAvailableCartItemsDueToQuantity' => [],
    ];

    /**
     * @var array
     */
    private array $transportModifications = [
        'transportPriceChanged' => false,
        'transportUnavailable' => false,
        'transportWeightLimitExceeded' => false,
    ];

    /**
     * @var array
     */
    private array $paymentModifications = [
        'paymentPriceChanged' => false,
        'paymentUnavailable' => false,
    ];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private ?Price $totalPrice = null;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private ?Price $totalDiscountPrice = null;

    /**
     * @var \App\Model\Transport\Transport|null
     */
    private ?Transport $transport;

    /**
     * @var \App\Model\Payment\Payment|null
     */
    private ?Payment $payment;

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->cart->getCartIdentifier();
    }

    /**
     * @return \App\Model\Cart\Item\CartItem[]
     */
    public function getItems(): array
    {
        return $this->cart->getItems();
    }

    /**
     * @return array<string, array>
     */
    public function getModifications(): array
    {
        $this->cartModifications['itemModifications'] = $this->itemModifications;
        $this->cartModifications['transportModifications'] = $this->transportModifications;
        $this->cartModifications['paymentModifications'] = $this->paymentModifications;
        return $this->cartModifications;
    }

    /**
     * @param \App\Model\Cart\Item\CartItem $cartItem
     */
    public function addNoLongerListableCartItem(CartItem $cartItem): void
    {
        $this->itemModifications['noLongerListableCartItems'][] = $cartItem;
    }

    /**
     * @param \App\Model\Cart\Item\CartItem $cartItem
     */
    public function addCartItemWithModifiedPrice(CartItem $cartItem): void
    {
        $this->itemModifications['cartItemsWithModifiedPrice'][] = $cartItem;
    }

    /**
     * @param \App\Model\Cart\Item\CartItem $cartItem
     */
    public function addCartItemWithChangedQuantity(CartItem $cartItem): void
    {
        $this->itemModifications['cartItemsWithChangedQuantity'][] = $cartItem;
    }

    /**
     * @param \App\Model\Cart\Item\CartItem $cartItem
     */
    public function addNoLongerAvailableCartItemDueToQuantity(CartItem $cartItem): void
    {
        $this->itemModifications['noLongerAvailableCartItemsDueToQuantity'][] = $cartItem;
    }

    /**
     * @param bool $transportPriceChanged
     */
    public function setTransportPriceChanged(bool $transportPriceChanged): void
    {
        $this->transportModifications['transportPriceChanged'] = $transportPriceChanged;
    }

    public function setTransportIsUnavailable(): void
    {
        $this->transportModifications['transportUnavailable'] = true;
    }

    /**
     * @param bool $transportWeightLimitExceeded
     */
    public function setTransportWeightLimitExceeded(bool $transportWeightLimitExceeded): void
    {
        $this->transportModifications['transportWeightLimitExceeded'] = $transportWeightLimitExceeded;
    }

    /**
     * @param bool $paymentPriceChanged
     */
    public function setPaymentPriceChanged(bool $paymentPriceChanged): void
    {
        $this->paymentModifications['paymentPriceChanged'] = $paymentPriceChanged;
    }

    public function setPaymentIsUnavailable(): void
    {
        $this->paymentModifications['paymentUnavailable'] = true;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPrice(): Price
    {
        if (!$this->totalPrice) {
            throw new LogicException('Total price must me set before calling the getter.');
        }

        return $this->totalPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPrice
     */
    public function setTotalPrice(Price $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalDiscountPrice(): Price
    {
        if (!$this->totalDiscountPrice) {
            throw new LogicException('Total discount price must me set before calling the getter.');
        }
        return $this->totalDiscountPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalDiscountPrice
     */
    public function setTotalDiscountPrice(Price $totalDiscountPrice): void
    {
        $this->totalDiscountPrice = $totalDiscountPrice;
    }

    /**
     * @return \App\Model\Transport\Transport|null
     */
    public function getTransport(): ?Transport
    {
        return $this->transport;
    }

    /**
     * @return \App\Model\Payment\Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * @param \App\Model\Transport\Transport|null $transport
     */
    public function setTransport(?Transport $transport): void
    {
        $this->transport = $transport;
    }

    /**
     * @param \App\Model\Payment\Payment|null $payment
     */
    public function setPayment(?Payment $payment): void
    {
        $this->payment = $payment;
    }
}
