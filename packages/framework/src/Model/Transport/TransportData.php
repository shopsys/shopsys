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
     * @var \Shopsys\FrameworkBundle\Component\Money\Money[]
     */
    public $pricesIndexedByDomainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public $vatsIndexedByDomainId;

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var int|null
     */
    public $daysUntilDelivery;

    /**
     * @var int|null
     */
    public $maxWeight;

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

    public function __construct()
    {
        $this->name = [];
        $this->description = [];
        $this->instructions = [];
        $this->trackingInstructions = [];
        $this->hidden = false;
        $this->enabled = [];
        $this->payments = [];
        $this->pricesIndexedByDomainId = [];
        $this->vatsIndexedByDomainId = [];
        $this->type = TransportTypeEnum::TYPE_COMMON;
    }
}
