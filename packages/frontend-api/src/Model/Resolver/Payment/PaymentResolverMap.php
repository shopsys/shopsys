<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Payment;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTypeProvider;

class PaymentResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentTypeProvider $paymentTypeProvider
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly PaymentTypeProvider $paymentTypeProvider,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Payment' => [
                'goPayPaymentMethod' => function (Payment $payment) {
                    return $payment->getGoPayPaymentMethodByDomainId($this->domain->getId());
                },
                'vat' => function (Payment $payment) {
                    return $payment->getVatForDomain($this->domain->getId());
                },
                'displayInCart' => function (Payment $payment) {
                    return in_array($payment->getType(), $this->paymentTypeProvider->getAllEnabledInCartIndexedByTranslations(), true);
                },
            ],
        ];
    }
}
