<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;

#[AsMcpTable]
#[Loggable(Loggable::STRATEGY_INCLUDE_ALL)]
#[ORM\Table(name: 'product_reviews')]
#[ORM\Index(columns: ['domain_id', 'status'])]
#[ORM\Index(columns: ['product_id', 'domain_id', 'status'])]
#[ORM\UniqueConstraint(columns: ['customer_user_id', 'product_id', 'domain_id'])]
#[ORM\Entity]
class ProductReview
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
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(name: 'domain_id', type: 'integer')]
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100)]
    protected $catnum;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $productName;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'order_item_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: OrderItem::class)]
    protected $orderItem;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    protected $customerUser;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100)]
    protected $firstName;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100)]
    protected $lastName;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $email;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $isAnonymous;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'smallint')]
    protected $rating;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $text;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 45)]
    protected $ipAddress;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $isVerifiedPurchase;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 20)]
    protected $status;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $rejectionReason;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $responseText;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $responseCreatedAt;

    public function __construct(ProductReviewData $productReviewData)
    {
        $this->uuid = $productReviewData->uuid ?? Uuid::uuid4()->toString();
        $this->createdAt = $productReviewData->createdAt ?? new DatePoint();
        $this->domainId = $productReviewData->domainId;
        $this->product = $productReviewData->product;
        $this->catnum = $productReviewData->catnum;
        $this->productName = $productReviewData->productName;
        $this->orderItem = $productReviewData->orderItem;
        $this->customerUser = $productReviewData->customerUser;
        $this->ipAddress = $productReviewData->ipAddress;
        $this->isVerifiedPurchase = $productReviewData->isVerifiedPurchase;

        $this->setData($productReviewData);
    }

    protected function setData(ProductReviewData $productReviewData): void
    {
        $this->firstName = $productReviewData->firstName;
        $this->lastName = $productReviewData->lastName;
        $this->email = $productReviewData->email;
        $this->isAnonymous = $productReviewData->isAnonymous;
        $this->rating = $productReviewData->rating;
        $this->text = $productReviewData->text;
        $this->status = $productReviewData->status;
        $this->rejectionReason = $productReviewData->status === ProductReviewStatusEnum::STATUS_REJECTED ? $productReviewData->rejectionReason : null;

        $this->setResponse($productReviewData);
    }

    protected function setResponse(ProductReviewData $productReviewData): void
    {
        $responseText = TransformStringHelper::emptyToNull($productReviewData->responseText);

        if ($responseText === null) {
            $this->responseText = null;
            $this->responseCreatedAt = null;

            return;
        }

        // the date says when the response was published, so it is kept while the already published response is only reworded
        if ($this->responseText === null) {
            $this->responseCreatedAt = $productReviewData->responseCreatedAt ?? new DatePoint();
        }

        $this->responseText = $responseText;
    }

    public function edit(ProductReviewData $productReviewData): void
    {
        $this->setData($productReviewData);
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
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function getCatnum()
    {
        return $this->catnum;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem|null
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    #[EntityLogIdentify]
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function isAnonymous()
    {
        return $this->isAnonymous;
    }

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @return string|null
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @return bool
     */
    public function isVerifiedPurchase()
    {
        return $this->isVerifiedPurchase;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getRejectionReason()
    {
        return $this->rejectionReason;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string|null
     */
    public function getResponseText()
    {
        return $this->responseText;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getResponseCreatedAt()
    {
        return $this->responseCreatedAt;
    }
}
