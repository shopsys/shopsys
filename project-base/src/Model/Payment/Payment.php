<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Payment\Payment as BasePayment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData as BasePaymentData;

/**
 * @ORM\Table(name="payments")
 * @ORM\Entity
 * @property \App\Model\Transport\Transport[]|\Doctrine\Common\Collections\Collection $transports
 * @method \App\Model\Transport\Transport[] getTransports()
 * @method addTransport(\App\Model\Transport\Transport $transport)
 * @method setTransports(\App\Model\Transport\Transport[] $transports)
 * @method removeTransport(\App\Model\Transport\Transport $transport)
 * @method setTranslations(\App\Model\Payment\PaymentData $paymentData)
 * @method setDomains(\App\Model\Payment\PaymentData $paymentData)
 * @method createDomains(\App\Model\Payment\PaymentData $paymentData)
 */
class Payment extends BasePayment
{
    /**
     * @param \App\Model\Payment\PaymentData $paymentData
     */
    public function __construct(BasePaymentData $paymentData)
    {
        parent::__construct($paymentData);
    }

    /**
     * @param \App\Model\Payment\PaymentData $paymentData
     */
    public function edit(BasePaymentData $paymentData)
    {
        parent::edit($paymentData);
    }
}
