<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Transport;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class TransportResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
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
                'vatPercent' => function (Transport $transport) {
                    return $transport->getVatForDomain($this->domain->getId())->getPercent();
                },
            ],
        ];
    }
}
