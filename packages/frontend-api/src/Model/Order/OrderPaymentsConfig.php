<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Payment\Payment;

class OrderPaymentsConfig
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $currentPayment
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $availablePayments
     */
    public function __construct(
        public readonly Payment $currentPayment,
        public readonly array $availablePayments,
    ) {
    }
}
