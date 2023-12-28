<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Payment\Payment as BasePayment;

/**
 * @ORM\Table(name="payments")
 * @ORM\Entity
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Transport\Transport> $transports
 * @method \App\Model\Transport\Transport[] getTransports()
 * @method addTransport(\App\Model\Transport\Transport $transport)
 * @method setTransports(\App\Model\Transport\Transport[] $transports)
 * @method removeTransport(\App\Model\Transport\Transport $transport)
 * @method setTranslations(\App\Model\Payment\PaymentData $paymentData)
 * @method setDomains(\App\Model\Payment\PaymentData $paymentData)
 * @method createDomains(\App\Model\Payment\PaymentData $paymentData)
 * @method __construct(\App\Model\Payment\PaymentData $paymentData)
 * @method edit(\App\Model\Payment\PaymentData $paymentData)
 * @method setData(\App\Model\Payment\PaymentData $paymentData)
 * @method setGoPayPaymentMethod(\App\Model\Payment\PaymentData $paymentData)
 */
class Payment extends BasePayment
{
}
