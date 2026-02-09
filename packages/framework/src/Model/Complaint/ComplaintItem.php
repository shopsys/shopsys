<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Product\Product;

#[ORM\Table(name: 'complaint_items')]
#[ORM\Entity]
class ComplaintItem
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    #[ORM\JoinColumn(name: 'complaint_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Complaint::class, inversedBy: 'items')]
    protected $complaint;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem|null
     */
    #[ORM\JoinColumn(name: 'order_item_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: OrderItem::class)]
    protected $orderItem;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $productName;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', nullable: true)]
    protected $catnum;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $quantity;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text')]
    protected $description;

    public function __construct(ComplaintItemData $complaintItemData)
    {
        $this->setData($complaintItemData);
    }

    public function edit(ComplaintItemData $complaintItemData): void
    {
        $this->setData($complaintItemData);
    }

    protected function setData(ComplaintItemData $complaintItemData): void
    {
        $this->uuid = $complaintItemData->uuid ?? Uuid::uuid4()->toString();
        $this->orderItem = $complaintItemData->orderItem;
        $this->product = $complaintItemData->product;
        $this->productName = $complaintItemData->productName;
        $this->catnum = $complaintItemData->catnum;
        $this->quantity = $complaintItemData->quantity;
        $this->description = $complaintItemData->description;
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
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    public function getComplaint()
    {
        return $this->complaint;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem|null
     */
    public function getOrderItem()
    {
        return $this->orderItem;
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
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @return string
     */
    public function getCatnum()
    {
        return $this->catnum;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     */
    public function setComplaint($complaint): void
    {
        $this->complaint = $complaint;
    }
}
