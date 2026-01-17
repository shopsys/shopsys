<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Payment\Exception\PaymentNotFoundUserError;

class PaymentQuery extends AbstractQuery
{
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly Domain $domain,
    ) {
    }

    public function paymentQuery(string $uuid): Payment
    {
        try {
            return $this->paymentFacade->getEnabledOnDomainByUuid($uuid, $this->domain->getId());
        } catch (PaymentNotFoundException $paymentNotFoundException) {
            throw new PaymentNotFoundUserError($paymentNotFoundException->getMessage());
        }
    }
}
