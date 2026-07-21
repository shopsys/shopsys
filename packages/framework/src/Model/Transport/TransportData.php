<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

class TransportData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var string[]|null[]
     */
    public $description;

    /**
     * @var string[]|null[]
     */
    public $instructions;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public $payments;

    /**
     * @var bool[]
     */
    public $enabled;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportInputPricesData[]
     */
    public $inputPricesByDomain;

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var int|null
     */
    public $daysUntilDelivery;

    /**
     * @var bool
     */
    public $deliversOnWeekends;

    /**
     * @var bool
     */
    public $deliversOnPublicHolidays;

    /**
     * @var bool
     */
    public $deliversOnInternalClosedDays;

    /**
     * @var string|null
     */
    public $trackingUrl;

    /**
     * @var string[]|null[]
     */
    public $trackingInstructions;

    /**
     * @var string
     */
    public $type;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportGroup|null
     */
    public $group;

    public function __construct()
    {
        $this->name = [];
        $this->description = [];
        $this->instructions = [];
        $this->trackingInstructions = [];
        $this->hidden = false;
        $this->deliversOnWeekends = false;
        $this->deliversOnPublicHolidays = false;
        $this->deliversOnInternalClosedDays = false;
        $this->enabled = [];
        $this->payments = [];
        $this->inputPricesByDomain = [];
        $this->type = TransportTypeEnum::TYPE_COMMON;
    }
}
