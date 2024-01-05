<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Payment;

use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
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
     */
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly OrderPaymentsConfigFactory $orderPaymentsConfigFactory,
    ) {
    }

    /**
     * @return array
     */
    public function paymentsQuery(): array
    {
        return $this->paymentFacade->getVisibleOnCurrentDomain();
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
}
