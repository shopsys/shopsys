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
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly OrderPaymentsConfigFactory $orderPaymentsConfigFactory,
    ) {
    }

    public function paymentsQuery(): array
    {
        return $this->paymentFacade->getVisibleOnCurrentDomain();
    }

    public function orderPaymentsQuery(string $orderUuid, ?string $orderUrlHash): OrderPaymentsConfig
    {
        $order = $this->orderApiFacade->getAuthorizedOrder($orderUuid, $orderUrlHash);

        return $this->orderPaymentsConfigFactory->createForOrder($order);
    }
}
