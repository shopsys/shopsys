<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Transport;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class TransportResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Transport' => [
                'payments' => function (Transport $transport) {
                    return $this->paymentFacade->getVisibleOnCurrentDomainByTransport($transport);
                },
                'transportTypeCode' => function (Transport $transport) {
                    return $transport->getType();
                },
                'vat' => function (Transport $transport) {
                    return $transport->getVatForDomain($this->domain->getId());
                },
            ],
        ];
    }
}
