<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

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
     * @var array<int, string>
     */
    public $orderRoundingTypeByDomainId;

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
     * @var array<int, \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null>
     */
    public $goPayPaymentMethodByDomainId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array<int, bool>
     */
    public $hiddenByGoPay;

    /**
     * @var string[]|null[]
     */
    public $accountNumberByDomainId;

    /**
     * @var string[]|null[]
     */
    public $ibanByDomainId;

    /**
     * @var string[]|null[]
     */
    public $bicSwiftByDomainId;

    public function __construct()
    {
        $this->name = [];
        $this->description = [];
        $this->instructions = [];
        $this->hidden = false;
        $this->enabled = [];
        $this->transports = [];
        $this->orderRoundingTypeByDomainId = [];
        $this->pricesIndexedByDomainId = [];
        $this->vatsIndexedByDomainId = [];
        $this->goPayPaymentMethodByDomainId = [];
        $this->hiddenByGoPay = [];
        $this->type = PaymentTypeEnum::TYPE_BASIC;
        $this->accountNumberByDomainId = [];
        $this->ibanByDomainId = [];
        $this->bicSwiftByDomainId = [];
    }
}
