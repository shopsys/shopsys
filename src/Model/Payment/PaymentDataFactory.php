<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Payment\Payment as BasePayment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData as BasePaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory as BasePaymentDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class PaymentDataFactory extends BasePaymentDataFactory
{
    /**
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Component\Image\ImageFacade $imageFacade
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
    public function create(): BasePaymentData
    {
        $paymentData = new PaymentData();
        $this->fillNew($paymentData);

        $paymentData->hiddenByGoPay = false;

        return $paymentData;
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @return \App\Model\Payment\PaymentData
     */
    public function createFromPayment(BasePayment $payment): BasePaymentData
    {
        $paymentData = new PaymentData();
        $this->fillFromPayment($paymentData, $payment);

        $paymentData->type = $payment->getType();
        $paymentData->goPayPaymentMethod = $payment->getGoPayPaymentMethod();
        $paymentData->hiddenByGoPay = $payment->isHiddenByGoPay();

        return $paymentData;
    }
}
