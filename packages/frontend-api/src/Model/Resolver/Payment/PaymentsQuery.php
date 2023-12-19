<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Payment;

use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PaymentsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade $orderApiFacade
     */
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly OrderApiFacade $orderApiFacade,
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
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function orderPaymentsQuery(string $orderUuid): array
    {
        $order = $this->orderApiFacade->getByUuid($orderUuid);

        return $this->paymentFacade->getVisibleForOrder($order);
    }
}
