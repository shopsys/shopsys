<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Payment;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PaymentQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly Domain $domain
    ) {
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function paymentQuery(string $uuid): Payment
    {
        try {
            return $this->paymentFacade->getEnabledOnDomainByUuid($uuid, $this->domain->getId());
        } catch (PaymentNotFoundException $paymentNotFoundException) {
            throw new UserError($paymentNotFoundException->getMessage());
        }
    }
}
