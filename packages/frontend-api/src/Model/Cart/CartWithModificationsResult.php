<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use LogicException;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class CartWithModificationsResult
{
    /**
     * @var array<string, array|bool>
     */
    protected array $cartModifications = [
        'itemModifications' => [],
        'transportModifications' => [],
        'paymentModifications' => [],
        'promoCodeModifications' => [],
        'someProductWasRemovedFromEshop' => false,
    ];

    /**
     * @var array<string, array<int, \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem>>
     */
    protected array $itemModifications = [
        'noLongerListableCartItems' => [],
        'cartItemsWithModifiedPrice' => [],
        'cartItemsWithChangedQuantity' => [],
    ];

    /**
     * @var array<string, bool>
     */
    protected array $transportModifications = [
        'transportPriceChanged' => false,
        'transportUnavailable' => false,
        'transportWeightLimitExceeded' => false,
        'personalPickupStoreUnavailable' => false,
    ];

    /**
     * @var array<string, bool>
     */
    protected array $paymentModifications = [
        'paymentPriceChanged' => false,
        'paymentUnavailable' => false,
    ];

    /**
     * @var array<string, array<int, string>>
     */
    protected array $promoCodeModifications = [
        'noLongerApplicablePromoCode' => [],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    protected array $multipleAddedProductModifications = [
        'notAddedProducts' => [],
    ];

    protected ?PriceInterface $totalPrice = null;

    protected ?PriceInterface $totalItemsPrice = null;

    protected ?PriceInterface $totalItemsPriceBeforeDiscount = null;

    protected ?PriceInterface $totalProductPriceAdjustmentsDiscount = null;

    protected ?PriceInterface $totalDiscountPrice = null;

    protected ?Money $remainingAmountForFreeTransport = null;

    protected ?PriceInterface $roundingPrice = null;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    protected array $promoCodes = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function __construct(protected Cart $cart)
    {
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->cart->getCartIdentifier() !== '' ? $this->cart->getCartIdentifier() : null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
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
        $this->cartModifications['multipleAddedProductModifications'] = $this->multipleAddedProductModifications;

        return $this->cartModifications;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     */
    public function addNoLongerListableCartItem(CartItem $cartItem): void
    {
        $this->itemModifications['noLongerListableCartItems'][] = $cartItem;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     */
    public function addCartItemWithModifiedPrice(CartItem $cartItem): void
    {
        $this->itemModifications['cartItemsWithModifiedPrice'][] = $cartItem;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     */
    public function addCartItemWithChangedQuantity(CartItem $cartItem): void
    {
        $this->itemModifications['cartItemsWithChangedQuantity'][] = $cartItem;
    }

    public function setCartHasRemovedProducts(): void
    {
        $this->cartModifications['someProductWasRemovedFromEshop'] = true;
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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getTotalPrice(): PriceInterface
    {
        if (!$this->totalPrice) {
            throw new LogicException('Total price must be set before calling the getter.');
        }

        return $this->totalPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getTotalItemsPrice(): PriceInterface
    {
        if (!$this->totalItemsPrice) {
            throw new LogicException('Total items price must be set before calling the getter.');
        }

        return $this->totalItemsPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $totalPrice
     */
    public function setTotalPrice(PriceInterface $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $totalItemsPrice
     */
    public function setTotalItemsPrice(PriceInterface $totalItemsPrice): void
    {
        $this->totalItemsPrice = $totalItemsPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getTotalItemsPriceBeforeDiscount(): PriceInterface
    {
        if (!$this->totalItemsPriceBeforeDiscount) {
            throw new LogicException('Total items price before discount must be set before calling the getter.');
        }

        return $this->totalItemsPriceBeforeDiscount;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $totalItemsPriceBeforeDiscount
     */
    public function setTotalItemsPriceBeforeDiscount(PriceInterface $totalItemsPriceBeforeDiscount): void
    {
        $this->totalItemsPriceBeforeDiscount = $totalItemsPriceBeforeDiscount;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getTotalProductPriceAdjustmentsDiscount(): PriceInterface
    {
        if (!$this->totalProductPriceAdjustmentsDiscount) {
            throw new LogicException('Total product discount price must be set before calling the getter.');
        }

        return $this->totalProductPriceAdjustmentsDiscount;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $totalProductPriceAdjustmentsDiscount
     */
    public function setTotalProductPriceAdjustmentsDiscount(PriceInterface $totalProductPriceAdjustmentsDiscount): void
    {
        $this->totalProductPriceAdjustmentsDiscount = $totalProductPriceAdjustmentsDiscount;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getTotalDiscountPrice(): PriceInterface
    {
        if (!$this->totalDiscountPrice) {
            throw new LogicException('Total discount price must be set before calling the getter.');
        }

        return $this->totalDiscountPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $totalDiscountPrice
     */
    public function setTotalDiscountPrice(PriceInterface $totalDiscountPrice): void
    {
        $this->totalDiscountPrice = $totalDiscountPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getRemainingAmountForFreeTransport(): ?Money
    {
        return $this->remainingAmountForFreeTransport;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $remainingAmountForFreeTransport
     */
    public function setRemainingAmountForFreeTransport(Money $remainingAmountForFreeTransport): void
    {
        $this->remainingAmountForFreeTransport = $remainingAmountForFreeTransport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport|null
     */
    public function getTransport(): ?Transport
    {
        return $this->cart->getTransport();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->cart->getPayment();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    public function getPromoCode(): ?PromoCode
    {
        return $this->cart->getFirstAppliedPromoCode();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    public function getPromoCodes(): array
    {
        return $this->promoCodes;
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

    /**
     * @return bool
     */
    public function isCartModified(): bool
    {
        return $this->isTransportInCartModified()
            || $this->isPaymentInCartModified()
            || $this->isPromoCodeInCartValid()
            || $this->isSomeCartItemModified()
            || $this->cartModifications['someProductWasRemovedFromEshop'];
    }

    /**
     * @return bool
     */
    protected function isPaymentInCartModified(): bool
    {
        return $this->paymentModifications['paymentPriceChanged']
            || $this->paymentModifications['paymentUnavailable'];
    }

    /**
     * @return bool
     */
    protected function isTransportInCartModified(): bool
    {
        return $this->transportModifications['transportPriceChanged']
            || $this->transportModifications['transportUnavailable']
            || $this->transportModifications['transportWeightLimitExceeded']
            || $this->transportModifications['personalPickupStoreUnavailable'];
    }

    /**
     * @return bool
     */
    protected function isPromoCodeInCartValid(): bool
    {
        return count($this->promoCodeModifications['noLongerApplicablePromoCode']) > 0;
    }

    /**
     * @return bool
     */
    protected function isSomeCartItemModified(): bool
    {
        return count($this->itemModifications['noLongerListableCartItems']) > 0
            || count($this->itemModifications['cartItemsWithModifiedPrice']) > 0
            || count($this->itemModifications['cartItemsWithChangedQuantity']) > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function addProductsNotAddedByMultipleAddition(array $products): void
    {
        foreach ($products as $product) {
            $this->multipleAddedProductModifications['notAddedProducts'][] = $product;
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface|null
     */
    public function getRoundingPrice(): ?PriceInterface
    {
        return $this->roundingPrice->isZero() ? null : $this->roundingPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface|null $roundingPrice
     */
    public function setRoundingPrice(?PriceInterface $roundingPrice): void
    {
        $this->roundingPrice = $roundingPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $promoCodeDiscountPrice
     */
    public function addPromoCode(PromoCode $promoCode, PriceInterface $promoCodeDiscountPrice): void
    {
        $this->promoCodes[] = new PromoCodeQueryDto(
            $promoCode->getCode(),
            $promoCode->getDiscountType(),
            $promoCodeDiscountPrice,
        );
    }
}
