<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Payment;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Payment\Payment as BasePayment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData as BasePaymentData;

/**
 * @ORM\Table(name="payments")
 * @ORM\Entity
 * @property \Shopsys\ShopBundle\Model\Transport\Transport[]|\Doctrine\Common\Collections\Collection $transports
 * @method \Shopsys\ShopBundle\Model\Transport\Transport[] getTransports()
 * @method addTransport(\Shopsys\ShopBundle\Model\Transport\Transport $transport)
 * @method setTransports(\Shopsys\ShopBundle\Model\Transport\Transport[] $transports)
 * @method removeTransport(\Shopsys\ShopBundle\Model\Transport\Transport $transport)
 * @method setTranslations(\Shopsys\ShopBundle\Model\Payment\PaymentData $paymentData)
 * @method setDomains(\Shopsys\ShopBundle\Model\Payment\PaymentData $paymentData)
 * @method createDomains(\Shopsys\ShopBundle\Model\Payment\PaymentData $paymentData)
 */
class Payment extends BasePayment
{
    /**
     * @param \Shopsys\ShopBundle\Model\Payment\PaymentData $paymentData
     */
    public function __construct(BasePaymentData $paymentData)
    {
        parent::__construct($paymentData);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Payment\PaymentData $paymentData
     */
    public function edit(BasePaymentData $paymentData)
    {
        parent::edit($paymentData);
    }
}
