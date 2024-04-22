<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\InputOrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Payment\Exception\InvalidPaymentTransportCombinationException;
use Shopsys\FrontendApiBundle\Model\Payment\Exception\PaymentPriceChangedException;

class PaymentValidationFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\InputOrderDataFactory $inputOrderDataFactory
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly OrderProcessor $orderProcessor,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly InputOrderDataFactory $inputOrderDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function checkPaymentPrice(Payment $payment, Cart $cart): void
    {
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $orderData = $this->orderDataFactory->create();
        $inputOrderData = $this->inputOrderDataFactory->createFromCart($cart);
        $inputOrderData->setPayment($payment);
        $orderData = $this->orderProcessor->process(
            $inputOrderData,
            $orderData,
            $this->domain->getCurrentDomainConfig(),
            $currentCustomerUser,
        );

        $calculatedPaymentPrice = $orderData->totalPricesByItemType[OrderItem::TYPE_PAYMENT];

        $paymentWatchedPrice = $cart->getPaymentWatchedPrice();

        if ($paymentWatchedPrice === null || ($calculatedPaymentPrice !== null && !$calculatedPaymentPrice->getPriceWithVat()->equals($paymentWatchedPrice))) {
            throw new PaymentPriceChangedException($calculatedPaymentPrice);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param string|null $cartUuid
     */
    public function checkPaymentTransportRelation(Payment $payment, ?string $cartUuid): void
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        $transport = $cart->getTransport();

        if ($transport === null || in_array($transport, $payment->getTransports(), true)) {
            return;
        }

        throw new InvalidPaymentTransportCombinationException();
    }
}
