<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\Model\Cart\Cart;
use App\Model\Cart\Item\CartItem;
use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
use LogicException;
use Shopsys\FrameworkBundle\Component\Money\Money;
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
        'promoCodeModifications' => [],
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
        'personalPickupStoreUnavailable' => false,
    ];

    /**
     * @var array
     */
    private array $paymentModifications = [
        'paymentPriceChanged' => false,
        'paymentUnavailable' => false,
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private array $promoCodeModifications = [
        'noLongerApplicablePromoCode' => [],
    ];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private ?Price $totalPrice = null;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private ?Price $totalItemsPrice = null;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private ?Price $totalDiscountPrice = null;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    private ?Money $remainingAmountWithVatForFreeTransport = null;

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->cart->getCartIdentifier() !== '' ? $this->cart->getCartIdentifier() : null;
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
        $this->cartModifications['promoCodeModifications'] = $this->promoCodeModifications;

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
     * @param bool $personalPickupStoreUnavailable
     */
    public function setPersonalPickupStoreUnavailable(bool $personalPickupStoreUnavailable): void
    {
        $this->transportModifications['personalPickupStoreUnavailable'] = $personalPickupStoreUnavailable;
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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalItemsPrice(): Price
    {
        if (!$this->totalItemsPrice) {
            throw new LogicException('Total items price must me set before calling the getter.');
        }

        return $this->totalItemsPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPrice
     */
    public function setTotalPrice(Price $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalItemsPrice
     */
    public function setTotalItemsPrice(Price $totalItemsPrice): void
    {
        $this->totalItemsPrice = $totalItemsPrice;
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
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getRemainingAmountWithVatForFreeTransport(): ?Money
    {
        return $this->remainingAmountWithVatForFreeTransport;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $remainingAmountWithVatForFreeTransport
     */
    public function setRemainingAmountWithVatForFreeTransport(Money $remainingAmountWithVatForFreeTransport): void
    {
        $this->remainingAmountWithVatForFreeTransport = $remainingAmountWithVatForFreeTransport;
    }

    /**
     * @return \App\Model\Transport\Transport|null
     */
    public function getTransport(): ?Transport
    {
        return $this->cart->getTransport();
    }

    /**
     * @return \App\Model\Payment\Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->cart->getPayment();
    }

    /**
     * @return string|null
     */
    public function getPromoCode(): ?string
    {
        if ($this->cart->getFirstAppliedPromoCode() === null) {
            return null;
        }

        return $this->cart->getFirstAppliedPromoCode()->getCode();
    }

    /**
     * @return string|null
     */
    public function getSelectedPickupPlaceIdentifier(): ?string
    {
        return $this->cart->getPickupPlaceIdentifier();
    }

    /**
     * @return string|null
     */
    public function getPaymentGoPayBankSwift(): ?string
    {
        return $this->cart->getPaymentGoPayBankSwift();
    }

    /**
     * @param string $promoCode
     */
    public function addChangedPromoCode(string $promoCode): void
    {
        $this->promoCodeModifications['noLongerApplicablePromoCode'][] = $promoCode;
    }
}
