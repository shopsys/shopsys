<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData as BasePaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory as BasePaymentDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

/**
 * @method \App\Model\Payment\PaymentData create()
 * @method \App\Model\Payment\PaymentData createFromPayment(\App\Model\Payment\Payment $payment)
 */
class PaymentDataFactory extends BasePaymentDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(
        PaymentFacade $paymentFacade,
        VatFacade $vatFacade,
        Domain $domain,
        ImageFacade $imageFacade
    ) {
        parent::__construct($paymentFacade, $vatFacade, $domain, $imageFacade);
    }

    /**
     * @return \App\Model\Payment\PaymentData
     */
    protected function createInstance(): BasePaymentData
    {
        return new PaymentData();
    }
}
