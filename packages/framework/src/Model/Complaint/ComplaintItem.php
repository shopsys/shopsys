<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="complaint_items")
 * @ORM\Entity
 */
class ComplaintItem
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Complaint\Complaint", inversedBy="items")
     * @ORM\JoinColumn(name="complaint_id", referencedColumnName="id", nullable=false)
     */
    protected $complaint;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Item\OrderItem")
     * @ORM\JoinColumn(name="order_item_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $orderItem;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $product;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $productName;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $catnum;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $quantity;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData $complaintItemData
     */
    public function __construct(ComplaintItemData $complaintItemData)
    {
        $this->setData($complaintItemData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData $complaintItemData
     */
    public function edit(ComplaintItemData $complaintItemData): void
    {
        $this->setData($complaintItemData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData $complaintItemData
     */
    protected function setData(ComplaintItemData $complaintItemData)
    {
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
    public function setComplaint($complaint)
    {
        $this->complaint = $complaint;
    }
}
