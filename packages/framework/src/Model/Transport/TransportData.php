<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;

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

    public function __construct()
    {
        $this->name = [];
        $this->description = [];
        $this->instructions = [];
        $this->hidden = false;
        $this->enabled = [];
        $this->image = new ImageUploadData();
        $this->payments = [];
        $this->pricesIndexedByDomainId = [];
        $this->vatsIndexedByDomainId = [];
    }
}
