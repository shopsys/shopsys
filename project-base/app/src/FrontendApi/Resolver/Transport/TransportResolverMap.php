<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Transport;

use App\Model\Payment\PaymentFacade;
use App\Model\Transport\Transport;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class TransportResolverMap extends ResolverMap
{
    /**
     * @var \App\Model\Payment\PaymentFacade
     */
    private PaymentFacade $paymentFacade;

    /**
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(PaymentFacade $paymentFacade)
    {
        $this->paymentFacade = $paymentFacade;
    }

    /**
     * @see https://github.com/shopsys/shopsys/issues/2381
     * @return array
     */
    protected function map(): array
    {
        return [
            'Transport' => [
                'payments' => function (Transport $transport) {
                    return $this->paymentFacade->getVisibleOnCurrentDomainByTransport($transport);
                },
            ],
        ];
    }
}
