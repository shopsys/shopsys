<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentFacade;
use Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreByUuidNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;
use Shopsys\FrontendApiBundle\Model\Order\Exception\InvalidPacketeryAddressIdUserError;
use Shopsys\FrontendApiBundle\Model\Payment\Exception\PaymentPriceChangedException;
use Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\TransportPriceChangedException;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\TransportUnavailableForProductsInCartException;
use Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade;

class TransportAndPaymentWatcherFacade
{
    protected CartWithModificationsResult $cartWithModificationsResult;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportFacade $cartTransportFacade
     * @param \Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade $transportValidationFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentFacade $cartPaymentFacade
     * @param \Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade $paymentValidationFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        protected readonly TransportFacade $transportFacade,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly Domain $domain,
        protected readonly FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade,
        protected readonly CartTransportFacade $cartTransportFacade,
        protected readonly TransportValidationFacade $transportValidationFacade,
        protected readonly CartPaymentFacade $cartPaymentFacade,
        protected readonly PaymentValidationFacade $paymentValidationFacade,
        protected readonly OrderFacade $orderFacade,
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult $cartWithModificationsResult
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function checkTransportAndPayment(
        CartWithModificationsResult $cartWithModificationsResult,
        Cart $cart,
    ): void {
        $this->cartWithModificationsResult = $cartWithModificationsResult;
        $this->checkTransport($cart);

        $domainId = $this->domain->getId();

        $orderData = $this->orderFacade->createOrderDataFromCart($cart, $this->domain->getCurrentDomainConfig());

        $productsPrice = $orderData->getProductsTotalPriceAfterAppliedDiscounts();

        $isFreeTransportAndPaymentPromoCodeApplied = $orderData->freeTransportAndPaymentApplied;

        if ($this->freeTransportAndPaymentFacade->isActive($domainId, $isFreeTransportAndPaymentPromoCodeApplied)) {
            $amountForFreeTransport = $this->freeTransportAndPaymentFacade->getRemainingAmount(
                $productsPrice,
                $domainId,
                $isFreeTransportAndPaymentPromoCodeApplied,
            );

            $this->cartWithModificationsResult->setRemainingAmountForFreeTransport($amountForFreeTransport);
        }

        // Calculate promo code discounts (TYPE_DISCOUNT + TYPE_PROMOTION)
        // These come as negative values from OrderData, so we inverse them to get positive discount amounts
        $promoCodeDiscountPrice = $orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_DISCOUNT];
        $promoCodeDiscountPrice = $promoCodeDiscountPrice->add($orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_PROMOTION]);
        $promoCodeDiscountPrice = $promoCodeDiscountPrice->inverse();

        // Calculate product-level discounts (difference between basic price and selling price)
        // This already returns positive values
        $productDiscountPrice = $this->calculateTotalProductDiscounts($cart);

        // Calculate total discount (promo codes + product discounts)
        $totalDiscountPrice = $promoCodeDiscountPrice->add($productDiscountPrice);

        // Calculate total items price before any discounts
        $totalItemsPriceBeforeDiscount = $this->calculateTotalItemsPriceBeforeDiscount($cart);

        $this->cartWithModificationsResult->setTotalPrice($orderData->totalPrice);
        $this->cartWithModificationsResult->setTotalItemsPrice($productsPrice);
        $this->cartWithModificationsResult->setTotalItemsPriceBeforeDiscount($totalItemsPriceBeforeDiscount);
        $this->cartWithModificationsResult->setTotalProductDiscountPrice($productDiscountPrice);
        $this->cartWithModificationsResult->setTotalPromoCodeDiscountPrice($promoCodeDiscountPrice);
        $this->cartWithModificationsResult->setTotalDiscountPrice($totalDiscountPrice);
        $this->cartWithModificationsResult->setTotalPriceWithoutDiscountTransportAndPayment(
            $orderData->getTotalPriceWithoutDiscountTransportAndPayment(),
        );
        $this->cartWithModificationsResult->setRoundingPrice($orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_ROUNDING]);

        $this->checkPayment($cart);
    }

    /**
     * Calculate total items price before any discounts (sum of basicPrice * quantity for all products)
     *
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function calculateTotalItemsPriceBeforeDiscount(Cart $cart): Price
    {
        $totalPrice = Price::zero();
        $domainId = $this->domain->getId();
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        foreach ($cart->getProductCartItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $quantity = $cartItem->getQuantity();

            // Calculate basic price (without special price)
            $basicProductPrice = $this->productPriceCalculationForCustomerUser->calculateBasicPriceForCustomerUserAndDomainId(
                $product,
                $domainId,
                $customerUser,
            );

            $basicPriceWithVat = $basicProductPrice->getPrice()->getPriceWithVat();
            $basicPriceWithoutVat = $basicProductPrice->getPrice()->getPriceWithoutVat();

            $totalPriceWithVat = $basicPriceWithVat->multiply($quantity);
            $totalPriceWithoutVat = $basicPriceWithoutVat->multiply($quantity);

            $itemPrice = new Price($totalPriceWithoutVat, $totalPriceWithVat);
            $totalPrice = $totalPrice->add($itemPrice);
        }

        return $totalPrice;
    }

    /**
     * Calculate total discount from product prices (basicPrice - sellingPrice) across all cart items
     *
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function calculateTotalProductDiscounts(Cart $cart): Price
    {
        $totalDiscount = Price::zero();
        $domainId = $this->domain->getId();
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        foreach ($cart->getProductCartItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $quantity = $cartItem->getQuantity();

            // Calculate basic price (without special price)
            $basicProductPrice = $this->productPriceCalculationForCustomerUser->calculateBasicPriceForCustomerUserAndDomainId(
                $product,
                $domainId,
                $customerUser,
            );

            // Calculate actual selling price (with special price if applicable)
            $sellingProductPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId(
                $product,
                $domainId,
                $customerUser,
            );

            $basicPriceWithVat = $basicProductPrice->getPrice()->getPriceWithVat();
            $sellingPriceWithVat = $sellingProductPrice->getPrice()->getPriceWithVat();

            // Calculate discount only if selling price is lower than basic price
            if (!$sellingPriceWithVat->isLessThan($basicPriceWithVat)) {
                continue;
            }

            $discountPerUnit = $basicPriceWithVat->subtract($sellingPriceWithVat);
            $discountTotal = $discountPerUnit->multiply($quantity);

            $basicPriceWithoutVat = $basicProductPrice->getPrice()->getPriceWithoutVat();
            $sellingPriceWithoutVat = $sellingProductPrice->getPrice()->getPriceWithoutVat();
            $discountPerUnitWithoutVat = $basicPriceWithoutVat->subtract($sellingPriceWithoutVat);
            $discountTotalWithoutVat = $discountPerUnitWithoutVat->multiply($quantity);

            $discountPrice = new Price($discountTotalWithoutVat, $discountTotal);
            $totalDiscount = $totalDiscount->add($discountPrice);
        }

        return $totalDiscount;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkTransportPriceAndWeightLimit(Transport $transport, Cart $cart): void
    {
        try {
            $this->transportValidationFacade->checkTransportPriceAndWeightLimit($transport, $cart);
        } catch (TransportPriceChangedException $exception) {
            $this->cartWithModificationsResult->setTransportPriceChanged(true);
            $this->cartTransportFacade->setTransportWatchedPrice($cart, $exception->getCurrentTransportPrice()->getPriceWithVat());
        } catch (TransportPriceNotFoundException) {
            $this->cartWithModificationsResult->setTransportWeightLimitExceeded(true);
            $this->cartTransportFacade->unsetCartTransport($cart);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     */
    protected function checkPaymentPrice(Cart $cart, Payment $payment): void
    {
        try {
            $this->paymentValidationFacade->checkPaymentPrice($payment, $cart);
        } catch (PaymentPriceChangedException $exception) {
            $this->cartWithModificationsResult->setPaymentPriceChanged(true);
            $this->cartPaymentFacade->setPaymentWatchedPrice($cart, $exception->getCurrentPaymentPrice()->getPriceWithVat());
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkTransportAvailabilityForProductsInCart(Transport $transport, Cart $cart): void
    {
        try {
            $this->transportValidationFacade->checkTransportAvailabilityForProductsInCart($transport, $cart);
        } catch (TransportUnavailableForProductsInCartException) {
            $this->cartWithModificationsResult->setTransportIsUnavailable();
            $this->cartTransportFacade->unsetCartTransport($cart);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkPersonalPickupStoreAvailability(Transport $transport, Cart $cart): void
    {
        try {
            $this->transportValidationFacade->checkPersonalPickupStoreAvailability($transport, $cart->getPickupPlaceIdentifier());
        } catch (StoreByUuidNotFoundException) {
            $this->cartWithModificationsResult->setPersonalPickupStoreUnavailable(true);
            $this->cartTransportFacade->unsetPickupPlaceIdentifierFromCart($cart);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkPacketeryIdIsValid(Transport $transport, Cart $cart): void
    {
        if ($transport->isPacketery() && !is_numeric($cart->getPickupPlaceIdentifier())) {
            throw new InvalidPacketeryAddressIdUserError('Wrong packetery address ID');
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkTransport(Cart $cart): void
    {
        if ($cart->isEmpty()) {
            $this->cartTransportFacade->unsetCartTransport($cart);
        }

        $transport = $cart->getTransport();

        if ($transport === null) {
            if ($cart->getTransportWatchedPrice() !== null) {
                // this might happen when transport is set to null in cart thanks to "onDelete=SET NULL" ORM setting
                $this->setTransportInCartUnavailable($cart);
            }

            return;
        }

        if ($this->transportFacade->isTransportVisibleAndEnabledOnCurrentDomain($transport) === false) {
            $this->setTransportInCartUnavailable($cart);

            return;
        }

        $this->checkTransportPriceAndWeightLimit($transport, $cart);

        if ($cart->getTransport() === null) {
            return;
        }
        $this->checkTransportAvailabilityForProductsInCart($transport, $cart);
        $this->checkPersonalPickupStoreAvailability($transport, $cart);
        $this->checkPacketeryIdIsValid($transport, $cart);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkPayment(Cart $cart): void
    {
        if ($cart->isEmpty()) {
            $this->cartPaymentFacade->unsetCartPayment($cart);
        }

        $payment = $cart->getPayment();

        if ($payment === null) {
            if ($cart->getPaymentWatchedPrice() !== null) {
                // this might happen when payment is set to null in cart thanks to "onDelete=SET NULL" ORM setting
                $this->setPaymentInCartUnavailable($cart);
            }

            return;
        }

        if ($this->paymentFacade->isPaymentVisibleAndEnabledOnCurrentDomain($payment) === false) {
            $this->setPaymentInCartUnavailable($cart);

            return;
        }
        $this->checkPaymentPrice($cart, $payment);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function setTransportInCartUnavailable(Cart $cart): void
    {
        $this->cartWithModificationsResult->setTransportIsUnavailable();
        $this->cartTransportFacade->unsetCartTransport($cart);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function setPaymentInCartUnavailable(Cart $cart): void
    {
        $this->cartWithModificationsResult->setPaymentIsUnavailable();
        $this->cartPaymentFacade->unsetCartPayment($cart);
    }
}
