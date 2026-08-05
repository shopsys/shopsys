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
    protected array $giftVoucherModifications = [
        'noLongerApplicableGiftVouchers' => [],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    protected array $multipleAddedProductModifications = [
        'notAddedProducts' => [],
    ];

    protected ?PriceInterface $totalPrice = null;

    protected Money $giftVoucherProductItemsPriceWithVat;

    protected ?PriceInterface $totalItemsPrice = null;

    protected ?PriceInterface $totalItemsPriceBeforeDiscount = null;

    protected ?PriceInterface $totalProductPriceAdjustmentsDiscount = null;

    protected ?PriceInterface $totalDiscountPrice = null;

    protected ?Money $remainingAmountForFreeTransport = null;

    protected ?Money $remainingAmountToPay = null;

    protected ?Money $remainingItemsAmountToPay = null;

    protected ?PriceInterface $roundingPrice = null;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Cart\PromoCodeQueryDto[]
     */
    protected array $promoCodes = [];

    public function __construct(protected Cart $cart)
    {
    }

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
        $this->cartModifications['giftVoucherModifications'] = $this->giftVoucherModifications;
        $this->cartModifications['multipleAddedProductModifications'] = $this->multipleAddedProductModifications;

        return $this->cartModifications;
    }

    public function addNoLongerListableCartItem(CartItem $cartItem): void
    {
        $this->itemModifications['noLongerListableCartItems'][] = $cartItem;
    }

    public function addCartItemWithModifiedPrice(CartItem $cartItem): void
    {
        $this->itemModifications['cartItemsWithModifiedPrice'][] = $cartItem;
    }

    public function addCartItemWithChangedQuantity(CartItem $cartItem): void
    {
        $this->itemModifications['cartItemsWithChangedQuantity'][] = $cartItem;
    }

    public function setCartHasRemovedProducts(): void
    {
        $this->cartModifications['someProductWasRemovedFromEshop'] = true;
    }

    public function setTransportPriceChanged(bool $transportPriceChanged): void
    {
        $this->transportModifications['transportPriceChanged'] = $transportPriceChanged;
    }

    public function setTransportIsUnavailable(): void
    {
        $this->transportModifications['transportUnavailable'] = true;
    }

    public function setTransportWeightLimitExceeded(bool $transportWeightLimitExceeded): void
    {
        $this->transportModifications['transportWeightLimitExceeded'] = $transportWeightLimitExceeded;
    }

    public function setPersonalPickupStoreUnavailable(bool $personalPickupStoreUnavailable): void
    {
        $this->transportModifications['personalPickupStoreUnavailable'] = $personalPickupStoreUnavailable;
    }

    public function setPaymentPriceChanged(bool $paymentPriceChanged): void
    {
        $this->paymentModifications['paymentPriceChanged'] = $paymentPriceChanged;
    }

    public function setPaymentIsUnavailable(): void
    {
        $this->paymentModifications['paymentUnavailable'] = true;
    }

    public function getTotalPrice(): PriceInterface
    {
        if (!$this->totalPrice) {
            throw new LogicException('Total price must be set before calling the getter.');
        }

        return $this->totalPrice;
    }

    public function getTotalItemsPrice(): PriceInterface
    {
        if (!$this->totalItemsPrice) {
            throw new LogicException('Total items price must be set before calling the getter.');
        }

        return $this->totalItemsPrice;
    }

    public function setTotalPrice(PriceInterface $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    public function setTotalItemsPrice(PriceInterface $totalItemsPrice): void
    {
        $this->totalItemsPrice = $totalItemsPrice;
    }

    public function getTotalItemsPriceBeforeDiscount(): PriceInterface
    {
        if (!$this->totalItemsPriceBeforeDiscount) {
            throw new LogicException('Total items price before discount must be set before calling the getter.');
        }

        return $this->totalItemsPriceBeforeDiscount;
    }

    public function setTotalItemsPriceBeforeDiscount(PriceInterface $totalItemsPriceBeforeDiscount): void
    {
        $this->totalItemsPriceBeforeDiscount = $totalItemsPriceBeforeDiscount;
    }

    public function getTotalProductPriceAdjustmentsDiscount(): PriceInterface
    {
        if (!$this->totalProductPriceAdjustmentsDiscount) {
            throw new LogicException('Total product discount price must be set before calling the getter.');
        }

        return $this->totalProductPriceAdjustmentsDiscount;
    }

    public function setTotalProductPriceAdjustmentsDiscount(PriceInterface $totalProductPriceAdjustmentsDiscount): void
    {
        $this->totalProductPriceAdjustmentsDiscount = $totalProductPriceAdjustmentsDiscount;
    }

    public function getTotalDiscountPrice(): PriceInterface
    {
        if (!$this->totalDiscountPrice) {
            throw new LogicException('Total discount price must be set before calling the getter.');
        }

        return $this->totalDiscountPrice;
    }

    public function setTotalDiscountPrice(PriceInterface $totalDiscountPrice): void
    {
        $this->totalDiscountPrice = $totalDiscountPrice;
    }

    public function getRemainingAmountForFreeTransport(): ?Money
    {
        return $this->remainingAmountForFreeTransport;
    }

    public function setRemainingAmountForFreeTransport(Money $remainingAmountForFreeTransport): void
    {
        $this->remainingAmountForFreeTransport = $remainingAmountForFreeTransport;
    }

    public function getTransport(): ?Transport
    {
        return $this->cart->getTransport();
    }

    public function getPayment(): ?Payment
    {
        return $this->cart->getPayment();
    }

    public function getPromoCode(): ?PromoCode
    {
        return $this->cart->getFirstAppliedPromoCode();
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Cart\PromoCodeQueryDto[]
     */
    public function getPromoCodes(): array
    {
        return $this->promoCodes;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[]
     */
    public function getGiftVouchers(): array
    {
        return $this->cart->getAllAppliedGiftVouchers();
    }

    public function getRemainingAmountToPay(): Money
    {
        if ($this->remainingAmountToPay === null) {
            throw new LogicException('Remaining to pay must be set before calling the getter.');
        }

        return $this->remainingAmountToPay;
    }

    public function setRemainingAmountToPay(Money $remainingAmountToPay): void
    {
        $this->remainingAmountToPay = $remainingAmountToPay;
    }

    public function getRemainingItemsAmountToPay(): Money
    {
        if ($this->remainingItemsAmountToPay === null) {
            throw new LogicException('Remaining items amount to pay must be set before calling the getter.');
        }

        return $this->remainingItemsAmountToPay;
    }

    public function setRemainingItemsAmountToPay(Money $remainingItemsAmountToPay): void
    {
        $this->remainingItemsAmountToPay = $remainingItemsAmountToPay;
    }

    public function isNothingLeftToPay(): bool
    {
        return $this->getRemainingAmountToPay()->isZero();
    }

    public function getGiftVouchersExceedPayableAmount(): bool
    {
        $giftVouchersTotalValue = $this->getAppliedGiftVouchersTotalValueWithVat();

        if ($giftVouchersTotalValue->isZero()) {
            return false;
        }

        $payableAmount = $this->getTotalPrice()->getPriceWithVat()
            ->subtract($this->getGiftVoucherProductItemsPriceWithVat());

        return $giftVouchersTotalValue->isGreaterThan($payableAmount);
    }

    protected function getAppliedGiftVouchersTotalValueWithVat(): Money
    {
        $giftVouchersTotalValue = Money::zero();

        foreach ($this->cart->getAllAppliedGiftVouchers() as $giftVoucher) {
            $giftVouchersTotalValue = $giftVouchersTotalValue->add($giftVoucher->getValueWithVat());
        }

        return $giftVouchersTotalValue;
    }

    public function getGiftVoucherProductItemsPriceWithVat(): Money
    {
        if (!isset($this->giftVoucherProductItemsPriceWithVat)) {
            throw new LogicException('Gift voucher product items price must be set before calling the getter.');
        }

        return $this->giftVoucherProductItemsPriceWithVat;
    }

    public function setGiftVoucherProductItemsPriceWithVat(Money $giftVoucherProductItemsPriceWithVat): void
    {
        $this->giftVoucherProductItemsPriceWithVat = $giftVoucherProductItemsPriceWithVat;
    }

    public function getSelectedPickupPlaceIdentifier(): ?string
    {
        return $this->cart->getPickupPlaceIdentifier();
    }

    public function getPaymentGoPayBankSwift(): ?string
    {
        return $this->cart->getPaymentGoPayBankSwift();
    }

    public function addChangedPromoCode(string $promoCode): void
    {
        $this->promoCodeModifications['noLongerApplicablePromoCode'][] = $promoCode;
    }

    public function addChangedGiftVoucher(string $giftVoucherCode): void
    {
        $this->giftVoucherModifications['noLongerApplicableGiftVouchers'][] = $giftVoucherCode;
    }

    public function isCartModified(): bool
    {
        return $this->isTransportInCartModified()
            || $this->isPaymentInCartModified()
            || $this->isPromoCodeInCartValid()
            || $this->isGiftVoucherInCartModified()
            || $this->isSomeCartItemModified()
            || $this->cartModifications['someProductWasRemovedFromEshop'];
    }

    protected function isPaymentInCartModified(): bool
    {
        return $this->paymentModifications['paymentPriceChanged']
            || $this->paymentModifications['paymentUnavailable'];
    }

    protected function isTransportInCartModified(): bool
    {
        return $this->transportModifications['transportPriceChanged']
            || $this->transportModifications['transportUnavailable']
            || $this->transportModifications['transportWeightLimitExceeded']
            || $this->transportModifications['personalPickupStoreUnavailable'];
    }

    protected function isPromoCodeInCartValid(): bool
    {
        return count($this->promoCodeModifications['noLongerApplicablePromoCode']) > 0;
    }

    protected function isGiftVoucherInCartModified(): bool
    {
        return count($this->giftVoucherModifications['noLongerApplicableGiftVouchers']) > 0;
    }

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

    public function getRoundingPrice(): ?PriceInterface
    {
        return $this->roundingPrice->isZero() ? null : $this->roundingPrice;
    }

    public function setRoundingPrice(?PriceInterface $roundingPrice): void
    {
        $this->roundingPrice = $roundingPrice;
    }

    public function addPromoCode(PromoCode $promoCode, PriceInterface $promoCodeDiscountPrice): void
    {
        $this->promoCodes[] = new PromoCodeQueryDto(
            $promoCode->getCode(),
            $promoCode->getDiscountType(),
            $promoCodeDiscountPrice,
        );
    }
}
