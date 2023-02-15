<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Payment;

use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PaymentsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(
        protected readonly PaymentFacade $paymentFacade
    ) {
    }

    /**
     * @return array
     */
    public function paymentsQuery(): array
    {
        return $this->paymentFacade->getVisibleOnCurrentDomain();
    }
}
