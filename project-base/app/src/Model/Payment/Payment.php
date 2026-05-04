<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Payment\Payment as BasePayment;

/**
 * @property \Doctrine\Common\Collections\Collection<int, \App\Model\Transport\Transport> $transports
 * @method \App\Model\Transport\Transport[] getTransports()
 * @method void addTransport(\App\Model\Transport\Transport $transport)
 * @method void setTransports(\App\Model\Transport\Transport[] $transports)
 * @method void removeTransport(\App\Model\Transport\Transport $transport)
 * @method void setTranslations(\App\Model\Payment\PaymentData $paymentData)
 * @method void setDomains(\App\Model\Payment\PaymentData $paymentData)
 * @method void createDomains(\App\Model\Payment\PaymentData $paymentData)
 * @method __construct(\App\Model\Payment\PaymentData $paymentData)
 * @method void edit(\App\Model\Payment\PaymentData $paymentData)
 * @method void setData(\App\Model\Payment\PaymentData $paymentData)
 */
#[ORM\Table(name: 'payments')]
#[ORM\Entity]
class Payment extends BasePayment
{
}
