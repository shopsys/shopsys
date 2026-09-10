<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentFacade;
use Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreByUuidNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;
use Shopsys\FrontendApiBundle\Model\Order\Exception\InvalidPacketeryAddressIdUserError;
use Shopsys\FrontendApiBundle\Model\Payment\Exception\PaymentPriceChangedException;
use Shopsys\FrontendApiBundle\Model\Payment\Exception\PaymentUnavailableForRemainingAmountToPayInCartException;
use Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\TransportPriceChangedException;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\TransportUnavailableForProductsInCartException;
use Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade;

class TransportAndPaymentWatcherFacade
{
    protected CartWithModificationsResult $cartWithModificationsResult;

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
        protected readonly OrderPriceCalculation $orderPriceCalculation,
    ) {
    }

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

        $this->cartWithModificationsResult->setTotalPrice($orderData->totalPrice);
        $this->cartWithModificationsResult->setRemainingAmountToPay(
            $this->orderPriceCalculation->calculateRemainingAmountToPay(
                $orderData->totalPrice->getPriceWithVat(),
                $cart->getAllAppliedGiftVouchers(),
            ),
        );
        $this->cartWithModificationsResult->setRemainingItemsAmountToPay(
            $this->orderPriceCalculation->calculateRemainingAmountToPay(
                $productsPrice->getPriceWithVat(),
                $cart->getAllAppliedGiftVouchers(),
            ),
        );
        $this->cartWithModificationsResult->setGiftVoucherProductItemsPriceWithVat(
            $orderData->getGiftVoucherProductItemsTotalPrice()->getPriceWithVat(),
        );
        $this->cartWithModificationsResult->setTotalItemsPrice($productsPrice);
        $this->cartWithModificationsResult->setTotalItemsPriceBeforeDiscount($orderData->basicTotalItemsPrice);
        $this->cartWithModificationsResult->setTotalProductPriceAdjustmentsDiscount($orderData->totalProductPriceAdjustmentsDiscount);
        $this->cartWithModificationsResult->setTotalDiscountPrice($orderData->getTotalDiscountPrice());
        $this->cartWithModificationsResult->setRoundingPrice($orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_ROUNDING]);

        $this->checkPayment($cart);
    }

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

    protected function checkPaymentPrice(Cart $cart, Payment $payment): void
    {
        try {
            $this->paymentValidationFacade->checkPaymentPrice($payment, $cart);
        } catch (PaymentPriceChangedException $exception) {
            $this->cartWithModificationsResult->setPaymentPriceChanged(true);
            $this->cartPaymentFacade->setPaymentWatchedPrice($cart, $exception->getCurrentPaymentPrice()->getPriceWithVat());
        }
    }

    protected function checkTransportAvailabilityForProductsInCart(Transport $transport, Cart $cart): void
    {
        try {
            $this->transportValidationFacade->checkTransportAvailabilityForProductsInCart($transport, $cart);
        } catch (TransportUnavailableForProductsInCartException) {
            $this->cartWithModificationsResult->setTransportIsUnavailable();
            $this->cartTransportFacade->unsetCartTransport($cart);
        }
    }

    protected function checkPersonalPickupStoreAvailability(Transport $transport, Cart $cart): void
    {
        try {
            $this->transportValidationFacade->checkPersonalPickupStoreAvailability($transport, $cart->getPickupPlaceIdentifier());
        } catch (StoreByUuidNotFoundException) {
            $this->cartWithModificationsResult->setPersonalPickupStoreUnavailable(true);
            $this->cartTransportFacade->unsetPickupPlaceIdentifierFromCart($cart);
        }
    }

    protected function checkPacketeryIdIsValid(Transport $transport, Cart $cart): void
    {
        if ($transport->isPacketery() && !is_numeric($cart->getPickupPlaceIdentifier())) {
            throw new InvalidPacketeryAddressIdUserError('Wrong packetery address ID');
        }
    }

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
        $this->checkPaymentSuitabilityForRemainingAmountToPay($cart, $payment);

        if ($cart->getPayment() === null) {
            return;
        }
        $this->checkPaymentPrice($cart, $payment);
    }

    protected function checkPaymentSuitabilityForRemainingAmountToPay(Cart $cart, Payment $payment): void
    {
        try {
            $this->paymentValidationFacade->checkPaymentSuitabilityForRemainingAmountToPay($payment, $cart);
        } catch (PaymentUnavailableForRemainingAmountToPayInCartException) {
            $this->setPaymentInCartUnavailable($cart);
        }
    }

    protected function setTransportInCartUnavailable(Cart $cart): void
    {
        $this->cartWithModificationsResult->setTransportIsUnavailable();
        $this->cartTransportFacade->unsetCartTransport($cart);
    }

    protected function setPaymentInCartUnavailable(Cart $cart): void
    {
        $this->cartWithModificationsResult->setPaymentIsUnavailable();
        $this->cartPaymentFacade->unsetCartPayment($cart);
    }
}
