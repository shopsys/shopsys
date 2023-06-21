<?php

declare(strict_types=1);

namespace App\Model\Transport;

use App\Model\Transport\Type\TransportType;
use App\Model\Transport\Type\TransportTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Transport\Transport as BaseTransport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;

/**
 * @ORM\Table(name="transports")
 * @ORM\Entity
 * @property \App\Model\Payment\Payment[]|\Doctrine\Common\Collections\Collection $payments
 * @method \App\Model\Payment\Payment[] getPayments()
 * @method setDomains(\App\Model\Transport\TransportData $transportData)
 * @method createDomains(\App\Model\Transport\TransportData $transportData)
 * @method addPayment(\App\Model\Payment\Payment $payment)
 * @method setPayments(\App\Model\Payment\Payment[] $payments)
 * @method removePayment(\App\Model\Payment\Payment $payment)
 * @method \App\Model\Transport\TransportTranslation translation(?string $locale = null)
 * @property \App\Model\Transport\TransportTranslation[]|\Doctrine\Common\Collections\Collection $translations
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
     * @var int
     * @ORM\Column(type="integer")
     */
    private int $daysUntilDelivery;

    /**
     * @var \App\Model\Transport\Type\TransportType
     * @ORM\ManyToOne(targetEntity="App\Model\Transport\Type\TransportType")
     * @ORM\JoinColumn(nullable=false)
     */
    private TransportType $transportType;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $trackingUrl;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $maxWeight;

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
        $this->daysUntilDelivery = $transportData->daysUntilDelivery;
        $this->trackingUrl = $transportData->trackingUrl;
        $this->transportType = $transportData->transportType;
        $this->maxWeight = $transportData->maxWeight > 0 ? $transportData->maxWeight : null;
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    protected function setTranslations(BaseTransportData $transportData)
    {
        parent::setTranslations($transportData);

        foreach ($transportData->trackingInstructions as $locale => $trackingInstruction) {
            $this->translation($locale)->setTrackingInstruction($trackingInstruction);
        }
    }

    /**
     * @return bool
     */
    public function isPersonalPickup(): bool
    {
        return $this->personalPickup;
    }

    /**
     * @return int
     */
    public function getDaysUntilDelivery(): int
    {
        return $this->daysUntilDelivery;
    }

    /**
     * @return ?string
     */
    public function getTrackingUrl(): ?string
    {
        return $this->trackingUrl;
    }

    /**
     * @return \App\Model\Transport\TransportTranslation
     */
    protected function createTranslation(): TransportTranslation
    {
        return new TransportTranslation();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getTrackingInstruction($locale = null): ?string
    {
        return $this->translation($locale)->getTrackingInstruction();
    }

    /**
     * @return \App\Model\Transport\Type\TransportType
     */
    public function getTransportType(): TransportType
    {
        return $this->transportType;
    }

    /**
     * @return int|null
     */
    public function getMaxWeight(): ?int
    {
        return $this->maxWeight;
    }

    /**
     * @return bool
     */
    public function isPacketery(): bool
    {
        return $this->transportType->getCode() === TransportTypeEnum::TYPE_PACKETERY;
    }
}
