<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Transport\Transport as BaseTransport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;

/**
 * @ORM\Table(name="transports")
 * @ORM\Entity
 * @property \App\Model\Payment\Payment[]|\Doctrine\Common\Collections\Collection $payments
 * @method \App\Model\Payment\Payment[] getPayments()
 * @method setTranslations(\App\Model\Transport\TransportData $transportData)
 * @method setDomains(\App\Model\Transport\TransportData $transportData)
 * @method createDomains(\App\Model\Transport\TransportData $transportData)
 * @method addPayment(\App\Model\Payment\Payment $payment)
 * @method setPayments(\App\Model\Payment\Payment[] $payments)
 * @method removePayment(\App\Model\Payment\Payment $payment)
 */
class Transport extends BaseTransport
{
    public const TYPE_COMMON = 'common';
    public const TYPE_PACKAGE = 'package';

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $personalPickup;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isOverLimitTransport;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private int $daysUntilDelivery;

    /**
     * @var string
     * @ORM\Column(type="string", length=10)
     */
    private string $deliveryCode;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private int $typeOfDeliveryKey;

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    public function __construct(BaseTransportData $transportData)
    {
        parent::__construct($transportData);
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    public function edit(BaseTransportData $transportData)
    {
        parent::edit($transportData);
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    protected function setData(BaseTransportData $transportData): void
    {
        parent::setData($transportData);

        $this->personalPickup = $transportData->personalPickup;
        $this->isOverLimitTransport = $transportData->isOverLimitTransport;
        $this->daysUntilDelivery = $transportData->daysUntilDelivery;
        $this->deliveryCode = $transportData->deliveryCode;
        $this->typeOfDeliveryKey = $transportData->typeOfDeliveryKey;
    }

    /**
     * @return bool
     */
    public function isPersonalPickup(): bool
    {
        return $this->personalPickup;
    }

    /**
     * @return bool
     */
    public function isOverLimitTransport(): bool
    {
        return $this->isOverLimitTransport;
    }

    /**
     * @return int
     */
    public function getDaysUntilDelivery(): int
    {
        return $this->daysUntilDelivery;
    }

    /**
     * @return string
     */
    public function getDeliveryCode(): string
    {
        return $this->deliveryCode;
    }

    /**
     * @return int
     */
    public function getTypeOfDeliveryKey(): int
    {
        return $this->typeOfDeliveryKey;
    }
}
