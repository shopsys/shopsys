<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Transport;

use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class TransportsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(
        protected readonly TransportFacade $transportFacade,
        protected readonly PaymentFacade $paymentFacade
    ) {
    }

    /**
     * @return array
     */
    public function transportsQuery(): array
    {
        return $this->transportFacade->getVisibleOnCurrentDomain($this->paymentFacade->getVisibleOnCurrentDomain());
    }
}
