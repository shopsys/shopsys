<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Transport\Transport as BaseTransport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;

/**
 * @ORM\Table(name="transports")
 * @ORM\Entity
 * @property \Shopsys\ShopBundle\Model\Payment\Payment[]|\Doctrine\Common\Collections\Collection $payments
 * @method \Shopsys\ShopBundle\Model\Payment\Payment[] getPayments()
 * @method setTranslations(\Shopsys\ShopBundle\Model\Transport\TransportData $transportData)
 * @method setDomains(\Shopsys\ShopBundle\Model\Transport\TransportData $transportData)
 * @method createDomains(\Shopsys\ShopBundle\Model\Transport\TransportData $transportData)
 * @method addPayment(\Shopsys\ShopBundle\Model\Payment\Payment $payment)
 * @method setPayments(\Shopsys\ShopBundle\Model\Payment\Payment[] $payments)
 * @method removePayment(\Shopsys\ShopBundle\Model\Payment\Payment $payment)
 */
class Transport extends BaseTransport
{
    /**
     * @param \Shopsys\ShopBundle\Model\Transport\TransportData $transportData
     */
    public function __construct(BaseTransportData $transportData)
    {
        parent::__construct($transportData);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Transport\TransportData $transportData
     */
    public function edit(BaseTransportData $transportData)
    {
        parent::edit($transportData);
    }
}
