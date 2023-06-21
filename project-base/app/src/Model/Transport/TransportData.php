<?php

declare(strict_types=1);

namespace App\Model\Transport;

use App\Model\Transport\Type\TransportType;
use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;

/**
 * @property \App\Model\Payment\Payment[] $payments
 */
class TransportData extends BaseTransportData
{
    /**
     * @var bool
     */
    public $personalPickup;

    /**
     * @var string
     */
    public string $type;

    /**
     * @var int|null
     */
    public ?int $daysUntilDelivery;

    /**
     * @var string|null
     */
    public ?string $trackingUrl;

    /**
     * @var \App\Model\Transport\Type\TransportType
     */
    public TransportType $transportType;

    /**
     * @var string[]|null[]
     */
    public array $trackingInstructions;

    /**
     * @var int|null
     */
    public ?int $maxWeight;

    public function __construct()
    {
        parent::__construct();

        $this->personalPickup = false;
        $this->maxWeight = null;
    }
}
