<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Payment;

use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTypeProvider;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderPaymentsConfig;
use Shopsys\FrontendApiBundle\Model\Order\OrderPaymentsConfigFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PaymentsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade $orderApiFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderPaymentsConfigFactory $orderPaymentsConfigFactory
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentTypeProvider $paymentTypeProvider
     */
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly OrderPaymentsConfigFactory $orderPaymentsConfigFactory,
        protected readonly PaymentTypeProvider $paymentTypeProvider,
    ) {
    }

    /**
     * @param bool $displayInCartOnly
     * @return array
     */
    public function paymentsQuery(bool $displayInCartOnly): array
    {
        return $this->filterByDisplayInCartOnly($this->paymentFacade->getVisibleOnCurrentDomain(), $displayInCartOnly);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param bool $displayInCartOnly
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function paymentsOfTransportQuery(Transport $transport, bool $displayInCartOnly): array
    {
        return $this->filterByDisplayInCartOnly($this->paymentFacade->getVisibleOnCurrentDomainByTransport($transport), $displayInCartOnly);
    }

    /**
     * @param string $orderUuid
     * @return \Shopsys\FrontendApiBundle\Model\Order\OrderPaymentsConfig
     */
    public function orderPaymentsQuery(string $orderUuid): OrderPaymentsConfig
    {
        $order = $this->orderApiFacade->getByUuid($orderUuid);

        return $this->orderPaymentsConfigFactory->createForOrder($order);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     * @param bool $displayInCartOnly
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    protected function filterByDisplayInCartOnly(array $payments, bool $displayInCartOnly): array
    {
        if ($displayInCartOnly === false) {
            return $payments;
        }

        $displayInCartOnlyPaymentTypes = $this->paymentTypeProvider->getAllEnabledInCartIndexedByTranslations();

        $displayInCartOnlyPayments = [];

        foreach ($payments as $payment) {
            if (in_array($payment->getType(), $displayInCartOnlyPaymentTypes, true)) {
                $displayInCartOnlyPayments[] = $payment;
            }
        }

        return $displayInCartOnlyPayments;
    }
}
