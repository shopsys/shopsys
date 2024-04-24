<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class CartPaymentDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     */
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly OrderProcessor $orderProcessor,
        protected readonly OrderInputFactory $orderInputFactory,
        protected readonly OrderDataFactory $orderDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param string $paymentUuid
     * @param string|null $goPayBankSwift
     * @return \Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentData
     */
    public function create(Cart $cart, string $paymentUuid, ?string $goPayBankSwift): CartPaymentData
    {
        $domainId = $this->domain->getId();
        $payment = $this->paymentFacade->getEnabledOnDomainByUuid($paymentUuid, $domainId);
        $watchedPriceWithVat = $this->getPaymentWatchedPriceWithVat($domainId, $cart, $payment);

        $cartPaymentData = new CartPaymentData();
        $cartPaymentData->payment = $payment;
        $cartPaymentData->watchedPrice = $watchedPriceWithVat;
        $cartPaymentData->goPayBankSwift = $goPayBankSwift;

        return $cartPaymentData;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getPaymentWatchedPriceWithVat(int $domainId, Cart $cart, Payment $payment): Money
    {
        $orderData = $this->orderDataFactory->create();
        $orderInput = $this->orderInputFactory->createFromCart($cart, $this->domain->getDomainConfigById($domainId));
        $orderInput->setPayment($payment);

        $orderData = $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );

        return $orderData->totalPricesByItemType[OrderItem::TYPE_PAYMENT]->getPriceWithVat();
    }
}
