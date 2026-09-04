<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Model\GiftVoucher\Exception\GiftVoucherAlreadyRedeemedException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[Loggable(Loggable::STRATEGY_INCLUDE_ALL)]
#[ORM\Table(name: 'gift_vouchers')]
#[ORM\Index(columns: ['domain_id'])]
#[ORM\Entity]
class GiftVoucher
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 20, unique: true, nullable: false)]
    protected $code;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer', nullable: false)]
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6)]
    protected $valueWithVat;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 3, nullable: false)]
    protected $currencyCode;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'decimal', precision: 20, scale: 6)]
    protected $vatPercent;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    protected $status;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $activatedAt;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $validUntil;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $productCatnum;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $productName;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $customerEmail;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'created_on_order_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Order::class)]
    protected $createdOnOrder;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'redeemed_on_order_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'redeemedGiftVouchers')]
    protected $redeemedOnOrder;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $redeemedAt;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $emailEnqueuedAt;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $emailSentAt;

    public function __construct(GiftVoucherData $giftVoucherData)
    {
        $this->uuid = $giftVoucherData->uuid ?: Uuid::uuid4()->toString();
        $this->code = $giftVoucherData->code;
        $this->domainId = $giftVoucherData->domainId;
        $this->vatPercent = $giftVoucherData->vatPercent;
        $this->activatedAt = $giftVoucherData->activatedAt;
        $this->productCatnum = $giftVoucherData->productCatnum;
        $this->productName = $giftVoucherData->productName;
        $this->customerEmail = $giftVoucherData->customerEmail;
        $this->createdOnOrder = $giftVoucherData->createdOnOrder;
        $this->setData($giftVoucherData);
    }

    public function edit(GiftVoucherData $giftVoucherData): void
    {
        $this->setData($giftVoucherData);
    }

    protected function setData(GiftVoucherData $giftVoucherData): void
    {
        $this->valueWithVat = $giftVoucherData->valueWithVat;
        $this->currencyCode = $giftVoucherData->currencyCode;
        $this->status = $giftVoucherData->status;
        $this->validUntil = $giftVoucherData->validUntil;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    #[EntityLogIdentify]
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getValueWithVat()
    {
        return $this->valueWithVat;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @return string
     */
    public function getVatPercent()
    {
        return $this->vatPercent;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * @return string|null
     */
    public function getProductCatnum()
    {
        return $this->productCatnum;
    }

    /**
     * @return string|null
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @return string|null
     */
    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function getCreatedOnOrder()
    {
        return $this->createdOnOrder;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function getRedeemedOnOrder()
    {
        return $this->redeemedOnOrder;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getRedeemedAt()
    {
        return $this->redeemedAt;
    }

    /**
     * @return bool
     */
    public function isUnredeemed()
    {
        return $this->status === GiftVoucherStatusEnum::STATUS_UNREDEEMED;
    }

    /**
     * @return bool
     */
    public function isValidAt(DateTimeImmutable $now)
    {
        return $this->activatedAt <= $now && $this->validUntil >= $now;
    }

    public function isExpiredAt(DateTimeImmutable $now): bool
    {
        return $this->validUntil < $now;
    }

    public function markAsRedeemed(Order $order, DateTimeImmutable $redeemedAt): void
    {
        if (!$this->isUnredeemed()) {
            throw new GiftVoucherAlreadyRedeemedException($this->code);
        }

        $this->status = GiftVoucherStatusEnum::STATUS_REDEEMED;
        $this->redeemedOnOrder = $order;
        $this->redeemedAt = $redeemedAt;
        $order->addRedeemedGiftVoucher($this);
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getEmailEnqueuedAt()
    {
        return $this->emailEnqueuedAt;
    }

    public function markEmailAsEnqueued(DateTimeImmutable $emailEnqueuedAt): void
    {
        $this->emailEnqueuedAt = $emailEnqueuedAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getEmailSentAt()
    {
        return $this->emailSentAt;
    }

    public function markEmailAsSent(DateTimeImmutable $emailSentAt): void
    {
        $this->emailSentAt = $emailSentAt;
    }
}
