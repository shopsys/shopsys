<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;

/**
 * @property \App\Model\Payment\Payment[] $payments
 */
class TransportData extends BaseTransportData
{
    /**
     * @var string|null
     */
    public ?string $trackingUrl;

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

        $this->maxWeight = null;
    }
}
