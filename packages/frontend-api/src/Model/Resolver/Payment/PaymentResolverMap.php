<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Payment;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Payment;

class PaymentResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return array
     */
    #[Override]
    protected function map(): array
    {
        return [
            'Payment' => [
                'goPayPaymentMethod' => function (Payment $payment) {
                    return $payment->getGoPayPaymentMethodByDomainId($this->domain->getId());
                },
                'vatPercent' => function (Payment $payment) {
                    return $payment->getVatForDomain($this->domain->getId())->getPercent();
                },
            ],
        ];
    }
}
