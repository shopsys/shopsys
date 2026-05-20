<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Model\Transport\Transport as BaseTransport;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @property \Doctrine\Common\Collections\Collection<int, \App\Model\Payment\Payment> $payments
 * @method \App\Model\Payment\Payment[] getPayments()
 * @method void setDomains(\App\Model\Transport\TransportData $transportData)
 * @method void createDomains(\App\Model\Transport\TransportData $transportData)
 * @method void addPayment(\App\Model\Payment\Payment $payment)
 * @method void setPayments(\App\Model\Payment\Payment[] $payments)
 * @method void removePayment(\App\Model\Payment\Payment $payment)
 * @method \App\Model\Transport\TransportTranslation translation(?string $locale = null)
 * @property \Doctrine\Common\Collections\Collection<string, \App\Model\Transport\TransportTranslation> $translations
 * @method \Doctrine\Common\Collections\Collection<string, \App\Model\Transport\TransportTranslation> getTranslations()
 * @method __construct(\App\Model\Transport\TransportData $transportData)
 * @method void edit(\App\Model\Transport\TransportData $transportData)
 * @method void setData(\App\Model\Transport\TransportData $transportData)
 * @method void setTranslations(\App\Model\Transport\TransportData $transportData)
 */
#[AsMcpTable]
#[ORM\Table(name: 'transports')]
#[ORM\Entity]
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
