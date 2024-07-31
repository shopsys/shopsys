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
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Item\OrderItem")
     * @ORM\JoinColumn(name="order_item_id", referencedColumnName="id", nullable=false)
     */
    protected $orderItem;

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
    protected function setData(ComplaintItemData $complaintItemData)
    {
        $this->orderItem = $complaintItemData->orderItem;
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
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function getOrderItem()
    {
        return $this->orderItem;
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
