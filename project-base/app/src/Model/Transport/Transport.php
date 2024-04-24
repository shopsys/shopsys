<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Model\Transport\Transport as BaseTransport;

/**
 * @ORM\Table(name="transports")
 * @ORM\Entity
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Payment\Payment> $payments
 * @method \App\Model\Payment\Payment[] getPayments()
 * @method setDomains(\App\Model\Transport\TransportData $transportData)
 * @method createDomains(\App\Model\Transport\TransportData $transportData)
 * @method addPayment(\App\Model\Payment\Payment $payment)
 * @method setPayments(\App\Model\Payment\Payment[] $payments)
 * @method removePayment(\App\Model\Payment\Payment $payment)
 * @method \App\Model\Transport\TransportTranslation translation(?string $locale = null)
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Transport\TransportTranslation> $translations
 * @method __construct(\App\Model\Transport\TransportData $transportData)
 * @method edit(\App\Model\Transport\TransportData $transportData)
 * @method setData(\App\Model\Transport\TransportData $transportData)
 * @method setTranslations(\App\Model\Transport\TransportData $transportData)
 */
class Transport extends BaseTransport
{
    /**
     * @return \App\Model\Transport\TransportTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new TransportTranslation();
    }
}
