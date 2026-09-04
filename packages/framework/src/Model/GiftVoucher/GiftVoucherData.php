<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

class GiftVoucherData
{
    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var string|null
     */
    public $code;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $valueWithVat;

    /**
     * @var string|null
     */
    public $currencyCode;

    /**
     * @var string
     */
    public $vatPercent;

    /**
     * @var string
     */
    public $status;

    /**
     * @var \DateTimeImmutable|null
     */
    public $activatedAt;

    /**
     * @var \DateTimeImmutable|null
     */
    public $validUntil;

    /**
     * @var string|null
     */
    public $productCatnum;

    /**
     * @var string|null
     */
    public $productName;

    /**
     * @var string|null
     */
    public $customerEmail;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public $createdOnOrder;

    public function __construct()
    {
        $this->vatPercent = '0';
        $this->status = GiftVoucherStatusEnum::STATUS_UNREDEEMED;
    }
}
