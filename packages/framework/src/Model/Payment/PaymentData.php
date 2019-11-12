<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;

class PaymentData
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
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public $transports;

    /**
     * @var bool
     */
    public $czkRounding;

    /**
     * @var bool[]
     */
    public $enabled;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money[]|object[]
     */
    public $pricesIndexedByDomainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]|object[]
     */
    public $vatsIndexedByDomainId;

    public function __construct()
    {
        $this->name = [];
        $this->description = [];
        $this->instructions = [];
        $this->hidden = false;
        $this->enabled = [];
        $this->image = new ImageUploadData();
        $this->transports = [];
        $this->czkRounding = false;
        $this->pricesIndexedByDomainId = [];
        $this->vatsIndexedByDomainId = [];
    }
}
