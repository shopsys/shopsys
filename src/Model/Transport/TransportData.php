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
     * @var bool
     */
    public $personalPickup;

    /**
     * @var string
     */
    public string $type;

    /**
     * @var bool
     */
    public $isOverLimitTransport;

    /**
     * @var int|null
     */
    public ?int $daysUntilDelivery;

    /**
     * @var string
     */
    public string $deliveryCode;

    /**
     * @var int
     */
    public int $typeOfDeliveryKey;

    public function __construct()
    {
        parent::__construct();

        $this->personalPickup = false;
        $this->isOverLimitTransport = false;
    }
}
