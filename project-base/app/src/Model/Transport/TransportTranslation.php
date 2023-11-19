<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Transport\TransportTranslation as BaseTransportTranslation;

/**
 * @ORM\Table(name="transport_translations")
 * @ORM\Entity
 * @property \App\Model\Transport\Transport $translatable
 */
class TransportTranslation extends BaseTransportTranslation
{
    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $trackingInstruction;

    /**
     * @return string|null
     */
    public function getTrackingInstruction(): ?string
    {
        return $this->trackingInstruction;
    }

    /**
     * @param string|null $trackingInstruction
     */
    public function setTrackingInstruction(?string $trackingInstruction): void
    {
        $this->trackingInstruction = $trackingInstruction;
    }
}
